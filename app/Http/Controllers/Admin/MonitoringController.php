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
            case 'monitor':
            default:
                return $this->getMonitorData();
        }
    }

    private function getMonitorData()
    {
        $date = Carbon::today();
        
        // Fetch all active employees
        $employees = Employee::with(['department'])->get();
        
        // Fetch logs for today
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
                
                if ($log->sessions->isNotEmpty()) {
                     $firstSession = $log->sessions->first();
                     $checkIn = $firstSession->in_time ? $firstSession->in_time->format('H:i') : null;

                    // Calculate Work Duration & Breaks
                    foreach ($log->sessions as $index => $session) {
                        $in = $session->in_time;
                        $out = $session->out_time ?? Carbon::now(); // If active, calc till now
                        
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
}
