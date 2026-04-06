<?php

namespace App\Services\Attendance;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\AttendancePolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DailyAttendanceProcessor
{
    protected $registry;
    protected $sandwichService;

    public function __construct(AttendanceRegistryService $registry, SandwichRuleService $sandwichService)
    {
        $this->registry = $registry;
        $this->sandwichService = $sandwichService;
    }

    /**
     * Run daily processing for a specific date (usually yesterday)
     */
    public function process(Carbon $date)
    {
        Log::info("Starting Daily Attendance Processing for {$date->toDateString()}");

        // 1. Mark Absentees
        $this->markAbsentees($date);

        // 1.1 Process Sandwich Rule (Check violations around weekends/holidays)
        $this->processSandwichRule($date);

        // 2. Apply Policy Rules to Logs
        $attendanceLogs = AttendanceLog::whereDate('date', $date)->get();
        foreach ($attendanceLogs as $log) {
            try {
                $this->applyPolicyRules($log);
            } catch (\Exception $e) {
                Log::error("Failed to process log ID {$log->id}: " . $e->getMessage());
            }
        }

        Log::info("Completed Daily Attendance Processing for {$date->toDateString()}");
    }

    /**
     * Identify employees who have no log and mark them as Absent
     */
    protected function markAbsentees(Carbon $date)
    {
        // Get all active employees
        $employees = Employee::where('status', 'Active')
            ->whereDate('joining_date', '<=', $date)
            ->where(function($q) use ($date) {
                $q->whereNull('exit_date')->orWhereDate('exit_date', '>=', $date);
            })
            ->get();

        foreach ($employees as $employee) {
            // Check if log exists
            $exists = AttendanceLog::where('employee_id', $employee->id)
                ->whereDate('date', $date)
                ->exists();

            if (!$exists) {
                // Check if it's a non-working day (Weekend/Holiday)
                if ($this->registry->isNonWorkingDay($employee, $date)) {
                    continue; // No need to mark absent on off days
                }

                // Create Absent Log
                AttendanceLog::create([
                    'employee_id' => $employee->id,
                    'date' => $date,
                    'status' => 'Absent',
                    'shift_id' => $this->registry->getShiftForDate($employee, $date)->id,
                    'total_work_minutes' => 0
                ]);
            }
        }
    }

    /**
     * Apply Attendance Policy Rules (Half Day, OT, Etc)
     */
    protected function applyPolicyRules(AttendanceLog $log)
    {
        $employee = $log->employee;
        $policy = $employee->effectiveAttendancePolicy;

        if (!$policy) {
            // No policy, keep as is
            return;
        }

        $updates = [];

        // 1. Half Day Check
        // Expect half_day_hours in 'rules' JSON
        $halfDayHours = $policy->rules['half_day_hours'] ?? 4; 
        $workHours = $log->total_work_minutes / 60;

        if ($log->status === 'Present' && $workHours < $halfDayHours) {
            $updates['is_half_day'] = true;
            $updates['status'] = 'Half Day';
        }

        // 2. Overtime Calculation
        // Expect 'overtime_rule' (standardized name)
        if (!empty($policy->overtime_policy) && $log->overtime_minutes > 0) {
            $minOtMinutes = $policy->overtime_policy['min_minutes'] ?? 30;
            
            if ($log->overtime_minutes < $minOtMinutes) {
                // Determine if we should discard it or keep it?
                // Usually we discard trivial OT
                $updates['overtime_minutes'] = 0;
            } else {
                // Auto-create OvertimeRequest if not exists
                $exists = \App\Models\OvertimeRequest::where('employee_id', $employee->id)
                    ->where('date', $log->date->toDateString())
                    ->exists();

                if (!$exists) {
                    \App\Models\OvertimeRequest::create([
                        'employee_id' => $employee->id,
                        'date' => $log->date,
                        'minutes' => $log->overtime_minutes,
                        'reason' => 'System Generated (Auto-calculated)',
                        'status' => 'Pending'
                    ]);
                    Log::info("Auto-created Overtime Request for Emp #{$employee->id} on {$log->date}");
                }
            }
        }

        // 3. Late Marking (already handled in ClockIn, but we can re-verify or enforce stricter rules)
        // e.g. if late_minutes > threshold, ensure is_late is true

        if (!empty($updates)) {
            $log->update($updates);
        }
    }
}
