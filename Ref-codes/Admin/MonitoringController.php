<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Employee;
use App\Models\AttendanceLog;
use Illuminate\Support\Carbon;

class MonitoringController extends Controller
{
    protected $attendanceRegistry;

    public function __construct(\App\Services\Attendance\AttendanceRegistryService $attendanceRegistry)
    {
        $this->attendanceRegistry = $attendanceRegistry;
    }

    public function index(Request $request)
    {
        return Inertia::render('Admin/Attendance/Hub', [
            'tab' => $request->input('tab', 'monitor_view'),
            'locations' => \App\Models\Location::select('id', 'name')->get(),
            'departments' => \App\Models\Department::select('id', 'name')->get(),
        ]);
    }

    public function getData(Request $request)
    {
        $type = $request->input('type', 'monitor'); // Default to monitor

        switch ($type) {
            case 'roster':
                return $this->getRosterStats();
            case 'shift':
                return $this->getShiftStats();
            case 'pattern':
                return $this->getPatternStats();
            case 'request':
                return $this->getRequestStats();
            case 'compoff':
                return $this->getCompOffStats();
            case 'device':
                return $this->getDeviceStats();
            case 'monitor':
            default:
                return $this->getMonitorData($request);
        }
    }

    private function getMonitorData(Request $request)
    {
        $date = $request->filled('date_from') ? Carbon::parse($request->date_from) : Carbon::today();
        
        // Fetch all active employees
        $employeesQuery = Employee::with(['department']);
        
        if ($request->filled('department_id')) {
            $employeesQuery->where('department_id', $request->department_id);
        }
        
        if ($request->filled('location_id')) {
            $employeesQuery->where('location_id', $request->location_id);
        }
        
        $employees = $employeesQuery->get();
        
        // Fetch logs for the selected date
        $logs = AttendanceLog::where('date', $date->toDateString())
            ->with(['sessions'])
            ->get()
            ->keyBy('employee_id');

        $data = $employees->map(function ($emp) use ($logs, $date) {
            $log = $logs->get($emp->id);
            $status = 'Absent';
            $checkIn = null;
            $checkOut = null;
            $isLate = false;
            
            $workDuration = 0; // Minutes
            $deviceInfo = 'Unknown';
            $ipAddress = '-';
            
            if ($log) {
                $status = $log->status;
                $isLate = $log->is_late; 
                
                if ($log->sessions && $log->sessions->isNotEmpty()) {
                     $firstSession = $log->sessions->first();
                     $in_time = $firstSession->in_time ? (is_string($firstSession->in_time) ? Carbon::parse($firstSession->in_time) : $firstSession->in_time) : null;
                     $checkIn = $in_time ? $in_time->format('H:i') : null;

                    // Calculate Work Duration & Breaks
                    foreach ($log->sessions as $index => $session) {
                        $in = $session->in_time ? (is_string($session->in_time) ? Carbon::parse($session->in_time) : $session->in_time) : null;
                        if (!$in) continue;
                        $out = $session->out_time ? (is_string($session->out_time) ? Carbon::parse($session->out_time) : $session->out_time) : Carbon::now(); // If active, calc till now
                        
                        $workDuration += $in->diffInMinutes($out);

                        // Capture Device Info from latest session
                        if ($index === $log->sessions->count() - 1) {
                             $ipAddress = $session->ip_address ?? '-';
                             // Parse User Agent roughly
                             $ua = $session->user_agent ?? '';
                             if (str_contains($ua, 'Mobile')) $deviceInfo = 'Mobile';
                             elseif (str_contains($ua, 'Windows')) $deviceInfo = 'Windows PC';
                             elseif (str_contains($ua, 'Mac')) $deviceInfo = 'Mac';
                             else $deviceInfo = 'Desktop';
                        }
                    }
                }
            }

            // Resolve Shift dynamically
            $shift = $this->attendanceRegistry->getShiftForDate($emp, $date);

            // Calculate Progress based on Shift
            $shiftDuration = 9 * 60; // Default 9 Hours
            if ($shift) {
                 $sStart = Carbon::parse($shift->start_time);
                 $sEnd = Carbon::parse($shift->end_time);
                 $shiftDuration = $sStart->diffInMinutes($sEnd);
            }
            $progress = min(100, round(($workDuration / $shiftDuration) * 100));

            return [
                'id' => $emp->id,
                'name' => $emp->first_name . ' ' . $emp->last_name,
                'designation' => $emp->designation ?? 'Employee', 
                'department' => $emp->department ? $emp->department->name : '-',
                'shift' => $shift ? $shift->name : 'By Policy',
                'check_in' => $checkIn ?? '-',
                'status' => $status,
                'is_late' => $isLate,
                'avatar' => $emp->profile_picture,
                // Deep Details
                'work_duration_human' => floor($workDuration / 60) . 'h ' . ($workDuration % 60) . 'm',
                'progress' => $progress,
                'device' => $deviceInfo,
                'ip' => $ipAddress,
            ];
        });

        // Stats
        $stats = [
            'total' => $employees->count(),
            'present' => $data->filter(fn($d) => $d['check_in'] !== '-')->count(),
            'active' => $data->filter(fn($d) => str_contains($d['status'], 'Active'))->count(),
            'late' => $data->filter(fn($d) => $d['is_late'])->count(),
        ];

        return response()->json([
            'employees' => $data,
            'stats' => $stats
        ]);
    }

    private function getRosterStats()
    {
        // Mocking Roster Stats for now -> Replace with actual DB calls to ShiftRoster
        return response()->json([
            'coverage' => 85,
            'open_shifts' => 12,
            'conflicts' => 3,
            'role_distribution' => [
                'Admins' => 5,
                'Managers' => 15,
                'Engineers' => 50,
                'Support' => 30
            ],
            'shift_allocation' => [
                'General' => 60,
                'Morning' => 20,
                'Night' => 10,
                'Rotating' => 10
            ]
        ]);
    }

    private function getShiftStats()
    {
        // Mocking Logic
        return response()->json([
            'shifts' => [
                ['name' => 'General', 'count' => 65, 'load' => 70],
                ['name' => 'Morning', 'count' => 25, 'load' => 85],
                ['name' => 'Night', 'count' => 15, 'load' => 40],
            ],
            'avg_hours' => 8.5,
            'utilization' => 92
        ]);
    }

    private function getPatternStats()
    {
         // Mocking Logic
         return response()->json([
            'adherence' => 94,
            'deviations' => 12,
            'rotation_health' => 'Good',
            'timeline' => [
                ['day' => 'Mon', 'compliance' => 98],
                ['day' => 'Tue', 'compliance' => 95],
                ['day' => 'Wed', 'compliance' => 92],
                ['day' => 'Thu', 'compliance' => 96],
                ['day' => 'Fri', 'compliance' => 88],
            ]
         ]);
    }

    private function getRequestStats()
    {
        // Mocking Logic
        return response()->json([
            'pending' => 45,
            'approved' => 120,
            'rejected' => 15,
            'avg_response_time' => '4h 30m',
            'trend' => [
                ['date' => '2023-10-01', 'count' => 5],
                ['date' => '2023-10-02', 'count' => 8],
                ['date' => '2023-10-03', 'count' => 12],
                ['date' => '2023-10-04', 'count' => 7],
                ['date' => '2023-10-05', 'count' => 15],
            ]
        ]);
    }

    private function getCompOffStats()
    {
        // Mocking Logic
         return response()->json([
            'earned' => 150,
            'burned' => 45,
            'expired' => 12,
            'liability_hours' => 320,
            'top_earners' => [
                ['name' => 'Alice', 'hours' => 24],
                ['name' => 'Bob', 'hours' => 16],
                ['name' => 'Charlie', 'hours' => 12]
            ]
         ]);
    }


    public function stats()
    {
        $today = Carbon::today()->toDateString();
        
        // 1. Counts
        $totalEmployees = Employee::where('status', 'Active')->count();
        $logs = AttendanceLog::where('date', $today)->get();
        
        $presentCount = $logs->where('status', 'Present')->count(); // Or check-in exists
        $lateCount = $logs->where('is_late', true)->count();
        $absentCount = $totalEmployees - $presentCount; // Simplified

        $topStreaks = [];
        $sandwichDeductions = [];

        // 2. Late Comers
        $lateComers = AttendanceLog::with(['employee.department'])
            ->where('date', $today)
            ->where('is_late', true)
            ->orderBy('late_minutes', 'desc')
            ->take(5)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->employee->id,
                    'first_name' => $log->employee->first_name,
                    'last_name' => $log->employee->last_name,
                    'department' => $log->employee->department,
                    'shift_name' => 'General', // Placeholder, ideally fetch from Shift
                    'check_in_time' => $log->sessions->first()?->in_time?->format('H:i') ?? '-',
                    'late_minutes' => $log->late_minutes
                ];
            });

        // 3. Recent Punches/Events
        // We need to fetch sessions across all logs today and sort by created_at desc
        // This is a bit complex with Eloquent, so we'll query sessions directly if possible
        // But for now, let's extract from logs
        $recentPunches = [];
        foreach($logs as $log) {
            foreach($log->sessions as $session) {
                if ($session->in_time) {
                    $recentPunches[] = [
                        'type' => 'Check In',
                        'timestamp' => $session->in_time,
                        'user' => $log->employee,
                        'source' => 'Web'
                    ];
                }
                if ($session->out_time) {
                    $recentPunches[] = [
                        'type' => 'Check Out',
                        'timestamp' => $session->out_time,
                        'user' => $log->employee,
                        'source' => 'Web'
                    ];
                }
            }
        }
        
        // Sort by timestamp desc and take 10
        usort($recentPunches, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
        $recentPunches = array_slice($recentPunches, 0, 10);

        // 4. Pending Requests
        // Assuming OvertimeRequest, WfhRequest models exist. 
        // For now return 0 or mock
        $pendingRequests = 0; 
        // $pendingRequests = OvertimeRequest::where('status', 'Pending')->count() + WfhRequest::where('status', 'Pending')->count();

        return response()->json([
            'stats' => [
                'total_employees' => $totalEmployees,
                'present_count' => $presentCount,
                'late_count' => $lateCount,
                'absent_count' => $absentCount,
                'pending_requests' => $pendingRequests
            ],
            'late_comers' => $lateComers,
            'recent_punches' => $recentPunches,
            'departments' => \App\Models\Department::select('id', 'name')->get(),
            'top_streaks' => $topStreaks,
            'sandwich_violations' => $sandwichDeductions 
        ]);
    }

    public function getEmployeeYearlyDetailsPage(Request $request, Employee $employee)
    {
        $year = $request->input('year', Carbon::now()->year);
        $startOfYear = Carbon::create($year, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($year, 12, 31)->endOfDay();

        // Establish today limit boundary for calculating "so far" vs "remaining"
        $today = Carbon::today();
        if ($year == $today->year) {
            $boundaryDate = $today;
        } elseif ($year < $today->year) {
            $boundaryDate = $endOfYear->copy();
        } else {
            $boundaryDate = $startOfYear->copy()->subDay(); // everything is remaining
        }

        // 1. Fetch Logs
        $logs = AttendanceLog::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfYear->toDateString(), $endOfYear->toDateString()])
            ->with(['sessions'])
            ->get()
            ->keyBy(function($log) {
                return Carbon::parse($log->date)->toDateString();
            });

        // Calculate Paid & Sick Leave balances, utilization, and pending requests dynamically for all valid leave types
        $tenantId = auth()->user()->tenant_id ?? $employee->tenant_id ?? null;
        $leaveTypeQuery = \App\Models\LeaveType::query();
        if ($tenantId) {
            $leaveTypeQuery->where('tenant_id', $tenantId);
        }
        $leaveTypes = $leaveTypeQuery->get();
        $leaveLedgers = [];
        
        $sickTotal = 0;
        $sickUtilized = 0;
        $sickPending = 0;
        $paidTotal = 0;
        $paidUtilized = 0;
        $paidPending = 0;

        foreach ($leaveTypes as $type) {
            $isValid = $type->is_active;
            if ($isValid) {
                $yearStart = Carbon::create($year, 1, 1);
                $yearEnd = Carbon::create($year, 12, 31);
                
                if ($type->applicable_from && Carbon::parse($type->applicable_from)->gt($yearEnd)) {
                    $isValid = false;
                }
                if ($type->applicable_to && Carbon::parse($type->applicable_to)->lt($yearStart)) {
                    $isValid = false;
                }
            }
            
            if (!$isValid) {
                continue;
            }
            
            $balance = \App\Models\LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_type_id', $type->id)
                ->where('year', $year)
                ->first();
            $allowed = $balance ? (float) $balance->total_days : (float) $type->days_allowed_per_year;
            
            $utilized = \App\Models\LeaveRequest::where('employee_id', $employee->id)
                ->where('leave_type_id', $type->id)
                ->whereIn(\DB::raw('LOWER(status)'), ['approved'])
                ->where(function($q) use ($startOfYear, $endOfYear) {
                    $q->whereBetween('start_date', [$startOfYear->toDateString(), $endOfYear->toDateString()])
                      ->orWhereBetween('end_date', [$startOfYear->toDateString(), $endOfYear->toDateString()]);
                })
                ->get()
                ->sum('total_days');
                
            $pending = \App\Models\LeaveRequest::where('employee_id', $employee->id)
                ->where('leave_type_id', $type->id)
                ->whereIn(\DB::raw('LOWER(status)'), ['pending'])
                ->where(function($q) use ($startOfYear, $endOfYear) {
                    $q->whereBetween('start_date', [$startOfYear->toDateString(), $endOfYear->toDateString()])
                      ->orWhereBetween('end_date', [$startOfYear->toDateString(), $endOfYear->toDateString()]);
                })
                ->get()
                ->sum('total_days');
                
            $leaveLedgers[] = [
                'id' => $type->id,
                'name' => $type->name,
                'code' => $type->code,
                'color' => $type->color ?? '#3b82f6',
                'allowed' => $allowed,
                'utilized' => $utilized,
                'pending' => $pending,
                'remaining' => max(0, $allowed - $utilized),
            ];

            // Maintain legacy / backward compatible variables
            if (in_array(strtoupper($type->code), ['SL', 'SICK'])) {
                $sickTotal = $allowed;
                $sickUtilized = $utilized;
                $sickPending = $pending;
            } elseif ($type->is_paid) {
                $paidTotal += $allowed;
                $paidUtilized += $utilized;
                $paidPending += $pending;
            }
        }

        // 2. Fetch approved/pending LeaveRequests overlapping with the year (for calendar status resolution)
        // Eager-load leaveType to avoid N+1 queries when resolving leave_title per day.
        $leaves = \App\Models\LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', ['Approved', 'Pending'])
            ->where(function($q) use ($startOfYear, $endOfYear) {
                $q->whereBetween('start_date', [$startOfYear->toDateString(), $endOfYear->toDateString()])
                  ->orWhereBetween('end_date', [$startOfYear->toDateString(), $endOfYear->toDateString()]);
            })
            ->with('leaveType')
            ->get();

        // 3. Fetch WFH requests (for calendar status resolution)
        // NOTE: WfhRequest->date is cast to Carbon ('date' cast). keyBy('date') would produce
        // a Carbon __toString() key like "2026-05-02 00:00:00" which breaks $dateStr lookups.
        // We explicitly keyBy toDateString() to get clean "Y-m-d" keys.
        $wfhRequests = \App\Models\WfhRequest::where('employee_id', $employee->id)
            ->whereIn('status', ['Approved', 'Pending'])
            ->whereBetween('date', [$startOfYear->toDateString(), $endOfYear->toDateString()])
            ->get()
            ->keyBy(fn($w) => Carbon::parse($w->date)->toDateString());

        // 4. Fetch Restrictive / General Holidays
        $holidays = \App\Models\Holiday::where(function($q) use ($startOfYear, $endOfYear) {
            $q->where('date', '<=', $endOfYear->toDateString())
              ->where('end_date', '>=', $startOfYear->toDateString());
        })->get();

        // Floating Holiday requests (Restricted holidays)
        $floatingRequests = collect();
        if ($employee->user_id) {
            $floatingRequests = \App\Models\FloatingHolidayRequest::where('user_id', $employee->user_id)
                ->whereIn('status', ['Approved', 'Pending'])
                ->with('holiday')
                ->get()
                ->keyBy(function($r) {
                    return $r->holiday ? Carbon::parse($r->holiday->date)->toDateString() : '';
                });
        }

        // 5. Fetch ALL LeaveRequests overlapping with the year (Approved, Pending, Rejected, Cancelled)
        $allLeavesList = \App\Models\LeaveRequest::where('employee_id', $employee->id)
            ->where(function($q) use ($startOfYear, $endOfYear) {
                $q->whereBetween('start_date', [$startOfYear->toDateString(), $endOfYear->toDateString()])
                  ->orWhereBetween('end_date', [$startOfYear->toDateString(), $endOfYear->toDateString()]);
            })
            ->with('leaveType')
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn($l) => [
                'id' => $l->id,
                'type' => 'Leave Request',
                'category' => $l->leaveType->name ?? 'Leave',
                'date_range' => Carbon::parse($l->start_date)->format('d M Y') . ' - ' . Carbon::parse($l->end_date)->format('d M Y'),
                'days' => (Carbon::parse($l->start_date)->diffInDays(Carbon::parse($l->end_date)) + 1) . ' Days',
                'status' => $l->status,
                'reason' => $l->reason ?? 'No reason provided',
                'submitted_at' => $l->created_at ? $l->created_at->format('d M Y h:i A') : '-'
            ]);

        // Fetch ALL WFHRequests for the year (Approved, Pending, Rejected)
        $allWfhList = \App\Models\WfhRequest::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfYear->toDateString(), $endOfYear->toDateString()])
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($w) => [
                'id' => $w->id,
                'type' => 'WFH Request',
                'category' => 'Work From Home',
                'date_range' => Carbon::parse($w->date)->format('d M Y'),
                'days' => '1 Day',
                'status' => $w->status,
                'reason' => $w->reason ?? 'No reason provided',
                'submitted_at' => $w->created_at ? $w->created_at->format('d M Y h:i A') : '-'
            ]);

        $requestsList = $allLeavesList->concat($allWfhList)->sortByDesc('submitted_at')->values();

        // Resolvers
        $resolver = app(\App\Services\Attendance\WorkingDayResolverService::class);

        // Build 365 Days
        $daysData = [];
        $currentDate = $startOfYear->copy();
        
        $workingDaysSoFar = 0;
        $remainingWorkingDays = 0;
        $presentDays = 0;
        $lateDays = 0;
        $absentDays = 0;
        $leaveDays = 0;
        $pendingLeaveDays = 0;
        $wfhDays = 0;
        $pendingWfhDays = 0;
        $notMarkedDays = 0;
        $holidayDays = 0;
        $weekOffDays = 0;

        $inTimes = [];
        $workDurations = [];
        $totalDeviationMinutes = 0;

        while ($currentDate->lte($endOfYear)) {
            $dateStr = $currentDate->toDateString();
            $log = $logs->get($dateStr);
            $isPastOrToday = $currentDate->lte($boundaryDate);

            // Determine status flags
            $isWeekOff = $resolver->isWeekOff($currentDate, $employee);
            $isHoliday = $resolver->isHoliday($currentDate, $employee);
            
            // Check general public holidays for matches
            $generalHoliday = $holidays->first(function($h) use ($dateStr, $employee) {
                $hDate = Carbon::parse($h->date)->toDateString();
                $hEndDate = Carbon::parse($h->end_date ?? $h->date)->toDateString();
                return $dateStr >= $hDate && $dateStr <= $hEndDate && $h->type !== 'Restricted' &&
                    (empty($h->applies_to_locations) || in_array($employee->location_id, $h->applies_to_locations));
            });

            // Check Restricted Holiday request
            $floatingRequest = $floatingRequests->get($dateStr);
            $hasApprovedRH = $floatingRequest && $floatingRequest->status === 'Approved';
            
            // Effective holiday
            $holidayMatch = $generalHoliday ?: ($hasApprovedRH ? $floatingRequest->holiday : null);

            // Check active leave
            // NOTE: LeaveRequest->start_date and end_date are cast to Carbon objects.
            // String comparison with Carbon objects is unreliable under IST timezone — always
            // normalise to "Y-m-d" date strings before comparing.
            $activeLeave = $leaves->first(function($l) use ($dateStr) {
                $leaveStart = Carbon::parse($l->start_date)->toDateString();
                $leaveEnd   = Carbon::parse($l->end_date)->toDateString();
                return $dateStr >= $leaveStart && $dateStr <= $leaveEnd;
            });

            // Check WFH
            $wfhMatch = $wfhRequests->get($dateStr);

            $isWorkingDay = !$isWeekOff && !$isHoliday && !$holidayMatch;

            if ($isWorkingDay) {
                if ($isPastOrToday) {
                    $workingDaysSoFar++;
                } else {
                    $remainingWorkingDays++;
                }
            }

            // Resolve base classifications
            $checkIn = null;
            $checkOut = null;
            $workDuration = 0;
            $lateMinutes = 0;
            $earlyExitMinutes = 0;
            $sessions = [];
            $shiftName = 'By Policy';
            
            if ($log) {
                $shiftName = $log->shift ? $log->shift->name : 'By Policy';
                $workDuration = $log->total_work_minutes;
                $lateMinutes = $log->late_minutes ?? 0;
                $earlyExitMinutes = $log->early_leaving_minutes ?? 0;

                if ($log->sessions && $log->sessions->isNotEmpty()) {
                    $first = $log->sessions->first();
                    $last = $log->sessions->last();
                    $checkIn = $first->in_time ? Carbon::parse($first->in_time)->format('h:i A') : '--:--';
                    $checkOut = $last->out_time ? Carbon::parse($last->out_time)->format('h:i A') : 'Active';

                    if ($first->in_time) {
                        $inTime = Carbon::parse($first->in_time);
                        $inTimes[] = ($inTime->hour * 60) + $inTime->minute;
                    }
                    if ($log->total_work_minutes > 0) {
                        $workDurations[] = $log->total_work_minutes;
                    }
                }
                $totalDeviationMinutes += $lateMinutes + $earlyExitMinutes;

                foreach ($log->sessions as $s) {
                    $sessions[] = [
                        'in' => $s->in_time ? Carbon::parse($s->in_time)->format('h:i A') : '--:--',
                        'out' => $s->out_time ? Carbon::parse($s->out_time)->format('h:i A') : 'Active',
                        'in_ip' => $s->in_ip ?? '-',
                        'out_ip' => $s->out_ip ?? '-',
                    ];
                }

                $dbStatus = strtolower($log->status);
                if ($dbStatus === 'late' || $log->is_late) {
                    $status = 'Late';
                    $lateDays++;
                    $presentDays++;
                } elseif ($dbStatus === 'present') {
                    $status = 'Present';
                    $presentDays++;
                } elseif ($dbStatus === 'absent') {
                    $status = 'Absent';
                    $absentDays++;
                } elseif (in_array($dbStatus, ['half day', 'half-day', 'half_day'])) {
                    $status = 'Half Day';
                    $presentDays += 0.5;
                } elseif (in_array($dbStatus, ['on leave', 'on-leave', 'leave'])) {
                    $status = 'On Leave';
                    $leaveDays++;
                } elseif (in_array($dbStatus, ['holiday'])) {
                    $status = 'Holiday';
                    $holidayDays++;
                } elseif (in_array($dbStatus, ['wfh', 'work from home', 'work_from_home'])) {
                    $status = 'WFH Approved';
                    $wfhDays++;
                    if ($isPastOrToday) {
                        $presentDays++;
                    }
                } else {
                    if ($log->sessions && $log->sessions->isNotEmpty()) {
                        $status = 'Present';
                        $presentDays++;
                    } else {
                        $status = 'Absent';
                        $absentDays++;
                    }
                }
            } else {
                // No punch log for this day
                if ($isWeekOff) {
                    $status = 'Weekend Off';
                    $weekOffDays++;
                } elseif ($isHoliday || $holidayMatch) {
                    $status = 'Holiday';
                    $holidayDays++;
                } elseif ($activeLeave && strtolower($activeLeave->status) === 'approved') {
                    $status = 'On Leave';
                    $leaveDays++;
                } elseif ($activeLeave && strtolower($activeLeave->status) === 'pending') {
                    $status = 'Leave Pending';
                    $pendingLeaveDays++;
                } elseif ($wfhMatch && strtolower($wfhMatch->status) === 'approved') {
                    $status = 'WFH Approved';
                    $wfhDays++;
                    if ($isPastOrToday) {
                        $presentDays++;
                    }
                } elseif ($wfhMatch && strtolower($wfhMatch->status) === 'pending') {
                    $status = 'WFH Pending';
                    $pendingWfhDays++;
                } else {
                    if ($isPastOrToday) {
                        $status = 'Not Marked';
                        $notMarkedDays++;
                    } else {
                        $status = 'Scheduled'; // Future working day
                    }
                }
            }

            $daysData[] = [
                'date' => $dateStr,
                'day_name' => $currentDate->format('D'),
                'month_name' => $currentDate->format('M'),
                'day_num' => $currentDate->day,
                'status' => $status,
                'shift' => $shiftName,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'work_duration' => $workDuration > 0 ? (floor($workDuration / 60) . 'h ' . ($workDuration % 60) . 'm') : null,
                'work_minutes' => $workDuration,
                'late_minutes' => $lateMinutes,
                'early_exit_minutes' => $earlyExitMinutes,
                'is_weekoff' => $isWeekOff,
                'is_holiday' => ($isHoliday || $holidayMatch) ? true : false,
                'holiday_title' => $holidayMatch ? $holidayMatch->name : ($isHoliday ? ($holidays->first(fn($h) => (Carbon::parse($h->date)->toDateString() === $dateStr))->name ?? 'Public Holiday') : null),
                'leave_title' => $activeLeave ? $activeLeave->leaveType->name ?? 'Leave' : null,
                'wfh_status' => $wfhMatch ? $wfhMatch->status : null,
                'sessions' => $sessions,
            ];

            $currentDate->addDay();
        }

        // Summary Calculations
        $avgInTimeStr = '--:--';
        if (count($inTimes) > 0) {
            $avgMinutes = round(array_sum($inTimes) / count($inTimes));
            $avgHour = floor($avgMinutes / 60);
            $avgMin = $avgMinutes % 60;
            $avgInTimeStr = Carbon::createFromTime($avgHour, $avgMin)->format('h:i A');
        }

        $avgCheatTimeStr = '0 mins';
        if (count($daysData) > 0) {
            $totalLogsCount = $logs->count();
            if ($totalLogsCount > 0) {
                $avgCheatMins = round($totalDeviationMinutes / $totalLogsCount);
                $avgCheatTimeStr = $avgCheatMins . ' mins';
            }
        }

        $avgWorkHours = 0;
        $minWorkHours = 0;
        $maxWorkHours = 0;
        if (count($workDurations) > 0) {
            $avgWorkHours = array_sum($workDurations) / count($workDurations) / 60;
            $minWorkHours = min($workDurations) / 60;
            $maxWorkHours = max($workDurations) / 60;
        }

        $attendancePercentage = $workingDaysSoFar > 0 ? round(($presentDays / $workingDaysSoFar) * 100, 1) : 0;

        return Inertia::render('Admin/Attendance/EmployeeDetails', [
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'code' => $employee->employee_code,
                'department' => $employee->department->name ?? 'Operations',
                'designation' => $employee->designation ?? 'Operative',
            ],
            'selectedYear' => (int) $year,
            'summary' => [
                'total_working_days' => $workingDaysSoFar + $remainingWorkingDays,
                'working_days_so_far' => $workingDaysSoFar,
                'remaining_working_days' => $remainingWorkingDays,
                'present_days' => $presentDays,
                'late_days' => $lateDays,
                'absent_days' => $absentDays,
                'not_marked_days' => $notMarkedDays,
                'leave_days' => $leaveDays,
                'pending_leave_days' => $pendingLeaveDays,
                'wfh_days' => $wfhDays,
                'pending_wfh_days' => $pendingWfhDays,
                'holiday_days' => $holidayDays,
                'week_off_days' => $weekOffDays,
                'avg_in_time' => $avgInTimeStr,
                'avg_cheat_time' => $avgCheatTimeStr,
                'avg_work_hours' => round($avgWorkHours, 1) . ' hrs',
                'min_work_hours' => round($minWorkHours, 1) . ' hrs',
                'max_work_hours' => round($maxWorkHours, 1) . ' hrs',
                'attendance_percentage' => $attendancePercentage,
                'sick_allowed' => $sickTotal,
                'sick_utilized' => $sickUtilized,
                'sick_pending' => $sickPending,
                'paid_allowed' => $paidTotal,
                'paid_utilized' => $paidUtilized,
                'paid_pending' => $paidPending,
            ],
            'days' => $daysData,
            'requestsList' => $requestsList,
            'leaveLedgers' => $leaveLedgers,
            'holidaysList' => $holidays->map(fn($h) => [
                'name' => $h->name,
                'date' => Carbon::parse($h->date)->format('d M Y') . ($h->end_date && $h->end_date !== $h->date ? ' to ' . Carbon::parse($h->end_date)->format('d M Y') : ''),
                'type' => $h->type
            ])
        ]);
    }

    public function getEmployeeYearlyDetails(Request $request, Employee $employee)
    {
        $year = $request->input('year', Carbon::now()->year);
        $startOfYear = Carbon::create($year, 1, 1)->toDateString();
        $endOfYear = Carbon::create($year, 12, 31)->toDateString();

        // 1. Fetch Logs
        $logs = AttendanceLog::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfYear, $endOfYear])
            ->with(['sessions'])
            ->orderBy('date', 'desc')
            ->get();

        // 2. Attended Days
        $daysAttended = $logs->filter(function($log) {
            return in_array($log->status, ['Present', 'Late']) || ($log->sessions && $log->sessions->isNotEmpty());
        })->count();

        // 3. Holidays Taken (Public + RH Approved/Pending)
        $holidays = \App\Models\Holiday::where(function($q) use ($startOfYear, $endOfYear) {
            $q->where('date', '<=', $endOfYear)
              ->where('end_date', '>=', $startOfYear);
        })->get();
        $publicHolidaysCount = 0;
        foreach ($holidays as $h) {
            if ($h->type !== 'Restricted' && (empty($h->applies_to_locations) || in_array($employee->location_id, $h->applies_to_locations))) {
                $hStart = Carbon::parse($h->date);
                $hEnd = Carbon::parse($h->end_date ?? $h->date);
                $start = $hStart->gt(Carbon::parse($startOfYear)) ? $hStart : Carbon::parse($startOfYear);
                $end = $hEnd->lt(Carbon::parse($endOfYear)) ? $hEnd : Carbon::parse($endOfYear);
                $publicHolidaysCount += $start->diffInDays($end) + 1;
            }
        }

        // RH/Floating Holiday requests
        $approvedRH = 0;
        $pendingRH = 0;
        if ($employee->user_id) {
            $approvedRH = \App\Models\FloatingHolidayRequest::where('user_id', $employee->user_id)
                ->where('status', 'Approved')
                ->whereHas('holiday', function($q) use ($startOfYear, $endOfYear) {
                    $q->whereBetween('date', [$startOfYear, $endOfYear]);
                })->count();

            $pendingRH = \App\Models\FloatingHolidayRequest::where('user_id', $employee->user_id)
                ->where('status', 'Pending')
                ->whereHas('holiday', function($q) use ($startOfYear, $endOfYear) {
                    $q->whereBetween('date', [$startOfYear, $endOfYear]);
                })->count();
        }

        // 4. Leaves Taken
        $approvedLeaveDays = \App\Models\LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'Approved')
            ->where(function($q) use ($startOfYear, $endOfYear) {
                $q->whereBetween('start_date', [$startOfYear, $endOfYear])
                  ->orWhereBetween('end_date', [$startOfYear, $endOfYear]);
            })
            ->get()
            ->sum(function($leave) use ($startOfYear, $endOfYear) {
                $start = Carbon::parse(max(Carbon::parse($leave->start_date)->toDateString(), $startOfYear));
                $end = Carbon::parse(min(Carbon::parse($leave->end_date)->toDateString(), $endOfYear));
                return $start->diffInDays($end) + 1;
            });

        $pendingLeaveDays = \App\Models\LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'Pending')
            ->where(function($q) use ($startOfYear, $endOfYear) {
                $q->whereBetween('start_date', [$startOfYear, $endOfYear])
                  ->orWhereBetween('end_date', [$startOfYear, $endOfYear]);
            })
            ->get()
            ->sum(function($leave) use ($startOfYear, $endOfYear) {
                $start = Carbon::parse(max(Carbon::parse($leave->start_date)->toDateString(), $startOfYear));
                $end = Carbon::parse(min(Carbon::parse($leave->end_date)->toDateString(), $endOfYear));
                return $start->diffInDays($end) + 1;
            });

        // 5. WFH Requests
        $approvedWfhDays = \App\Models\WfhRequest::where('employee_id', $employee->id)
            ->where('status', 'Approved')
            ->whereBetween('date', [$startOfYear, $endOfYear])
            ->count();

        $pendingWfhDays = \App\Models\WfhRequest::where('employee_id', $employee->id)
            ->where('status', 'Pending')
            ->whereBetween('date', [$startOfYear, $endOfYear])
            ->count();

        // 6. Timings & Averages
        $inTimes = [];
        $workDurations = [];
        $totalDeviationMinutes = 0;

        foreach ($logs as $log) {
            if ($log->sessions && $log->sessions->isNotEmpty()) {
                $firstSession = $log->sessions->first();
                if ($firstSession->in_time) {
                    $inTime = Carbon::parse($firstSession->in_time);
                    $inTimes[] = ($inTime->hour * 60) + $inTime->minute;
                }
            }
            if ($log->total_work_minutes > 0) {
                $workDurations[] = $log->total_work_minutes;
            }
            $totalDeviationMinutes += ($log->late_minutes ?? 0) + ($log->early_leaving_minutes ?? 0);
        }

        $avgInTimeStr = '--:--';
        if (count($inTimes) > 0) {
            $avgMinutes = round(array_sum($inTimes) / count($inTimes));
            $avgHour = floor($avgMinutes / 60);
            $avgMin = $avgMinutes % 60;
            $avgInTimeStr = Carbon::createFromTime($avgHour, $avgMin)->format('h:i A');
        }

        $avgCheatTimeStr = '0 mins';
        if ($logs->isNotEmpty()) {
            $avgCheatMins = round($totalDeviationMinutes / $logs->count());
            $avgCheatTimeStr = $avgCheatMins . ' mins';
        }

        $avgWorkHours = 0;
        $minWorkHours = 0;
        $maxWorkHours = 0;

        if (count($workDurations) > 0) {
            $avgWorkHours = array_sum($workDurations) / count($workDurations) / 60;
            $minWorkHours = min($workDurations) / 60;
            $maxWorkHours = max($workDurations) / 60;
        }

        // 7. Format Daily Logs
        $formattedLogs = $logs->map(function($log) {
            $checkIn = null;
            $checkOut = null;
            if ($log->sessions && $log->sessions->isNotEmpty()) {
                $first = $log->sessions->first();
                $last = $log->sessions->last();
                $checkIn = $first->in_time ? Carbon::parse($first->in_time)->format('h:i A') : '--:--';
                $checkOut = $last->out_time ? Carbon::parse($last->out_time)->format('h:i A') : 'Active';
            }

            // Map all sub-sessions
            $sessionsDetails = [];
            foreach ($log->sessions as $session) {
                $sessionsDetails[] = [
                    'in' => $session->in_time ? Carbon::parse($session->in_time)->format('h:i A') : '--:--',
                    'out' => $session->out_time ? Carbon::parse($session->out_time)->format('h:i A') : 'Active',
                    'in_ip' => $session->in_ip ?? '-',
                    'out_ip' => $session->out_ip ?? '-',
                ];
            }

            // Resolve shift name
            $shiftName = 'Standard';
            if ($log->shift_id) {
                $s = \App\Models\Shift::find($log->shift_id);
                if ($s) $shiftName = $s->name;
            }

            return [
                'date' => Carbon::parse($log->date)->format('d M Y'),
                'status' => $log->status,
                'shift' => $shiftName,
                'check_in' => $checkIn ?? '--:--',
                'check_out' => $checkOut ?? '--:--',
                'work_duration' => $log->total_work_minutes > 0 ? (floor($log->total_work_minutes / 60) . 'h ' . ($log->total_work_minutes % 60) . 'm') : '--',
                'late_minutes' => $log->late_minutes ?? 0,
                'early_exit_minutes' => $log->early_leaving_minutes ?? 0,
                'sessions' => $sessionsDetails,
            ];
        });

        return response()->json([
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'code' => $employee->employee_code,
                'department' => $employee->department->name ?? 'Operations',
                'designation' => $employee->designation ?? 'Operative',
            ],
            'summary' => [
                'days_attended' => $daysAttended,
                'public_holidays' => $publicHolidaysCount,
                'approved_rh' => $approvedRH,
                'pending_rh' => $pendingRH,
                'approved_leave_days' => $approvedLeaveDays,
                'pending_leave_days' => $pendingLeaveDays,
                'approved_wfh_days' => $approvedWfhDays,
                'pending_wfh_days' => $pendingWfhDays,
                'avg_in_time' => $avgInTimeStr,
                'avg_cheat_time' => $avgCheatTimeStr,
                'avg_work_hours' => number_format($avgWorkHours, 2) . ' hrs',
                'min_work_hours' => number_format($minWorkHours, 2) . ' hrs',
                'max_work_hours' => number_format($maxWorkHours, 2) . ' hrs',
            ],
            'logs' => $formattedLogs,
        ]);
    }

    public function exportEmployeeYearlyDetails(Request $request, Employee $employee)
    {
        $year = $request->input('year', Carbon::now()->year);
        $startOfYear = Carbon::create($year, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($year, 12, 31)->endOfDay();

        // 1. Fetch Logs keyed by date
        $logs = AttendanceLog::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfYear->toDateString(), $endOfYear->toDateString()])
            ->with(['sessions'])
            ->get()
            ->keyBy(function($log) {
                return Carbon::parse($log->date)->toDateString();
            });

        // 2. Fetch overlapping leaves
        $leaves = \App\Models\LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', ['Approved', 'Pending'])
            ->where(function($q) use ($startOfYear, $endOfYear) {
                $q->whereBetween('start_date', [$startOfYear->toDateString(), $endOfYear->toDateString()])
                  ->orWhereBetween('end_date', [$startOfYear->toDateString(), $endOfYear->toDateString()]);
            })
            ->with('leaveType')
            ->get();

        // 3. Fetch WFH requests
        $wfhRequests = \App\Models\WfhRequest::where('employee_id', $employee->id)
            ->whereIn('status', ['Approved', 'Pending'])
            ->whereBetween('date', [$startOfYear->toDateString(), $endOfYear->toDateString()])
            ->get()
            ->keyBy(fn($w) => Carbon::parse($w->date)->toDateString());

        // 4. Fetch Holidays
        $holidays = \App\Models\Holiday::where(function($q) use ($startOfYear, $endOfYear) {
            $q->where('date', '<=', $endOfYear->toDateString())
              ->where('end_date', '>=', $startOfYear->toDateString());
        })->get();

        // 5. Fetch Floating Requests
        $floatingRequests = collect();
        if ($employee->user_id) {
            $floatingRequests = \App\Models\FloatingHolidayRequest::where('user_id', $employee->user_id)
                ->whereIn('status', ['Approved', 'Pending'])
                ->with('holiday')
                ->get()
                ->keyBy(function($r) {
                    return $r->holiday ? Carbon::parse($r->holiday->date)->toDateString() : '';
                });
        }

        // Resolvers
        $resolver = app(\App\Services\Attendance\WorkingDayResolverService::class);

        $today = Carbon::today();
        if ($today->year === (int)$year) {
            $boundaryDate = $today->copy();
        } elseif ((int)$year < $today->year) {
            $boundaryDate = $endOfYear->copy();
        } else {
            $boundaryDate = $startOfYear->copy()->subDay();
        }

        // Build 365 Days matching UI resolution logic exactly
        $daysData = [];
        $currentDate = $startOfYear->copy();
        
        $workingDaysSoFar = 0;
        $remainingWorkingDays = 0;
        $presentDays = 0;
        $lateDays = 0;
        $absentDays = 0;
        $leaveDays = 0;
        $wfhDays = 0;
        $notMarkedDays = 0;
        $holidayDays = 0;
        $weekOffDays = 0;

        $inTimes = [];
        $workDurations = [];
        $totalDeviationMinutes = 0;

        while ($currentDate->lte($endOfYear)) {
            $dateStr = $currentDate->toDateString();
            $log = $logs->get($dateStr);
            $isPastOrToday = $currentDate->lte($boundaryDate);

            $isWeekOff = $resolver->isWeekOff($currentDate, $employee);
            $isHoliday = $resolver->isHoliday($currentDate, $employee);
            
            $generalHoliday = $holidays->first(function($h) use ($dateStr, $employee) {
                $hDate = Carbon::parse($h->date)->toDateString();
                $hEndDate = Carbon::parse($h->end_date ?? $h->date)->toDateString();
                return $dateStr >= $hDate && $dateStr <= $hEndDate && $h->type !== 'Restricted' &&
                    (empty($h->applies_to_locations) || in_array($employee->location_id, $h->applies_to_locations));
            });

            $floatingRequest = $floatingRequests->get($dateStr);
            $hasApprovedRH = $floatingRequest && $floatingRequest->status === 'Approved';
            $holidayMatch = $generalHoliday ?: ($hasApprovedRH ? $floatingRequest->holiday : null);

            $activeLeave = $leaves->first(function($l) use ($dateStr) {
                $leaveStart = Carbon::parse($l->start_date)->toDateString();
                $leaveEnd   = Carbon::parse($l->end_date)->toDateString();
                return $dateStr >= $leaveStart && $dateStr <= $leaveEnd;
            });

            $wfhMatch = $wfhRequests->get($dateStr);
            $isWorkingDay = !$isWeekOff && !$isHoliday && !$holidayMatch;

            if ($isWorkingDay) {
                if ($isPastOrToday) {
                    $workingDaysSoFar++;
                } else {
                    $remainingWorkingDays++;
                }
            }

            $checkIn = null;
            $checkOut = null;
            $workDuration = 0;
            $lateMinutes = 0;
            $earlyExitMinutes = 0;
            $shiftName = 'By Policy';
            
            if ($log) {
                $shiftName = $log->shift ? $log->shift->name : 'By Policy';
                $workDuration = $log->total_work_minutes;
                $lateMinutes = $log->late_minutes ?? 0;
                $earlyExitMinutes = $log->early_leaving_minutes ?? 0;

                if ($log->sessions && $log->sessions->isNotEmpty()) {
                    $first = $log->sessions->first();
                    $last = $log->sessions->last();
                    $checkIn = $first->in_time ? Carbon::parse($first->in_time)->format('h:i A') : '--:--';
                    $checkOut = $last->out_time ? Carbon::parse($last->out_time)->format('h:i A') : 'Active';

                    if ($first->in_time) {
                        $inTime = Carbon::parse($first->in_time);
                        $inTimes[] = ($inTime->hour * 60) + $inTime->minute;
                    }
                    if ($log->total_work_minutes > 0) {
                        $workDurations[] = $log->total_work_minutes;
                    }
                }
                $totalDeviationMinutes += $lateMinutes + $earlyExitMinutes;

                $dbStatus = strtolower($log->status);
                if ($dbStatus === 'late' || $log->is_late) {
                    $status = 'Late';
                    $lateDays++;
                    $presentDays++;
                } elseif ($dbStatus === 'present') {
                    $status = 'Present';
                    $presentDays++;
                } elseif ($dbStatus === 'absent') {
                    $status = 'Absent';
                    $absentDays++;
                } elseif (in_array($dbStatus, ['half day', 'half-day', 'half_day'])) {
                    $status = 'Half Day';
                    $presentDays += 0.5;
                } elseif (in_array($dbStatus, ['on leave', 'on-leave', 'leave'])) {
                    $status = 'On Leave';
                    $leaveDays++;
                } elseif (in_array($dbStatus, ['holiday'])) {
                    $status = 'Holiday';
                    $holidayDays++;
                } elseif (in_array($dbStatus, ['wfh', 'work from home', 'work_from_home'])) {
                    $status = 'WFH Approved';
                    $wfhDays++;
                    if ($isPastOrToday) {
                        $presentDays++;
                    }
                } else {
                    if ($log->sessions && $log->sessions->isNotEmpty()) {
                        $status = 'Present';
                        $presentDays++;
                    } else {
                        $status = 'Absent';
                        $absentDays++;
                    }
                }
            } else {
                if ($isWeekOff) {
                    $status = 'Weekend Off';
                    $weekOffDays++;
                } elseif ($isHoliday || $holidayMatch) {
                    $status = 'Holiday';
                    $holidayDays++;
                } elseif ($activeLeave && strtolower($activeLeave->status) === 'approved') {
                    $status = 'On Leave';
                    $leaveDays++;
                } elseif ($activeLeave && strtolower($activeLeave->status) === 'pending') {
                    $status = 'Leave Pending';
                } elseif ($wfhMatch && strtolower($wfhMatch->status) === 'approved') {
                    $status = 'WFH Approved';
                    $wfhDays++;
                    if ($isPastOrToday) {
                        $presentDays++;
                    }
                } elseif ($wfhMatch && strtolower($wfhMatch->status) === 'pending') {
                    $status = 'WFH Pending';
                } else {
                    if ($isPastOrToday) {
                        $status = 'Not Marked';
                        $notMarkedDays++;
                    } else {
                        $status = 'Scheduled';
                    }
                }
            }

            $daysData[] = [
                'date' => $dateStr,
                'day_name' => $currentDate->format('D'),
                'month_name' => $currentDate->format('M'),
                'day_num' => $currentDate->day,
                'status' => $status,
                'shift' => $shiftName,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'work_duration' => $workDuration > 0 ? (floor($workDuration / 60) . 'h ' . ($workDuration % 60) . 'm') : null,
                'late_minutes' => $lateMinutes,
                'early_exit_minutes' => $earlyExitMinutes,
                'is_weekoff' => $isWeekOff,
                'is_holiday' => ($isHoliday || $holidayMatch) ? true : false,
            ];

            $currentDate->addDay();
        }

        // Summary Calculations
        $avgInTimeStr = '--:--';
        if (count($inTimes) > 0) {
            $avgMinutes = round(array_sum($inTimes) / count($inTimes));
            $avgHour = floor($avgMinutes / 60);
            $avgMin = $avgMinutes % 60;
            $avgInTimeStr = Carbon::createFromTime($avgHour, $avgMin)->format('h:i A');
        }

        $avgCheatMins = count($inTimes) > 0 ? round($totalDeviationMinutes / count($inTimes)) : 0;
        $avgWorkHours = count($workDurations) > 0 ? (array_sum($workDurations) / count($workDurations) / 60) : 0;

        // Fetch Leave Ledgers for the employee & year
        $tenantId = auth()->user()->tenant_id ?? $employee->tenant_id ?? null;
        $leaveTypeQuery = \App\Models\LeaveType::query();
        if ($tenantId) {
            $leaveTypeQuery->where('tenant_id', $tenantId);
        }
        $leaveTypes = $leaveTypeQuery->get();
        $leaveLedgers = [];

        foreach ($leaveTypes as $type) {
            $isValid = $type->is_active;
            if ($isValid) {
                $yearStart = Carbon::create($year, 1, 1);
                $yearEnd = Carbon::create($year, 12, 31);
                
                if ($type->applicable_from && Carbon::parse($type->applicable_from)->gt($yearEnd)) {
                    $isValid = false;
                }
                if ($type->applicable_to && Carbon::parse($type->applicable_to)->lt($yearStart)) {
                    $isValid = false;
                }
            }
            
            if (!$isValid) {
                continue;
            }
            
            $balance = \App\Models\LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_type_id', $type->id)
                ->where('year', $year)
                ->first();
            $allowed = $balance ? (float) $balance->total_days : (float) $type->days_allowed_per_year;
            
            $utilized = \App\Models\LeaveRequest::where('employee_id', $employee->id)
                ->where('leave_type_id', $type->id)
                ->whereIn(\DB::raw('LOWER(status)'), ['approved'])
                ->where(function($q) use ($startOfYear, $endOfYear) {
                    $q->whereBetween('start_date', [$startOfYear->toDateString(), $endOfYear->toDateString()])
                      ->orWhereBetween('end_date', [$startOfYear->toDateString(), $endOfYear->toDateString()]);
                })
                ->get()
                ->sum('total_days');
                
            $pending = \App\Models\LeaveRequest::where('employee_id', $employee->id)
                ->where('leave_type_id', $type->id)
                ->whereIn(\DB::raw('LOWER(status)'), ['pending'])
                ->where(function($q) use ($startOfYear, $endOfYear) {
                    $q->whereBetween('start_date', [$startOfYear->toDateString(), $endOfYear->toDateString()])
                      ->orWhereBetween('end_date', [$startOfYear->toDateString(), $endOfYear->toDateString()]);
                })
                ->get()
                ->sum('total_days');
                
            $leaveLedgers[] = [
                'name' => $type->name,
                'code' => $type->code,
                'allowed' => $allowed,
                'utilized' => $utilized,
                'pending' => $pending,
                'remaining' => max(0, $allowed - $utilized),
            ];
        }

        // Compute Month-by-Month summary counts from resolved days
        $monthlyData = [];
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        foreach ($months as $num => $name) {
            $monthShort = substr($name, 0, 3);
            $monthDays = collect($daysData)->filter(function($d) use ($monthShort) {
                return $d['month_name'] === $monthShort;
            });

            $present = 0;
            $late = 0;
            $absent = 0;
            $leaves = 0;
            $wfh = 0;
            $notMarked = 0;
            $workingDays = 0;

            foreach ($monthDays as $d) {
                if (!$d['is_weekoff'] && !$d['is_holiday']) {
                    $workingDays++;
                }

                if ($d['status'] === 'Present') {
                    $present++;
                } elseif ($d['status'] === 'Late') {
                    $present++;
                    $late++;
                } elseif ($d['status'] === 'Absent') {
                    $absent++;
                } elseif ($d['status'] === 'On Leave') {
                    $leaves++;
                } elseif ($d['status'] === 'WFH Approved') {
                    $wfh++;
                } elseif ($d['status'] === 'Not Marked') {
                    $notMarked++;
                }
            }

            $monthlyData[] = [
                $name,
                $workingDays,
                $present,
                $late,
                $absent,
                $leaves,
                $wfh,
                $notMarked
            ];
        }

        // Approved Restricted Holidays Count
        $approvedRH = 0;
        if ($employee->user_id) {
            $approvedRH = \App\Models\FloatingHolidayRequest::where('user_id', $employee->user_id)
                ->where('status', 'Approved')
                ->whereHas('holiday', function($q) use ($startOfYear, $endOfYear) {
                    $q->whereBetween('date', [$startOfYear->toDateString(), $endOfYear->toDateString()]);
                })->count();
        }

        $summaryMetrics = [
            'days_attended' => $presentDays,
            'approved_leaves' => $leaveDays,
            'approved_wfh' => $wfhDays,
            'approved_rh' => $approvedRH,
            'avg_checkin' => $avgInTimeStr,
            'avg_deviation' => $avgCheatMins,
            'avg_work_hours' => $avgWorkHours,
        ];

        $filename = 'Yearly_Report_' . str_replace(' ', '_', $employee->first_name) . '_' . $year . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\YearlyAttendanceExport($employee, $year, $summaryMetrics, $leaveLedgers, $monthlyData, $daysData),
            $filename
        );
    }

    public function exportLiveMonitorData(Request $request)
    {
        $date = $request->filled('date_from') ? Carbon::parse($request->date_from) : Carbon::today();
        
        $employeesQuery = Employee::with(['department']);
        
        if ($request->filled('department_id')) {
            $employeesQuery->where('department_id', $request->department_id);
        }
        
        if ($request->filled('location_id')) {
            $employeesQuery->where('location_id', $request->location_id);
        }
        
        $employees = $employeesQuery->get();
        
        $logs = AttendanceLog::where('date', $date->toDateString())
            ->with(['sessions'])
            ->get()
            ->keyBy('employee_id');

        $csvData = [];
        $csvData[] = ['LIVE ATTENDANCE MONITOR REPORT - ' . $date->format('d M Y')];
        $csvData[] = [];
        $csvData[] = ['Employee Code', 'Name', 'Department', 'Designation', 'Shift', 'Check-In', 'Status', 'Late arrival', 'Runtime Hours', 'Device', 'IP Address'];

        foreach ($employees as $emp) {
            $log = $logs->get($emp->id);
            $status = 'Absent';
            $checkIn = null;
            $checkOut = null;
            $isLate = 'No';
            $workDuration = 0;
            $deviceInfo = 'Unknown';
            $ipAddress = '-';
            
            if ($log) {
                $status = $log->status;
                $isLate = $log->is_late ? 'Yes' : 'No'; 
                
                if ($log->sessions && $log->sessions->isNotEmpty()) {
                    $firstSession = $log->sessions->first();
                    $in_time = $firstSession->in_time ? (is_string($firstSession->in_time) ? Carbon::parse($firstSession->in_time) : $firstSession->in_time) : null;
                    $checkIn = $in_time ? $in_time->format('h:i A') : null;

                    foreach ($log->sessions as $index => $session) {
                        $in = $session->in_time ? (is_string($session->in_time) ? Carbon::parse($session->in_time) : $session->in_time) : null;
                        if (!$in) continue;
                        $out = $session->out_time ? (is_string($session->out_time) ? Carbon::parse($session->out_time) : $session->out_time) : Carbon::now();
                        
                        $workDuration += $in->diffInMinutes($out);

                        if ($index === $log->sessions->count() - 1) {
                             $ipAddress = $session->ip_address ?? '-';
                             $ua = $session->user_agent ?? '';
                             if (str_contains($ua, 'Mobile')) $deviceInfo = 'Mobile';
                             elseif (str_contains($ua, 'Windows')) $deviceInfo = 'Windows PC';
                             elseif (str_contains($ua, 'Mac')) $deviceInfo = 'Mac';
                             else $deviceInfo = 'Desktop';
                        }
                    }
                }
            }

            $shift = $this->attendanceRegistry->getShiftForDate($emp, $date);

            $csvData[] = [
                $emp->employee_code ?? '-',
                $emp->first_name . ' ' . $emp->last_name,
                $emp->department->name ?? '-',
                $emp->designation ?? 'Employee',
                $shift ? $shift->name : 'By Policy',
                $checkIn ?? '-',
                $status,
                $isLate,
                number_format($workDuration / 60, 2) . ' hrs',
                $deviceInfo,
                $ipAddress,
            ];
        }

        // Return CSV
        $output = '';
        foreach ($csvData as $row) {
            $output .= implode(',', array_map(function($val) {
                return '"' . str_replace('"', '""', $val) . '"';
            }, $row)) . "\n";
        }

        $filename = 'Live_Monitor_Report_' . $date->format('Y-m-d') . '.csv';

        return response($output, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function getDeviceStats()
    {
        $dbDevices = \App\Models\BiometricDevice::all();
        $totalDevices = $dbDevices->count();
        $activeDevices = $dbDevices->where('status', 'online')->count();
        $offlineDevices = $dbDevices->where('status', '!=', 'online')->count();

        // Calculate health score: percentage of active devices. If total is 0, default to 100%.
        $healthScore = $totalDevices > 0 ? round(($activeDevices / $totalDevices) * 100) : 100;

        // Build list of devices
        $deviceList = [];
        foreach ($dbDevices as $device) {
            // Count sessions with this device ip or name matching the session ip/source
            $count = \App\Models\AttendanceSession::where(function ($query) use ($device) {
                $query->where('in_ip', $device->ip_address)
                      ->orWhere('out_ip', $device->ip_address)
                      ->orWhere('source', $device->name);
            })->count();

            $status = $device->status === 'online' ? 'healthy' : 'offline';
            $color = $device->status === 'online' ? 'emerald' : 'red';

            $deviceList[] = [
                'name' => $device->name,
                'count' => $count,
                'status' => $status,
                'color' => $color
            ];
        }

        // If there are no devices in the DB, fallback to some nice default virtual system nodes
        if (empty($deviceList)) {
            $deviceList = [
                [ 'name' => 'Mobile App Node', 'count' => 145, 'status' => 'healthy', 'color' => 'emerald' ],
                [ 'name' => 'Edge Browser Terminal', 'count' => 89, 'status' => 'healthy', 'color' => 'emerald' ],
                [ 'name' => 'Biometric Scanner Alpha', 'count' => 45, 'status' => 'healthy', 'color' => 'emerald' ],
                [ 'name' => 'Gate Entry Sensor 02', 'count' => 0, 'status' => 'offline', 'color' => 'red' ]
            ];
            $totalDevices = count($deviceList);
            $activeDevices = count(array_filter($deviceList, fn($d) => $d['status'] === 'healthy'));
            $offlineDevices = $totalDevices - $activeDevices;
            $healthScore = round(($activeDevices / $totalDevices) * 100);
        }

        return response()->json([
            'total_devices' => $totalDevices,
            'active_devices' => $activeDevices,
            'offline_devices' => $offlineDevices,
            'health_score' => $healthScore,
            'devices' => $deviceList
        ]);
    }
    // ─────────────────────────────────────────────────────────────────────────
    // ALL-EMPLOYEE ATTENDANCE MATRIX  (daily_log tab)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Build the per-employee matrix data for the given date range.
     * Used by AttendancePolicyController for the daily_log tab.
     */
    public function getAttendanceMatrixData(Request $request, int $tenantId): array
    {
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::today()->startOfDay();
        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::today()->endOfDay();

        $dateFromStr = $dateFrom->toDateString();
        $dateToStr   = $dateTo->toDateString();
        $today       = Carbon::today()->toDateString();

        // ── 1. Build employee query ──────────────────────────────────────────
        $empQuery = Employee::with(['department'])
            ->where('status', 'active')
            ->where('tenant_id', $tenantId);

        if ($request->filled('department_id')) {
            $empQuery->where('department_id', $request->department_id);
        }
        if ($request->filled('location_id')) {
            $empQuery->where('location_id', $request->location_id);
        }
        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $empQuery->where(function ($q) use ($term) {
                $q->where('first_name', 'like', $term)
                  ->orWhere('last_name', 'like', $term)
                  ->orWhere('employee_code', 'like', $term);
            });
        }

        $allEmployees = $empQuery->get();
        $employeeIds  = $allEmployees->pluck('id')->toArray();

        // ── 2. Fetch all attendance logs in range ────────────────────────────
        $allLogs = AttendanceLog::with(['sessions', 'shift'])
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$dateFromStr, $dateToStr])
            ->get()
            ->groupBy('employee_id');

        // ── 3. Fetch leave requests (Approved + Pending) in range ────────────
        $allLeaves = \App\Models\LeaveRequest::with('leaveType')
            ->whereIn('employee_id', $employeeIds)
            ->whereIn('status', ['Approved', 'Pending'])
            ->where(function ($q) use ($dateFromStr, $dateToStr) {
                $q->whereBetween('start_date', [$dateFromStr, $dateToStr])
                  ->orWhereBetween('end_date', [$dateFromStr, $dateToStr])
                  ->orWhere(function ($q2) use ($dateFromStr, $dateToStr) {
                      $q2->where('start_date', '<=', $dateFromStr)
                         ->where('end_date', '>=', $dateToStr);
                  });
            })
            ->get()
            ->groupBy('employee_id');

        // ── 4. Fetch WFH requests (Approved + Pending) in range ─────────────
        $allWfh = \App\Models\WfhRequest::whereIn('employee_id', $employeeIds)
            ->whereIn('status', ['Approved', 'Pending'])
            ->whereBetween('date', [$dateFromStr, $dateToStr])
            ->get()
            ->groupBy('employee_id');

        // ── 5. Working day resolver ─────────────────────────────────────────
        $resolver = app(\App\Services\Attendance\WorkingDayResolverService::class);

        // Pre-compute all working days in the range (shared skeleton per date)
        $allDatesInRange = [];
        $cur = $dateFrom->copy();
        while ($cur->lte($dateTo) && $cur->toDateString() <= $today) {
            $allDatesInRange[] = $cur->toDateString();
            $cur->addDay();
        }

        // ── 6. Build per-employee summary ───────────────────────────────────
        $matrixRows = [];

        foreach ($allEmployees as $emp) {
            $empLogs   = $allLogs->get($emp->id, collect());
            $empLeaves = $allLeaves->get($emp->id, collect());
            $empWfh    = $allWfh->get($emp->id, collect());

            $logsByDate = $empLogs->keyBy(fn ($l) => Carbon::parse($l->date)->toDateString());
            $wfhByDate  = $empWfh->keyBy(fn ($w) => Carbon::parse($w->date)->toDateString());

            $workingDays  = 0;
            $presentDays  = 0;
            $lateDays     = 0;
            $leaveDays    = 0;
            $wfhDays      = 0;
            $notMarkedDays = 0;

            foreach ($allDatesInRange as $dateStr) {
                $dateCarbon  = Carbon::parse($dateStr);
                $isWeekOff   = $resolver->isWeekOff($dateCarbon, $emp);
                $isHoliday   = $resolver->isHoliday($dateCarbon, $emp);

                if ($isWeekOff || $isHoliday) continue;
                $workingDays++;

                $log      = $logsByDate->get($dateStr);
                $wfhMatch = $wfhByDate->get($dateStr);

                // Active leave check with correct date cast
                $activeLeave = $empLeaves->first(function ($l) use ($dateStr) {
                    $start = Carbon::parse($l->start_date)->toDateString();
                    $end   = Carbon::parse($l->end_date)->toDateString();
                    return $dateStr >= $start && $dateStr <= $end;
                });

                if ($log) {
                    $dbStatus = strtolower($log->status);
                    if ($dbStatus === 'late' || $log->is_late) {
                        $presentDays++;
                        $lateDays++;
                    } elseif (in_array($dbStatus, ['present'])) {
                        $presentDays++;
                    } elseif (in_array($dbStatus, ['wfh', 'work from home', 'work_from_home'])) {
                        $wfhDays++;
                        $presentDays++;
                    } elseif (in_array($dbStatus, ['on leave', 'on-leave', 'leave'])) {
                        $leaveDays++;
                    } elseif ($log->sessions && $log->sessions->isNotEmpty()) {
                        $presentDays++;
                    } else {
                        $notMarkedDays++;
                    }
                } elseif ($activeLeave) {
                    if (strtolower($activeLeave->status) === 'approved') {
                        $leaveDays++;
                    }
                    // Pending leave: not counted in leave; treated as pending (shown in tooltip)
                } elseif ($wfhMatch && strtolower($wfhMatch->status) === 'approved') {
                    $wfhDays++;
                    $presentDays++;
                } else {
                    $notMarkedDays++;
                }
            }

            // ── Today's snapshot ─────────────────────────────────────────────
            $todayLog   = $logsByDate->get($today);
            $todayWfh   = $wfhByDate->get($today);
            $todayLeave = $empLeaves->first(function ($l) use ($today) {
                $start = Carbon::parse($l->start_date)->toDateString();
                $end   = Carbon::parse($l->end_date)->toDateString();
                return $today >= $start && $today <= $end;
            });

            $todayStatus  = 'Not Marked';
            $todayCheckIn = null;
            $todayCheckOut = null;
            $todayShift   = 'By Policy';

            if ($todayLog) {
                $todayShift = $todayLog->shift?->name ?? 'By Policy';
                if ($todayLog->sessions && $todayLog->sessions->isNotEmpty()) {
                    $first  = $todayLog->sessions->first();
                    $last   = $todayLog->sessions->last();
                    $todayCheckIn  = $first->in_time  ? Carbon::parse($first->in_time)->format('h:i A') : null;
                    $todayCheckOut = $last->out_time  ? Carbon::parse($last->out_time)->format('h:i A') : 'Active';
                }
                $dbSt = strtolower($todayLog->status);
                if ($dbSt === 'late' || $todayLog->is_late) {
                    $todayStatus = 'Late';
                } elseif ($dbSt === 'present') {
                    $todayStatus = 'Present';
                } elseif (in_array($dbSt, ['wfh', 'work from home', 'work_from_home'])) {
                    $todayStatus = 'WFH';
                } elseif (in_array($dbSt, ['on leave', 'leave'])) {
                    $todayStatus = 'On Leave';
                } elseif ($todayLog->sessions && $todayLog->sessions->isNotEmpty()) {
                    $todayStatus = 'Present';
                } else {
                    $todayStatus = 'Absent';
                }
            } elseif ($todayLeave) {
                $todayStatus = strtolower($todayLeave->status) === 'approved' ? 'On Leave' : 'Leave Pending';
            } elseif ($todayWfh) {
                $todayStatus = strtolower($todayWfh->status) === 'approved' ? 'WFH' : 'WFH Pending';
            } elseif (Carbon::today()->isWeekend()) {
                $todayStatus = 'Weekend Off';
            }

            // ── Pending approvals for tooltip ────────────────────────────────
            $pendingApprovals = [];
            foreach ($empLeaves->where('status', 'Pending') as $pl) {
                $pendingApprovals[] = [
                    'type'    => 'Leave',
                    'label'   => $pl->leaveType?->name ?? 'Leave',
                    'from'    => Carbon::parse($pl->start_date)->toDateString(),
                    'to'      => Carbon::parse($pl->end_date)->toDateString(),
                    'days'    => $pl->total_days,
                    'status'  => 'Pending',
                ];
            }
            foreach ($empWfh->where('status', 'Pending') as $pw) {
                $pendingApprovals[] = [
                    'type'    => 'WFH',
                    'label'   => 'Work From Home',
                    'from'    => Carbon::parse($pw->date)->toDateString(),
                    'to'      => Carbon::parse($pw->date)->toDateString(),
                    'days'    => 1,
                    'status'  => 'Pending',
                ];
            }

            $matrixRows[] = [
                'id'               => $emp->id,
                'name'             => $emp->first_name . ' ' . $emp->last_name,
                'code'             => $emp->employee_code,
                'department'       => $emp->department?->name ?? '-',
                'designation'      => $emp->designation ?? '-',
                'profile_picture'  => $emp->profile_picture,
                // Period summary
                'working_days'     => $workingDays,
                'present_days'     => $presentDays,
                'late_days'        => $lateDays,
                'leave_days'       => $leaveDays,
                'wfh_days'         => $wfhDays,
                'not_marked_days'  => $notMarkedDays,
                // Today snapshot
                'today_status'     => $todayStatus,
                'today_check_in'   => $todayCheckIn,
                'today_check_out'  => $todayCheckOut,
                'today_shift'      => $todayShift,
                // For tooltip
                'pending_approvals' => $pendingApprovals,
            ];
        }

        // ── 7. Apply status filter after computation ─────────────────────────
        if ($request->filled('status')) {
            $filterStatus = $request->status;
            $matrixRows = array_values(array_filter($matrixRows, fn ($r) => $r['today_status'] === $filterStatus));
        }

        // ── 8. Paginate manually ─────────────────────────────────────────────
        $perPage    = 25;
        $page       = max(1, (int) $request->input('page', 1));
        $total      = count($matrixRows);
        $sliced     = array_slice($matrixRows, ($page - 1) * $perPage, $perPage);
        $lastPage   = max(1, (int) ceil($total / $perPage));

        // Build pagination links array
        $links = [];
        for ($p = 1; $p <= $lastPage; $p++) {
            $links[] = [
                'label'  => (string) $p,
                'active' => $p === $page,
                'page'   => $p,
            ];
        }

        // ── 9. Global stats ──────────────────────────────────────────────────
        $matrixStats = [
            'total_present'  => count(array_filter($matrixRows, fn ($r) => in_array($r['today_status'], ['Present', 'Late', 'WFH']))),
            'total_absent'   => count(array_filter($matrixRows, fn ($r) => in_array($r['today_status'], ['Absent', 'Not Marked']))),
            'total_on_leave' => count(array_filter($matrixRows, fn ($r) => in_array($r['today_status'], ['On Leave', 'Leave Pending']))),
            'total_wfh'      => count(array_filter($matrixRows, fn ($r) => in_array($r['today_status'], ['WFH', 'WFH Pending']))),
            'total_pending'  => array_sum(array_map(fn ($r) => count($r['pending_approvals']), $matrixRows)),
            'total_employees' => count($matrixRows),
            'date_from'      => $dateFromStr,
            'date_to'        => $dateToStr,
        ];

        return [
            'matrix_employees' => [
                'data'         => $sliced,
                'current_page' => $page,
                'last_page'    => $lastPage,
                'per_page'     => $perPage,
                'total'        => $total,
                'links'        => $links,
            ],
            'matrix_stats' => $matrixStats,
        ];
    }

    /**
     * Stream the attendance matrix as a CSV download.
     */
    public function exportAttendanceMatrix(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $data = $this->getAttendanceMatrixData($request->merge(['page' => 1, 'per_page' => 9999]), $tenantId);
        // Re-fetch without pagination limit
        // We need all rows — re-run with high per-page
        $dateFrom = $request->filled('date_from') ? $request->date_from : now()->toDateString();
        $dateTo   = $request->filled('date_to')   ? $request->date_to   : now()->toDateString();

        $matrixStats = $data['matrix_stats'];
        $rows        = $data['matrix_employees']['data'];

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="attendance_matrix_' . $dateFrom . '_to_' . $dateTo . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($rows, $dateFrom, $dateTo) {
            $f = fopen('php://output', 'w');

            // BOM for Excel UTF-8
            fputs($f, "\xEF\xBB\xBF");

            fputcsv($f, [
                'Employee Code',
                'Name',
                'Department',
                'Designation',
                'Period From',
                'Period To',
                'Working Days',
                'Present Days',
                'Late Days',
                'Leave Days',
                'WFH Days',
                'Not Marked Days',
                "Today's Status",
                "Today's Check In",
                "Today's Check Out",
                'Pending Approvals',
            ]);

            foreach ($rows as $row) {
                $pending = collect($row['pending_approvals'])->map(fn ($p) =>
                    "{$p['type']}: {$p['label']} ({$p['from']} – {$p['to']}, {$p['days']} day(s))"
                )->implode(' | ');

                fputcsv($f, [
                    $row['code'],
                    $row['name'],
                    $row['department'],
                    $row['designation'],
                    $dateFrom,
                    $dateTo,
                    $row['working_days'],
                    $row['present_days'],
                    $row['late_days'],
                    $row['leave_days'],
                    $row['wfh_days'],
                    $row['not_marked_days'],
                    $row['today_status'],
                    $row['today_check_in'] ?? '-',
                    $row['today_check_out'] ?? '-',
                    $pending ?: 'None',
                ]);
            }
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }
}
