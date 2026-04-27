<?php

namespace App\Services\Attendance;

use App\Models\AttendanceLog;
use App\Models\AttendanceSession;
use App\Models\Shift;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\WfhRequest;
use Carbon\Carbon;

class AttendanceRegistryService
{
    private const DEFAULT_POST_SHIFT_SESSION_CAP_MINUTES = 60;

    protected $rotationCalculator;
    protected $logger;
    protected $gamification;

    public function __construct(
        \App\Services\Infrastructure\LoggerService $logger,
        \App\Services\RotationCalculator $rotationCalculator,
        \App\Services\Gamification\GamificationService $gamification
    ) {
        $this->logger = $logger;
        $this->rotationCalculator = $rotationCalculator;
        $this->gamification = $gamification;
    }

    /**
     * Smart Punch Logic for Biometrics/API.
     * Auto-detects In or Out based on state.
     */
    public function logPunch(Employee $employee, Carbon $timestamp, string $source = 'API', $lat = null, $lon = null)
    {
        $date = $timestamp->copy()->startOfDay();
        
        // Find existing valid log for today
        $log = AttendanceLog::where('employee_id', $employee->id)
            ->whereDate('date', $date)
            ->first();

        // SCENARIO 1: No Log exists -> CLOCK IN
        if (!$log) {
            // Clock In logic (simplified version of full context)
            $log = $this->clockIn($employee, '0.0.0.0', $source);
            // Update time to exact punch time
            $session = $log->sessions()->latest()->first();
            $session->update(['in_time' => $timestamp]);
            return $log;
        }

        // SCENARIO 2: Log exists -> Check open session
        $openSession = $log->sessions()->whereNull('out_time')->latest()->first();

        if ($openSession) {
            // SCENARIO 2A: Open Session Exists -> CLOCK OUT
            // Check debounce (e.g., if punch is within 1 min of in_time, ignore?)
            if ($timestamp->diffInMinutes($openSession->in_time) < 1) {
                return $log; // Ignore rapid double punch
            }

            $openSession->update([
                'out_time' => $timestamp,
                'out_ip' => '0.0.0.0'
            ]);
            
            $this->recalculateDailyTotals($log);
            return $log;
        } else {
            // SCENARIO 2B: No Open Session -> CLOCK IN (New Session, e.g. after lunch break)
             $log->sessions()->create([
                'in_time' => $timestamp,
                'in_ip' => '0.0.0.0',
                'source' => $source,
                'session_type' => 'Work'
            ]);
            return $log;
        }
    }

    /**
     * The Clock In Action.
     * Handles logic for Late Mark, Shift assignment, and starting a session.
     */
    public function clockIn(Employee $employee, string $ip, string $source = 'Web', $lat = null, $long = null)
    {
        $today = Carbon::today();
        $now = Carbon::now();

        // Ensure any expired open sessions are force-closed before starting a new one.
        $this->autoCheckoutOpenSessions($now, $employee->id);

        // 1. Determine Context (Holiday / Leave)
        $contextStatus = 'Present'; // Default
        $isHoliday = $this->isHoliday($today, $employee);
        $isOnLeave = $this->isOnLeave($employee, $today);

        if ($isHoliday) {
            $contextStatus = 'Holiday Work';
        } elseif ($isOnLeave) {
            $contextStatus = 'Work on Leave';
        }

        // 2. Get or Create Daily Log
        // Note: Ideally, a nightly job creates "Absent" logs. Here we create "Present" logs.
        $log = AttendanceLog::firstOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            [
                'status' => $contextStatus, 
                'shift_id' => $this->getShiftForDate($employee, $today)->id
            ]
        );

        // Update status if it was previously Absent/Holiday but now they are working
        if (in_array($log->status, ['Absent', 'Holiday', 'On Leave'])) {
            $log->update(['status' => $contextStatus]);
        }

        // 3. Refresh relationship to get Shift details
        $log->load('shift');
        $shift = $log->shift;

        // --- IP Restriction Check ---
        if (!empty($shift->ip_restrictions)) {
            // Check if WFH Approved
            $isWfh = WfhRequest::where('employee_id', $employee->id)
                ->where('date', $today->toDateString())
                ->where('status', 'Approved')
                ->exists();

            if (!$isWfh && !in_array($ip, $shift->ip_restrictions)) {
                 // Throw exception if not WFH and IP doesn't match
                 throw new \Exception("Access Denied: You are attempting to clock in from an unauthorized IP address ({$ip}).");
            }
        }
        // ---------------------------

        // 4. Late Logic (First punch of the day)
        if ($log->sessions()->count() === 0) {
            $shiftStart = Carbon::parse($today->format('Y-m-d') . ' ' . $shift->start_time);
            $graceTime = $shiftStart->copy()->addMinutes($shift->grace_late_entry);

            if ($now->gt($graceTime)) {
                $log->update(['is_late' => true]);
                
                // Only mark status as "Late" if it's a regular working day
                if ($contextStatus === 'Present') {
                    $log->update(['status' => 'Late']);
                }

                // Calculate Late Minutes
                $lateMinutes = $now->diffInMinutes($shiftStart);
                $log->increment('late_minutes', $lateMinutes);
            } else {
                // --- Gamification: On-time or Early Bird ---
                if ($now->lte($shiftStart->copy()->subMinutes(30))) {
                    $this->gamification->awardPoints($employee, 'attendance.checkin.early', [
                        'check_in' => $now->toDateTimeString(),
                        'shift_start' => $shiftStart->toDateTimeString()
                    ]);
                } else {
                    $this->gamification->awardPoints($employee, 'attendance.checkin.ontime', [
                        'check_in' => $now->toDateTimeString(),
                        'shift_start' => $shiftStart->toDateTimeString()
                    ]);
                }
                // Check if any badges earned
                $this->gamification->checkBadges($employee);
            }
        }

        // 5. Start New Session
        $existingOpenSession = $log->sessions()->whereNull('out_time')->latest('in_time')->first();
        if ($existingOpenSession) {
            throw new \Exception('You are already checked in. Please check out first.');
        }

        $session = $log->sessions()->create([
            'in_time' => $now,
            'in_ip' => $ip,
            'in_lat' => $lat,
            'in_long' => $long,
            'source' => $source,
            'session_type' => 'Work'
        ]);

        $this->logger->log('attendance', 'clock_in', "{$employee->first_name} clocked in from {$ip} via {$source}. Status: {$log->status}", ['user_id' => $employee->user_id]);

        return $log;
    }

    /**
     * Auto-close open sessions based on shift-end and post-shift cap rules.
     *
     * Rules:
     * 1) If session started before/at shift end: close at shift_end + 60 minutes.
     * 2) If session started after shift end: close at in_time + 60 minutes.
     */
    public function autoCheckoutOpenSessions(?Carbon $now = null, ?int $employeeId = null): int
    {
        $now = $now ?: Carbon::now();

        $openSessionsQuery = AttendanceSession::query()
            ->whereNull('out_time')
            ->with(['log.employee', 'log.shift']);

        if ($employeeId) {
            $openSessionsQuery->whereHas('log', function ($q) use ($employeeId) {
                $q->where('employee_id', $employeeId);
            });
        }

        $openSessions = $openSessionsQuery->get();
        $closedCount = 0;

        foreach ($openSessions as $session) {
            $log = $session->log;
            $employee = $log?->employee;
            if (!$log || !$employee) {
                continue;
            }

            $shift = $log->shift ?: $this->getShiftForDate($employee, Carbon::parse($log->date));
            if (!$shift || !$shift->start_time || !$shift->end_time) {
                continue;
            }

            $cutoffTime = $this->resolveSessionAutoCheckoutCutoff($session, $log, $shift);
            if (!$cutoffTime || $now->lt($cutoffTime)) {
                continue;
            }

            $outTime = $cutoffTime->copy();
            if ($outTime->lt(Carbon::parse($session->in_time))) {
                $outTime = Carbon::parse($session->in_time);
            }

            $session->update([
                'out_time' => $outTime,
                'out_ip' => 'SYSTEM_AUTO_CUTOFF',
            ]);

            $freshLog = $log->fresh(['sessions', 'shift']);
            if ($freshLog) {
                $this->recalculateDailyTotals($freshLog);
            }

            $closedCount++;
        }

        return $closedCount;
    }

    /**
     * The Clock Out Action.
     * Closes the current session and recalculates totals.
     */
    public function clockOut(Employee $employee, string $ip, $lat = null, $long = null)
    {
        $today = Carbon::today();
        $now = Carbon::now();

        $log = AttendanceLog::where('employee_id', $employee->id)
                            ->where('date', $today)
                            ->firstOrFail();

        // Find open session
        $session = $log->sessions()->whereNull('out_time')->latest()->first();

        if ($session) {
            $session->update([
                'out_time' => $now,
                'out_ip' => $ip,
                'out_lat' => $lat,
                'out_long' => $long
            ]);

            // Recalculate Totals & Overtime
            $this->recalculateDailyTotals($log);

            $this->logger->log('attendance', 'clock_out', "{$employee->first_name} clocked out from {$ip}. Total: {$log->total_work_minutes}m", ['user_id' => $employee->user_id]);
        }

        return $log;
    }

    /**
     * Recalculate total work minutes and Overtime for the day.
     */
    public function recalculateDailyTotals(AttendanceLog $log)
    {
        $totalMinutes = 0;
        foreach ($log->sessions as $session) {
            if ($session->out_time) {
                $totalMinutes += $session->out_time->diffInMinutes($session->in_time);
            }
        }

        // Calculate Shift Duration
        $log->load('shift');
        if (!$log->shift || !$log->shift->start_time || !$log->shift->end_time) {
            $log->update([
                'total_work_minutes' => $totalMinutes,
                'overtime_minutes' => 0,
            ]);

            return;
        }

        $shiftStart = Carbon::parse($log->date->format('Y-m-d') . ' ' . $log->shift->start_time);
        $shiftEnd = Carbon::parse($log->date->format('Y-m-d') . ' ' . $log->shift->end_time);
        if ($shiftEnd->lessThanOrEqualTo($shiftStart)) {
            $shiftEnd->addDay();
        }
        $shiftMinutes = $shiftStart->diffInMinutes($shiftEnd);

        // Calculate Overtime
        $overtime = 0;
        if ($totalMinutes > $shiftMinutes) {
            $overtime = $totalMinutes - $shiftMinutes;
        }

        $log->update([
            'total_work_minutes' => $totalMinutes,
            'overtime_minutes' => $overtime
        ]);
    }

    private function resolveSessionAutoCheckoutCutoff(AttendanceSession $session, AttendanceLog $log, Shift $shift): ?Carbon
    {
        if (!$session->in_time || !$shift->start_time || !$shift->end_time) {
            return null;
        }

        $logDate = Carbon::parse($log->date);
        $sessionIn = Carbon::parse($session->in_time);
        $shiftStart = Carbon::parse($logDate->format('Y-m-d') . ' ' . $shift->start_time);
        $shiftEnd = Carbon::parse($logDate->format('Y-m-d') . ' ' . $shift->end_time);

        // Overnight shift support.
        if ($shiftEnd->lessThanOrEqualTo($shiftStart)) {
            $shiftEnd->addDay();
        }

        $postShiftCapMinutes = $this->getPostShiftSessionCapMinutes($shift);

        // Before or during shift: allow max 1 hour beyond shift end.
        if ($sessionIn->lessThanOrEqualTo($shiftEnd)) {
            return $shiftEnd->copy()->addMinutes($postShiftCapMinutes);
        }

        // After shift end re-check-ins: each session gets its own 1-hour cap.
        return $sessionIn->copy()->addMinutes($postShiftCapMinutes);
    }

    private function getPostShiftSessionCapMinutes(Shift $shift): int
    {
        $cap = $shift->post_shift_auto_checkout_cap_minutes;
        if (!is_numeric($cap)) {
            return self::DEFAULT_POST_SHIFT_SESSION_CAP_MINUTES;
        }

        return max(0, (int) $cap);
    }

    /**
     * Determine Shift for the day.
     * Priority: 
     * 1. Shift Swap (Approved)
     * 2. Shift Rotation (Active)
     * 3. Default Shift (Location Aware)
     */
    public function getShiftForDate(Employee $employee, Carbon $date)
    {
        // 0. Check for Manual Roster Assignment (Highest Priority)
        // This Table is populated by Shift Rotations OR Manual Admin/Manager Overrides
        $rosterEntry = \App\Models\ShiftRoster::where('employee_id', $employee->id)
            ->where('date', $date->toDateString())
            ->with('shift')
            ->first();

        if ($rosterEntry && $rosterEntry->shift) {
            return $rosterEntry->shift;
        }

        // 1. Check for Approved Shift Swaps (As Requester or Recipient)
        $swapAsRequester = \App\Models\ShiftSwap::where('requester_id', $employee->id)
            ->where('date', $date)
            ->where('status', 'Approved')
            ->first();

        if ($swapAsRequester) {
            return $swapAsRequester->shiftTo;
        }

        $swapAsRecipient = \App\Models\ShiftSwap::where('recipient_id', $employee->id)
            ->where('date', $date)
            ->where('status', 'Approved')
            ->first();

        if ($swapAsRecipient) {
            return $swapAsRecipient->shiftFrom;
        }

        // 2. Check Shift Rotations (Level 5)
        $rotationShift = $this->rotationCalculator->getShiftForDate($employee, $date);
        if ($rotationShift) {
            return $rotationShift;
        }

        // 3. Return Default Shift (Location Aware)
        // A. Priority: Specific Location Match
        if ($employee->location_id) {
            $specificDefault = Shift::where('is_default', true)
                ->where(function($q) use ($employee) {
                     $q->whereJsonContains('location_ids', $employee->location_id)
                       ->orWhereJsonContains('location_ids', (string)$employee->location_id);
                })
                ->first();
            
            if ($specificDefault) {
                return $specificDefault;
            }
        }

        // B. Fallback: Global Default (No Location restriction)
        $globalDefault = Shift::where('is_default', true)
            ->whereNull('location_ids')
            ->first();

        return $globalDefault ?? Shift::first();
    }

    /**
     * Check if date is a Holiday for this specific employee.
     * Handles Fixed vs Restricted logic.
     */
    private function isHoliday(Carbon $date, Employee $employee): bool
    {
        $holiday = Holiday::where('date', $date->toDateString())->first();

        if (!$holiday) {
            return false;
        }

        if ($holiday->type === 'Fixed' || $holiday->type === 'AdHoc') {
            // Check location scope (future)
            return true;
        }

        if ($holiday->type === 'Restricted') {
            // Must have an approved request
            return \App\Models\FloatingHolidayRequest::where('user_id', $employee->user_id)
                ->where('holiday_id', $holiday->id)
                ->where('status', 'Approved')
                ->exists();
        }

        return false;
    }

    /**
     * Check if employee has Approved Leave for date.
     */
    private function isOnLeave(Employee $employee, Carbon $date): bool
    {
        return \App\Models\LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'Approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();
    }

    /**
     * Check if date is a Non-Working Day (Holiday or Weekend/Off-day).
     */
    public function isNonWorkingDay(Employee $employee, Carbon $date): bool
    {
        // 1. Check Holiday (Fixed only for working day calculation?)
        // isHoliday handles Restricted too. For WFH calculation, if I have an Approved RH, it's a non-working day.
        if ($this->isHoliday($date, $employee)) {
            return true;
        }

        // 2. Check Shift Work Days
        $shift = $this->getShiftForDate($employee, $date);
        
        // Handle migration JSON format. Assuming ["Mon", "Tue"]
        $workDays = $shift->work_days ?? [];
        if (is_string($workDays)) $workDays = json_decode($workDays, true);

        $dayName = $date->format('D'); // Mon, Tue...
        
        return !in_array($dayName, $workDays);
    }
}
