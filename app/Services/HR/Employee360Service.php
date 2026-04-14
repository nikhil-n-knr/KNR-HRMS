<?php

namespace App\Services\HR;

use App\Models\Employee;
use App\Models\User;
use App\Models\WorkAssignment;
use App\Models\Task;
use App\Models\BugTicket;
use App\Models\BugActivity;
use App\Models\AttendanceSession;
use App\Models\AttendanceLog;
use App\Models\LeaveRequest;
use App\Models\WfhRequest;
use App\Models\ShiftSwap;
use App\Models\WorkflowApproval;
use App\Models\Timesheet;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Employee360Service
{
    /**
     * Get Aggregated Dashboard Metrics
     */
    public function getDashboardMetrics(Employee $employee, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return [
            'overview' => $this->getOperationalHealth($employee, $start, $end),
            'projects' => $this->getProjectSummary($employee, $start, $end),
            'quality'  => $this->getBugDeepDive($employee, $start, $end),
            'timesheet' => $this->getTimesheetSummary($employee, $start, $end),
            'requests' => $this->getRequestAudit($employee, $start, $end),
            'attendance_grid' => $this->getDetailedAttendanceGrid($employee, $start, $end),
            'deviation' => $this->getOperationalDeviation($employee, $start, $end),
        ];
    }

    protected function getOperationalHealth(Employee $employee, Carbon $start, Carbon $end)
    {
        // Calculate basic points & hours
        $points = 0;
        $hoursBurned = 0;
        
        $assignments = WorkAssignment::with('task')
            ->where('assignee_id', $employee->id)
            ->where('assignee_type', Employee::class)
            ->where(function($q) use ($start, $end) {
                $q->where('start_date', '<=', $end)
                  ->where('end_date', '>=', $start);
            })->get();

        foreach ($assignments as $a) {
            $hoursBurned += $a->allocated_hours;
            if ($a->task) {
                $points += $a->task->scrum_points ?? 0;
            }
        }

        // Attendance basic score - use AttendanceLog which has direct employee_id
        $attendanceDays = AttendanceLog::where('employee_id', $employee->id)
            ->whereBetween('date', [$start, $end])
            ->where('status', 'present')
            ->count();
            
        $totalDays = max(1, $start->diffInDays($end) - 8); // rough weekdays
        $reliability = min(100, round(($attendanceDays / $totalDays) * 100));

        // Basic burnout risk logic: Too many hours + weekends?
        $burnout = 'Low';
        if ($hoursBurned > ($totalDays * 8) + 20) {
            $burnout = 'High';
        } elseif ($hoursBurned > ($totalDays * 8)) {
            $burnout = 'Medium';
        }

        return [
            'scrum_velocity' => $points,
            'hours_burned' => $hoursBurned,
            'reliability_score' => $reliability,
            'burnout_risk' => $burnout,
            'status' => $employee->status,
        ];
    }

    protected function getProjectSummary(Employee $employee, Carbon $start, Carbon $end)
    {
        $assignments = WorkAssignment::with('task.project')
            ->where('assignee_id', $employee->id)
            ->where('assignee_type', Employee::class)
            ->where(function($q) use ($start, $end) {
                $q->where('start_date', '<=', $end)
                  ->where('end_date', '>=', $start);
            })->get();

        $completedTasks = 0;
        $overdueTasks = 0;
        foreach ($assignments as $a) {
            if ($a->task) {
                if ($a->task->status === 'Done') $completedTasks++;
                elseif ($a->task->due_date && $a->task->due_date < now()) $overdueTasks++;
            }
        }

        return [
            'total_assignments' => count($assignments),
            'completed' => $completedTasks,
            'overdue' => $overdueTasks
        ];
    }

    protected function getBugDeepDive(Employee $employee, Carbon $start, Carbon $end)
    {
        $assigned = BugTicket::where('assignee_id', $employee->id)
            ->where('assignee_type', Employee::class)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $resolved = BugTicket::where('assignee_id', $employee->id)
            ->where('assignee_type', Employee::class)
            ->whereNotNull('resolved_at')
            ->whereBetween('resolved_at', [$start, $end])
            ->count();

        // Calculate reopening rates from activities
        $reopened = BugActivity::where('activity_type', 'Status Changed')
            ->where('description', 'like', '%Reopened%')
            ->whereHas('ticket', function($q) use ($employee) {
                $q->where('assignee_id', $employee->id);
            })
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // Module-level bug impact
        $moduleImpact = BugTicket::where('assignee_id', $employee->id)
            ->select('module_id', DB::raw('count(*) as count'))
            ->groupBy('module_id')
            ->with('module:id,name')
            ->get()
            ->map(function($item) {
                return [
                    'module' => $item->module->name ?? 'Unknown',
                    'count' => $item->count
                ];
            });

        return [
            'assigned' => $assigned,
            'resolved' => $resolved,
            'reopened' => $reopened,
            'sla_breaches' => BugTicket::where('assignee_id', $employee->id)->where('is_sla_breached', true)->count(),
            'module_impact' => $moduleImpact
        ];
    }

    protected function getRequestAudit(Employee $employee, Carbon $start, Carbon $end)
    {
        $leaves = LeaveRequest::where('employee_id', $employee->id)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $wfh = WfhRequest::where('employee_id', $employee->id)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $swaps = ShiftSwap::where('requestor_id', $employee->id)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // Items pending the employee's approval (Action Required)
        $pendingMyAction = WorkflowApproval::where('approver_id', Auth::id() ?? $employee->user_id)
            ->where('status', 'pending')
            ->count();

        return [
            'leaves' => $leaves->count(),
            'wfh' => $wfh,
            'swaps' => $swaps,
            'pending_approvals' => $pendingMyAction,
            'recent_requests' => $leaves->take(5)
        ];
    }

    protected function getDetailedAttendanceGrid(Employee $employee, Carbon $start, Carbon $end)
    {
        // Last 7 days grid
        $gridStart = now()->subDays(7)->startOfDay();
        $logs = AttendanceLog::with('sessions')
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$gridStart, now()])
            ->orderBy('date', 'desc')
            ->get();

        return $logs->map(function($log) {
            return [
                'date' => $log->date->format('Y-m-d'),
                'status' => $log->status,
                'check_in' => $log->sessions->first()->in_time ?? '-',
                'check_out' => $log->sessions->last()->out_time ?? '-',
                'duration' => round($log->total_work_minutes / 60, 1)
            ];
        });
    }

    protected function getOperationalDeviation(Employee $employee, Carbon $start, Carbon $end)
    {
        $estimatedHours = WorkAssignment::where('assignee_id', $employee->id)
            ->where('assignee_type', Employee::class)
            ->whereBetween('start_date', [$start, $end])
            ->sum('allocated_hours');

        $actualHours = Timesheet::where('employee_id', $employee->id)
            ->whereBetween('date', [$start, $end])
            ->sum('hours_spent');

        $unassignedWork = Timesheet::where('employee_id', $employee->id)
            ->whereNull('task_id')
            ->whereBetween('date', [$start, $end])
            ->sum('hours_spent');

        return [
            'estimated' => $estimatedHours,
            'actual' => $actualHours,
            'unassigned' => $unassignedWork,
            'efficiency' => $estimatedHours > 0 ? round(($actualHours / $estimatedHours) * 100) : 100
        ];
    }

    protected function getTimesheetSummary(Employee $employee, Carbon $start, Carbon $end)
    {
        $sessions = AttendanceSession::join('attendance_logs', 'attendance_sessions.attendance_log_id', '=', 'attendance_logs.id')
            ->where('attendance_logs.employee_id', $employee->id)
            ->whereBetween('attendance_logs.date', [$start, $end])
            ->select('attendance_sessions.*')
            ->get();

        $totalMinutes = AttendanceLog::where('employee_id', $employee->id)
            ->whereBetween('date', [$start, $end])
            ->sum('total_work_minutes');

        $geoBreaches = $sessions->filter(function($s) {
            return $s->in_lat == 0 || $s->in_long == 0; // Simplified geo check for this schema
        })->count();

        return [
            'total_hours' => round($totalMinutes / 60, 1),
            'sessions' => count($sessions),
            'geo_breaches' => $geoBreaches
        ];
    }

    /**
     * Data Loaders for EXCEL Exports (Returns raw collections)
     */
    public function getProjectDeliveryData(Employee $employee, $startDate, $endDate) {
        return WorkAssignment::with('task.project')
            ->where('assignee_id', $employee->id)
            ->where('assignee_type', Employee::class)
            ->where(function($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate);
            })->get();
    }

    public function getTimesheetData(Employee $employee, $startDate, $endDate) {
        return AttendanceSession::join('attendance_logs', 'attendance_sessions.attendance_log_id', '=', 'attendance_logs.id')
            ->where('attendance_logs.employee_id', $employee->id)
            ->whereBetween('attendance_logs.date', [$startDate, $endDate])
            ->select('attendance_sessions.*', 'attendance_logs.date', 'attendance_logs.status')
            ->get();
    }

    public function getQualityData(Employee $employee, $startDate, $endDate) {
        return BugTicket::with('project')
            ->where('assignee_id', $employee->id)
            ->where('assignee_type', Employee::class)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
    }

    public function getLearningData(User $user) {
        // Safe check if classes exist
        if (!class_exists(\App\Models\LMS\LmsCourseProgress::class)) return collect();
        return \App\Models\LMS\LmsCourseProgress::with('course')
            ->where('user_id', $user->id)
            ->get();
    }
    
    public function getCrmData(User $user, $startDate, $endDate) {
        if (!class_exists(\App\Models\CRM\Deal::class)) return collect();
        return \App\Models\CRM\Deal::where('owner_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
    }
}
