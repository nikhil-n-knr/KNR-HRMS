<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AttendanceLog;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\StatisticalService;

class AttendanceAnalyticsController extends Controller
{
    /**
     * Display the Analytics Dashboard (Unified Hub).
     */
    public function index()
    {
        return Inertia::render('Admin/Attendance/Insights', [
            'departments' => Department::select('id', 'name')->get(),
            'filters' => request()->all(['start_date', 'end_date', 'employee_id', 'department_id']) // Pre-fill if needed
        ]);
    }

    /**
     * Get JSON Stats for Charts (Optimized & Statistical).
     */
    public function data(Request $request, StatisticalService $statsService)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now()->endOfDay();
        $deptId = $request->input('department_id');
        $empId = $request->input('employee_id');

        // Determine Context: Individual vs Organization
        // If employee_id is set, OR user is essentially an employee viewing their own stats
        $isIndexPage = !$request->filled('employee_id') && auth()->user()->hasRole(['Admin', 'Super Admin', 'Manager']);
        $targetUserId = $isIndexPage ? null : ($empId ?? auth()->id());

        if ($targetUserId) {
            return $this->getIndividualStats($targetUserId, $startDate, $endDate, $statsService);
        }

        return $this->getOrganizationStats($startDate, $endDate, $deptId, $statsService);
    }

    private function getIndividualStats($userId, $startDate, $endDate, StatisticalService $stats)
    {
        $logs = AttendanceLog::where('employee_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        $durations = $logs->pluck('duration')->filter()->values(); 
        $durationsMinutes = $logs->map(function($log) {
            return $this->parseDurationToMinutes($log->duration ?? '00:00:00');
        });

        // Stats
        $meanMinutes = $stats->calculateMean($durationsMinutes);
        $medianMinutes = $stats->calculateMedian($durationsMinutes);
        $stdDev = $stats->calculateStandardDeviation($durationsMinutes);
        
        $consistencyScore = $meanMinutes > 0 ? max(0, 100 - (($stdDev / $meanMinutes) * 100)) : 0;

        // --- GAMIFICATION STATS ---
        // Verify if tables exist to prevent crashing if migration wasn't fully run for gamification tables (though we ran it)
        $points = 0;
        $badges = [];
        $streaks = [];

        try {
            $points = \App\Models\EmployeePoint::where('employee_id', $userId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('points_awarded');
                
            $badges = \App\Models\EmployeeBadge::where('employee_id', $userId)
                ->with('badge')
                ->get()
                ->map(fn($eb) => [
                    'name' => $eb->badge->name ?? 'Badge',
                    'icon' => $eb->badge->icon ?? 'star',
                    'awarded_at' => $eb->awarded_at
                ]);

            $streaks = \App\Models\EmployeeStreak::where('employee_id', $userId)->get();
        } catch (\Exception $e) {
            // Log::warning("Gamification tables might be missing: " . $e->getMessage());
        }

        return response()->json([
            'type' => 'individual',
            'stats' => [
                'mean_hours' => round($meanMinutes / 60, 2),
                'median_hours' => round($medianMinutes / 60, 2),
                'consistency_score' => round($consistencyScore, 1),
                'total_days' => $logs->count(),
                'late_days' => $logs->where('status', 'Late')->count(),
                'total_points' => (int) $points,
                'badges_count' => count($badges),
            ],
            'gamification' => [
                'badges' => $badges,
                'streaks' => $streaks
            ],
            'trend' => [
                'labels' => $logs->map(fn($l) => Carbon::parse($l->date)->format('M d')),
                'data' => $durationsMinutes->map(fn($m) => round($m/60, 2)),
                'mean_line' => round($meanMinutes / 60, 2)
            ]
        ]);
    }

    private function getOrganizationStats($startDate, $endDate, $deptId, StatisticalService $stats)
    {
        $query = AttendanceLog::with('employee.department')
            ->whereBetween('date', [$startDate, $endDate]);

        if ($deptId) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        }

        $logs = $query->get();

        // Group by Date for Trend
        $dailyGroup = $logs->groupBy('date');
        $trendLabels = $dailyGroup->keys()->sort()->map(fn($d) => Carbon::parse($d)->format('M d'))->values();
        $trendPresent = $dailyGroup->map(fn($g) => $g->where('status', 'Present')->count())->values();
        
        // Department Health (Average Hours)
        $deptGroup = $logs->groupBy(fn($l) => $l->employee->department->name ?? 'Unassigned');
        $deptHealthLabels = $deptGroup->keys();
        $deptHealthData = $deptGroup->map(function ($group) use ($stats) {
             $mins = $group->map(fn($l) => $this->parseDurationToMinutes($l->duration));
             return round($stats->calculateMean($mins) / 60, 2);
        })->values();

        // --- EXTENDED MODULE ANALYTICS ---

        // 1. Leave Stats (Days)
        $leaveQuery = \App\Models\LeaveRequest::where('status', 'Approved')
            ->whereBetween('start_date', [$startDate, $endDate]);
        if ($deptId) $leaveQuery->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        // Use employee_id as key, sum total_days
        $leaveData = $leaveQuery->get()->groupBy('employee_id')->map->sum('total_days')->toArray();

        // 2. Overtime Stats (Minutes)
        $otQuery = \App\Models\OvertimeRequest::where('status', 'Approved')
            ->whereBetween('date', [$startDate, $endDate]);
        if ($deptId) $otQuery->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        $otData = $otQuery->get()->groupBy('employee_id')->map->sum('minutes')->toArray();

        // 3. WFH Stats (Days count)
        $wfhQuery = \App\Models\WfhRequest::where('status', 'Approved')
             ->whereBetween('date', [$startDate, $endDate]);
        if ($deptId) $wfhQuery->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        $wfhData = $wfhQuery->get()->groupBy('employee_id')->map->count()->toArray();
        
        return response()->json([
            'type' => 'organization',
            'overview' => [
                'total_logs' => $logs->count(),
                'avg_daily_present' => round($dailyGroup->avg(fn($g) => $g->where('status', 'Present')->count()), 1),
                'late_rate' => $logs->count() > 0 ? round(($logs->where('is_late', 1)->count() / $logs->count()) * 100, 1) : 0
            ],
            'trend' => [
                'labels' => $trendLabels,
                'present' => $trendPresent
            ],
            'department_health' => [
                'labels' => $deptHealthLabels,
                'data' => $deptHealthData // Average Hours by Dept
            ],
            'modules' => [
                'leaves' => $this->computeModuleStats($leaveData, $stats, 'Days'),
                'overtime' => $this->computeModuleStats($otData, $stats, 'Minutes'),
                'wfh' => $this->computeModuleStats($wfhData, $stats, 'Days'),
            ]
        ]);
    }

    private function computeModuleStats(array $data, StatisticalService $statService, $unit) {
        $values = collect(array_values($data));
        if ($values->isEmpty()) {
            return [
                'total' => 0, 'mean' => 0, 'median' => 0, 
                'distribution' => ['above' => 0, 'below' => 0, 'equal' => 0],
                'top_performers' => [], 'unit' => $unit
            ];
        }

        $mean = $statService->calculateMean($values);
        
        // Resolve names for rankings
        $rankings = $statService->getRankings($data, 5);
        $topUsers = User::whereIn('id', array_keys($rankings['top']))->pluck('name', 'id');
        
        $formattedTop = [];
        foreach($rankings['top'] as $uid => $val) {
            $formattedTop[] = ['name' => $topUsers[$uid] ?? "ID $uid", 'value' => $val];
        }

        return [
            'total' => $values->sum(),
            'mean' => $mean,
            'median' => $statService->calculateMedian($values),
            'distribution' => $statService->countAboveBelow($values, $mean),
            'top_performers' => $formattedTop,
            'unit' => $unit
        ];
    }

    private function parseDurationToMinutes($duration) {
        if (!$duration) return 0;
        try {
            // Assume "HH:MM:SS" or "HH:MM"
            $parts = explode(':', $duration);
            $hours = (int) ($parts[0] ?? 0);
            $mins = (int) ($parts[1] ?? 0);
            return ($hours * 60) + $mins;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
