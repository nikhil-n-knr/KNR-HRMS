<?php

namespace App\Services\HR;

use App\Models\Employee;
use App\Models\User;
use App\Models\WorkAssignment;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\BugTicket;
use App\Models\BugActivity;
use App\Models\AttendanceSession;
use App\Models\AttendanceLog;
use App\Models\LeaveRequest;
use App\Models\WfhRequest;
use App\Models\ShiftSwap;
use App\Models\WorkflowApproval;
use App\Models\WorkflowInstance;
use App\Models\Timesheet;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Employee360Service
{
    /**
     * Get Aggregated Dashboard Metrics
     */
    public function getDashboardMetrics(Employee $employee, $startDate, $endDate, array $options = [])
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return [
            'overview' => $this->getOperationalHealth($employee, $start, $end),
            'projects' => $this->getProjectSummary($employee, $start, $end),
            'quality'  => $this->getBugDeepDive($employee, $start, $end),
            'timesheet' => $this->getTimesheetSummary($employee, $start, $end),
            'requests' => $this->getRequestAudit($employee, $start, $end),
            'approval_stats' => $this->getApprovalStats($employee, $start, $end),
            'workload' => $this->getWorkloadSummary($employee, $start, $end),
            'task_details' => $this->getTaskDetails($employee, $start, $end, $options),
            'bug_details' => $this->getBugDetails($employee, $start, $end, $options),
            'attendance_grid' => $this->getDetailedAttendanceGrid($employee, $start, $end),
            'deviation' => $this->getOperationalDeviation($employee, $start, $end),
            'project_details' => $this->getDetailedProjectList($employee, $start, $end),
            'weekly_velocity' => $this->getWeeklyVelocityData($employee, $start, $end),
            'activity_logs' => $this->getActivityTimeline($employee, $start, $end),
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
            ->whereHas('bug', function($q) use ($employee) {
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
            'open' => max(0, $assigned - $resolved),
            'reopened' => $reopened,
            'sla_breaches' => BugTicket::where('assignee_id', $employee->id)->where('is_sla_breached', true)->count(),
            'critical_open' => BugTicket::where('assignee_id', $employee->id)
                ->where('assignee_type', Employee::class)
                ->whereNull('resolved_at')
                ->whereRaw("LOWER(COALESCE(severity, '')) IN ('critical', 'high')")
                ->count(),
            'module_impact' => $moduleImpact
        ];
    }

    protected function getRequestAudit(Employee $employee, Carbon $start, Carbon $end)
    {
        $leaves = LeaveRequest::where('employee_id', $employee->id)
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->map(function($l) {
                return [
                    'id' => $l->id,
                    'start_date' => $l->start_date->format('d M Y'),
                    'end_date' => $l->end_date->format('d M Y'),
                    'status' => $l->status
                ];
            });

        $leaveApprovedDays = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_days');

        $leaveBalance = [
            'allowed' => 0,
            'used' => 0,
            'remaining' => 0,
        ];
        if (Schema::hasTable('leave_balances')) {
            $currentYear = (int) now()->format('Y');
            $allowed = (float) DB::table('leave_balances')
                ->where('employee_id', $employee->id)
                ->where('year', $currentYear)
                ->sum('total_days');
            $used = (float) DB::table('leave_balances')
                ->where('employee_id', $employee->id)
                ->where('year', $currentYear)
                ->sum('used_days');
            $leaveBalance = [
                'allowed' => round($allowed, 1),
                'used' => round($used, 1),
                'remaining' => round(max(0, $allowed - $used), 1),
            ];
        }

        $wfhQuery = WfhRequest::where('employee_id', $employee->id)
            ->whereBetween('created_at', [$start, $end]);
        $wfh = (clone $wfhQuery)->count();
        $wfhApproved = (clone $wfhQuery)->whereRaw('LOWER(status) = ?', ['approved'])->count();
        $wfhPending = (clone $wfhQuery)->whereRaw('LOWER(status) = ?', ['pending'])->count();
        $wfhRejected = (clone $wfhQuery)->whereRaw('LOWER(status) = ?', ['rejected'])->count();

        $wfhAllowed = null;
        $wfhUsedYear = (int) WfhRequest::where('employee_id', $employee->id)
            ->whereYear('date', now()->year)
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->count();
        $policy = $employee->effective_attendance_policy;
        if ($policy && is_array($policy->wfh_policy ?? null)) {
            $wfhPolicy = $policy->wfh_policy;
            $wfhAllowed = $wfhPolicy['days_allowed_per_year']
                ?? $wfhPolicy['max_days_per_year']
                ?? $wfhPolicy['annual_limit']
                ?? null;
            if ($wfhAllowed !== null) {
                $wfhAllowed = (int) $wfhAllowed;
            }
        }

        $swaps = ShiftSwap::where('requester_id', $employee->id)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // Items pending the employee's approval (Action Required)
        $pendingMyAction = WorkflowApproval::where('approver_id', Auth::id() ?? $employee->user_id)
            ->where('status', 'pending')
            ->count();

        return [
            'leaves' => $leaves->count(),
            'leave_days_taken' => round((float) $leaveApprovedDays, 1),
            'leave_balance' => $leaveBalance,
            'wfh' => $wfh,
            'wfh_summary' => [
                'total' => $wfh,
                'approved' => $wfhApproved,
                'pending' => $wfhPending,
                'rejected' => $wfhRejected,
                'allowed' => $wfhAllowed,
                'used' => $wfhUsedYear,
                'remaining' => $wfhAllowed !== null ? max(0, $wfhAllowed - $wfhUsedYear) : null,
            ],
            'swaps' => $swaps,
            'pending_approvals' => $pendingMyAction,
            'recent_requests' => $leaves->take(5)
        ];
    }

    protected function getApprovalStats(Employee $employee, Carbon $start, Carbon $end)
    {
        $userId = $employee->user_id;
        if (!$userId) {
            return [
                'raised_total' => 0,
                'raised_approved' => 0,
                'raised_pending' => 0,
                'raised_rejected' => 0,
                'action_total' => 0,
                'action_approved' => 0,
                'action_rejected' => 0,
                'action_pending' => 0,
            ];
        }

        $raisedTotal = 0;
        $raisedApproved = 0;
        $raisedPending = 0;
        $raisedRejected = 0;
        if (Schema::hasTable('workflow_instances')) {
            $raisedQuery = WorkflowInstance::where('initiator_id', $userId)
                ->whereBetween('created_at', [$start, $end]);
            $raisedTotal = (clone $raisedQuery)->count();
            $raisedApproved = (clone $raisedQuery)->where('status', 'approved')->count();
            $raisedPending = (clone $raisedQuery)->where('status', 'pending')->count();
            $raisedRejected = (clone $raisedQuery)->where('status', 'rejected')->count();
        }

        $actionTotal = 0;
        $actionApproved = 0;
        $actionRejected = 0;
        $actionPending = 0;
        if (Schema::hasTable('workflow_approvals')) {
            $actedQuery = WorkflowApproval::where('approver_id', $userId)
                ->whereBetween('created_at', [$start, $end]);
            $actionTotal = (clone $actedQuery)->count();
            $actionApproved = (clone $actedQuery)->where('status', 'approved')->count();
            $actionRejected = (clone $actedQuery)->where('status', 'rejected')->count();
            $actionPending = (clone $actedQuery)->where('status', 'pending')->count();
        }

        return [
            'raised_total' => $raisedTotal,
            'raised_approved' => $raisedApproved,
            'raised_pending' => $raisedPending,
            'raised_rejected' => $raisedRejected,
            'action_total' => $actionTotal,
            'action_approved' => $actionApproved,
            'action_rejected' => $actionRejected,
            'action_pending' => $actionPending,
        ];
    }

    protected function getWorkloadSummary(Employee $employee, Carbon $start, Carbon $end)
    {
        $projectIds = WorkAssignment::where('assignee_id', $employee->id)
            ->where('assignee_type', Employee::class)
            ->where(function ($q) use ($start, $end) {
                $q->where('start_date', '<=', $end)
                    ->where('end_date', '>=', $start);
            })
            ->pluck('project_id')
            ->filter()
            ->unique();

        $taskIds = WorkAssignment::where('assignee_id', $employee->id)
            ->where('assignee_type', Employee::class)
            ->whereNotNull('task_id')
            ->pluck('task_id')
            ->filter()
            ->unique();

        $totalProjects = $projectIds->count();
        $activeProjects = Project::whereIn('id', $projectIds)
            ->whereRaw("LOWER(COALESCE(status, '')) NOT IN ('inactive', 'on hold', 'on_hold', 'paused', 'completed', 'closed', 'cancelled', 'archived')")
            ->count();

        $totalTasks = Task::whereIn('id', $taskIds)->count();
        $completedTasks = Task::whereIn('id', $taskIds)
            ->whereRaw("LOWER(COALESCE(status, '')) IN ('done', 'completed', 'closed')")
            ->count();

        $filledDays = Timesheet::where('employee_id', $employee->id)
            ->whereBetween('date', [$start, $end])
            ->select('date')
            ->distinct()
            ->count('date');

        $daysInRange = max(1, $start->copy()->startOfDay()->diffInDays($end->copy()->endOfDay()) + 1);

        return [
            'projects_total' => $totalProjects,
            'projects_active' => $activeProjects,
            'projects_inactive' => max(0, $totalProjects - $activeProjects),
            'tasks_total' => $totalTasks,
            'tasks_completed' => $completedTasks,
            'tasks_open' => max(0, $totalTasks - $completedTasks),
            'bugs_total' => (int) BugTicket::where('assignee_id', $employee->id)
                ->where('assignee_type', Employee::class)
                ->whereBetween('created_at', [$start, $end])
                ->count(),
            'bugs_closed' => (int) BugTicket::where('assignee_id', $employee->id)
                ->where('assignee_type', Employee::class)
                ->whereNotNull('resolved_at')
                ->whereBetween('created_at', [$start, $end])
                ->count(),
            'bugs_open' => (int) BugTicket::where('assignee_id', $employee->id)
                ->where('assignee_type', Employee::class)
                ->whereNull('resolved_at')
                ->whereBetween('created_at', [$start, $end])
                ->count(),
            'timesheet_days_filled' => $filledDays,
            'timesheet_days_missing' => max(0, $daysInRange - $filledDays),
        ];
    }

    protected function getTaskDetails(Employee $employee, Carbon $start, Carbon $end, array $options = [])
    {
        $taskIds = WorkAssignment::where('assignee_id', $employee->id)
            ->where('assignee_type', Employee::class)
            ->whereNotNull('task_id')
            ->where(function ($q) use ($start, $end) {
                $q->where('start_date', '<=', $end)
                    ->where('end_date', '>=', $start);
            })
            ->pluck('task_id')
            ->filter()
            ->unique()
            ->values();

        if ($taskIds->isEmpty()) {
            return [
                'items' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => (int) ($options['task_per_page'] ?? 4),
                    'total' => 0,
                ],
            ];
        }

        $taskStatus = strtolower(trim((string) ($options['task_status'] ?? 'all')));
        $taskPriority = trim((string) ($options['task_priority'] ?? ''));
        $taskPage = max(1, (int) ($options['task_page'] ?? 1));
        $taskPerPage = max(1, min(20, (int) ($options['task_per_page'] ?? 4)));

        $tasksQuery = Task::with(['project:id,name', 'stage:id,name,type'])
            ->whereIn('id', $taskIds);

        if ($taskPriority !== '' && strtolower($taskPriority) !== 'all') {
            $tasksQuery->whereRaw('LOWER(COALESCE(priority, "")) = ?', [strtolower($taskPriority)]);
        }

        if ($taskStatus === 'closed') {
            $tasksQuery->where(function ($q) {
                $q->whereRaw("LOWER(COALESCE(status, '')) IN ('done', 'completed', 'closed', 'cancelled', 'rejected')")
                    ->orWhereHas('stage', function ($stageQ) {
                        $stageQ->whereRaw("LOWER(COALESCE(type, '')) IN ('done', 'completed', 'closed')");
                    });
            });
        } elseif ($taskStatus === 'open') {
            $tasksQuery->where(function ($q) {
                $q->whereRaw("LOWER(COALESCE(status, '')) NOT IN ('done', 'completed', 'closed', 'cancelled', 'rejected')")
                    ->orWhereNull('status');
            });
        } elseif ($taskStatus === 'overdue') {
            $tasksQuery->whereDate('due_date', '<', now()->toDateString())
                ->where(function ($q) {
                    $q->whereRaw("LOWER(COALESCE(status, '')) NOT IN ('done', 'completed', 'closed', 'cancelled', 'rejected')")
                        ->orWhereNull('status');
                });
        }

        $tasksPaginator = $tasksQuery
            ->orderByRaw("CASE WHEN LOWER(COALESCE(status, '')) IN ('done', 'completed', 'closed') THEN 1 ELSE 0 END")
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->paginate($taskPerPage, ['*'], 'task_page', $taskPage);

        $items = $tasksPaginator->getCollection()->map(function ($task) use ($employee, $start, $end) {
            $actualHours = (float) Timesheet::where('employee_id', $employee->id)
                ->where('task_id', $task->id)
                ->whereBetween('date', [$start, $end])
                ->sum('hours_spent');

            $allocatedHours = (float) WorkAssignment::where('assignee_id', $employee->id)
                ->where('assignee_type', Employee::class)
                ->where('task_id', $task->id)
                ->sum('allocated_hours');

            $latestActivity = TaskActivity::where('task_id', $task->id)
                ->latest('created_at')
                ->value('type');

            $progress = $this->deriveTaskProgress($task, $allocatedHours, $actualHours);
            $isClosed = $this->isTaskClosed($task->status, optional($task->stage)->type);

            return [
                'id' => $task->id,
                'title' => $task->title,
                'project' => $task->project->name ?? 'Unknown Project',
                'status' => $task->status ?? 'Unknown',
                'stage' => $task->stage->name ?? 'No Stage',
                'stage_type' => $task->stage->type ?? null,
                'priority' => $task->priority ?? 'Normal',
                'due_date' => $task->due_date?->format('d M Y'),
                'estimated_hours' => round((float) ($task->estimated_hours ?? $allocatedHours), 1),
                'allocated_hours' => round($allocatedHours, 1),
                'actual_hours' => round($actualHours, 1),
                'scrum_points' => (int) ($task->scrum_points ?? 0),
                'progress' => $progress,
                'is_closed' => $isClosed,
                'is_overdue' => !$isClosed && $task->due_date && Carbon::parse($task->due_date)->isPast(),
                'latest_activity' => $latestActivity ?: 'No recent task activity',
                'project_id' => $task->project_id,
                'task_url' => ($task->project_id && Route::has('projects.tasks.show'))
                    ? route('projects.tasks.show', ['project' => $task->project_id, 'task' => $task->id])
                    : null,
            ];
        })->values()->all();

        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $tasksPaginator->currentPage(),
                'last_page' => $tasksPaginator->lastPage(),
                'per_page' => $tasksPaginator->perPage(),
                'total' => $tasksPaginator->total(),
            ],
        ];
    }

    protected function getBugDetails(Employee $employee, Carbon $start, Carbon $end, array $options = [])
    {
        $bugStatus = strtolower(trim((string) ($options['bug_status'] ?? 'all')));
        $bugPriority = trim((string) ($options['bug_priority'] ?? ''));
        $bugPage = max(1, (int) ($options['bug_page'] ?? 1));
        $bugPerPage = max(1, min(20, (int) ($options['bug_per_page'] ?? 4)));

        $bugsQuery = BugTicket::with(['project:id,name', 'module:id,name', 'task:id,title'])
            ->where('assignee_id', $employee->id)
            ->where('assignee_type', Employee::class)
            ->whereBetween('created_at', [$start, $end]);

        if ($bugPriority !== '' && strtolower($bugPriority) !== 'all') {
            $bugsQuery->whereRaw('LOWER(COALESCE(priority, "")) = ?', [strtolower($bugPriority)]);
        }

        if ($bugStatus === 'open') {
            $bugsQuery->whereNull('resolved_at');
        } elseif ($bugStatus === 'closed') {
            $bugsQuery->whereNotNull('resolved_at');
        }

        $bugsPaginator = $bugsQuery
            ->orderByRaw('CASE WHEN resolved_at IS NULL THEN 0 ELSE 1 END')
            ->orderByDesc('created_at')
            ->paginate($bugPerPage, ['*'], 'bug_page', $bugPage);

        $items = $bugsPaginator->getCollection()->map(function ($bug) {
            $isClosed = !is_null($bug->resolved_at);
            return [
                'id' => $bug->id,
                'subject' => $bug->subject ?: 'Untitled bug',
                'project' => $bug->project->name ?? 'Unknown Project',
                'project_id' => $bug->project_id,
                'module' => $bug->module->name ?? 'Unknown Module',
                'linked_task' => $bug->task->title ?? null,
                'severity' => $bug->severity ?? 'Unknown',
                'priority' => $bug->priority ?? 'Unknown',
                'status' => $isClosed ? 'Closed' : 'Open',
                'is_closed' => $isClosed,
                'is_sla_breached' => (bool) $bug->is_sla_breached,
                'created_at' => $bug->created_at?->format('d M Y h:i A'),
                'resolved_at' => $bug->resolved_at?->format('d M Y h:i A'),
                'hours_spent' => round((float) ($bug->hours_spent ?? 0), 1),
                'bug_url' => Route::has('bugs.show') ? route('bugs.show', ['bug' => $bug->id]) : null,
            ];
        })->values()->all();

        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $bugsPaginator->currentPage(),
                'last_page' => $bugsPaginator->lastPage(),
                'per_page' => $bugsPaginator->perPage(),
                'total' => $bugsPaginator->total(),
            ],
        ];
    }

    protected function deriveTaskProgress(Task $task, float $allocatedHours, float $actualHours): int
    {
        $status = strtolower(trim((string) ($task->status ?? '')));
        $stageType = strtolower(trim((string) (optional($task->stage)->type ?? '')));

        if ($this->isTaskClosed($task->status, optional($task->stage)->type)) {
            return 100;
        }

        $estimated = (float) ($task->estimated_hours ?? 0);
        if ($estimated <= 0) {
            $estimated = $allocatedHours;
        }

        if ($estimated > 0 && $actualHours > 0) {
            return max(5, min(95, (int) round(($actualHours / $estimated) * 100)));
        }

        if (in_array($stageType, ['review', 'qa', 'testing', 'uat'], true)) {
            return 80;
        }

        if (in_array($status, ['in progress', 'in_progress', 'doing', 'development', 'active'], true) || in_array($stageType, ['in_progress', 'development', 'doing', 'progress'], true)) {
            return 55;
        }

        if (in_array($status, ['todo', 'to do', 'open', 'pending', 'backlog'], true) || in_array($stageType, ['todo', 'backlog', 'open'], true)) {
            return 15;
        }

        return 25;
    }

    protected function isTaskClosed(?string $status, ?string $stageType = null): bool
    {
        $normalizedStatus = strtolower(trim((string) $status));
        $normalizedStageType = strtolower(trim((string) $stageType));

        return in_array($normalizedStatus, ['done', 'completed', 'closed', 'cancelled', 'rejected'], true)
            || in_array($normalizedStageType, ['done', 'completed', 'closed'], true);
    }

    protected function getDetailedAttendanceGrid(Employee $employee, Carbon $start, Carbon $end)
    {
        // Show selected range (latest 45 records), with normalized datetime output
        $logs = AttendanceLog::with('sessions')
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date', 'desc')
            ->limit(45)
            ->get();

        return $logs->map(function($log) {
            $sessions = $log->sessions->sortBy('in_time')->values();
            $firstSession = $sessions->first();
            $lastSession = $sessions->last();
            $checkIn = $firstSession && $firstSession->in_time
                ? Carbon::parse($firstSession->in_time)->format('d M Y h:i A')
                : null;
            $checkOut = $lastSession && $lastSession->out_time
                ? Carbon::parse($lastSession->out_time)->format('d M Y h:i A')
                : null;

            $note = '-';
            if ($checkIn && !$checkOut) {
                $note = 'Open Session';
            } elseif ((int) ($log->total_work_minutes ?? 0) > 0 && !$checkIn) {
                $note = 'Auto captured work minutes';
            } elseif ((int) ($log->total_work_minutes ?? 0) === 0 && $log->status === 'present') {
                $note = 'Present but work minutes not closed';
            }

            return [
                'date' => $log->date->format('d M Y'),
                'status' => $log->status,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'duration' => round(((float) ($log->total_work_minutes ?? 0)) / 60, 1),
                'note' => $note,
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

    protected function getDetailedProjectList(Employee $employee, Carbon $start, Carbon $end)
    {
        $assignments = WorkAssignment::with('project')
            ->where('assignee_id', $employee->id)
            ->where('assignee_type', Employee::class)
            ->whereBetween('start_date', [$start, $end])
            ->get();

        return $assignments->map(function($ass) use ($employee, $start, $end) {
            $actualHours = Timesheet::where('employee_id', $employee->id)
                ->where('project_id', $ass->project_id)
                ->whereBetween('date', [$start, $end])
                ->sum('hours_spent');

            $estimatedDays = $ass->start_date->diffInDays($ass->end_date);

            return [
                'name' => $ass->project->name ?? 'Unknown Project',
                'id' => $ass->project_id,
                'estimated_hours' => $ass->allocated_hours,
                'actual_hours' => $actualHours,
                'estimated_days' => $estimatedDays,
                'status' => $ass->project->status ?? 'Unknown',
                'progress' => $ass->project->manual_progress_percentage ?? 0
            ];
        });
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
            'geo_breaches' => $geoBreaches,
            'entries_total' => (int) Timesheet::where('employee_id', $employee->id)->whereBetween('date', [$start, $end])->count(),
            'days_filled' => (int) Timesheet::where('employee_id', $employee->id)->whereBetween('date', [$start, $end])->distinct('date')->count('date'),
        ];
    }

    protected function getActivityTimeline(Employee $employee, Carbon $start, Carbon $end)
    {
        $timeline = collect();

        $attendanceEvents = AttendanceLog::where('employee_id', $employee->id)
            ->whereBetween('date', [$start, $end])
            ->orderByDesc('date')
            ->limit(20)
            ->get(['date', 'status', 'total_work_minutes'])
            ->map(function ($log) {
                return [
                    'event_type' => 'attendance',
                    'title' => 'Attendance ' . strtoupper((string) $log->status),
                    'meta' => round(((float) ($log->total_work_minutes ?? 0)) / 60, 1) . 'h worked',
                    'occurred_at' => Carbon::parse($log->date)->endOfDay()->toDateTimeString(),
                ];
            });

        $timesheetEvents = Timesheet::where('employee_id', $employee->id)
            ->whereBetween('date', [$start, $end])
            ->orderByDesc('date')
            ->limit(20)
            ->get(['date', 'hours_spent', 'status', 'task_title'])
            ->map(function ($t) {
                return [
                    'event_type' => 'timesheet',
                    'title' => 'Timesheet ' . strtoupper((string) $t->status),
                    'meta' => trim(((string) ($t->task_title ?? 'General work')) . ' · ' . ((float) $t->hours_spent) . 'h'),
                    'occurred_at' => Carbon::parse($t->date)->setTime(12, 0)->toDateTimeString(),
                ];
            });

        $leaveEvents = LeaveRequest::where('employee_id', $employee->id)
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['start_date', 'end_date', 'status', 'created_at'])
            ->map(function ($leave) {
                return [
                    'event_type' => 'leave',
                    'title' => 'Leave ' . strtoupper((string) $leave->status),
                    'meta' => $leave->start_date->format('d M') . ' → ' . $leave->end_date->format('d M'),
                    'occurred_at' => $leave->created_at?->toDateTimeString() ?? now()->toDateTimeString(),
                ];
            });

        $wfhEvents = WfhRequest::where('employee_id', $employee->id)
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['date', 'status', 'created_at'])
            ->map(function ($wfh) {
                return [
                    'event_type' => 'wfh',
                    'title' => 'WFH ' . strtoupper((string) $wfh->status),
                    'meta' => $wfh->date?->format('d M Y') ?? '-',
                    'occurred_at' => $wfh->created_at?->toDateTimeString() ?? now()->toDateTimeString(),
                ];
            });

        $timeline = $timeline
            ->concat($attendanceEvents)
            ->concat($timesheetEvents)
            ->concat($leaveEvents)
            ->concat($wfhEvents);

        if ($employee->user_id && Schema::hasTable('workflow_instances')) {
            $workflowEvents = WorkflowInstance::where('initiator_id', $employee->user_id)
                ->whereBetween('created_at', [$start, $end])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(['entity_type', 'status', 'created_at'])
                ->map(function ($wf) {
                    return [
                        'event_type' => 'workflow',
                        'title' => 'Request ' . strtoupper((string) $wf->status),
                        'meta' => str_replace('_', ' ', (string) $wf->entity_type),
                        'occurred_at' => $wf->created_at?->toDateTimeString() ?? now()->toDateTimeString(),
                    ];
                });
            $timeline = $timeline->concat($workflowEvents);
        }

        return $timeline
            ->sortByDesc('occurred_at')
            ->take(30)
            ->values()
            ->map(function ($item) {
                return [
                    'event_type' => $item['event_type'],
                    'title' => $item['title'],
                    'meta' => $item['meta'],
                    'occurred_at' => Carbon::parse($item['occurred_at'])->format('d M Y h:i A'),
                ];
            });
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

    protected function getWeeklyVelocityData(Employee $employee, Carbon $start, Carbon $end)
    {
        $labels = [];
        $plannedData = [];
        $actualData = [];

        // Create weekly buckets for the selected range
        $current = $start->copy();
        while ($current <= $end) {
            $weekStart = $current->copy()->startOfWeek();
            $weekEnd = $current->copy()->endOfWeek();
            
            $labels[] = "W/O " . $weekStart->format('d M');

            // 1. Calculate Planned Hours (Interpolated from WorkAssignments)
            $planned = 0;
            $assignments = WorkAssignment::where('assignee_id', $employee->id)
                ->where('assignee_type', Employee::class)
                ->where(function($q) use ($weekStart, $weekEnd) {
                    $q->where('start_date', '<=', $weekEnd)
                      ->where('end_date', '>=', $weekStart);
                })->get();

            foreach ($assignments as $a) {
                $totalDays = max(1, $a->start_date->diffInDays($a->end_date));
                $dailyRate = $a->allocated_hours / $totalDays;
                
                // Calculate overlap days with this specific week
                $overlapStart = $a->start_date > $weekStart ? $a->start_date : $weekStart;
                $overlapEnd = $a->end_date < $weekEnd ? $a->end_date : $weekEnd;
                $overlapDays = max(0, $overlapStart->diffInDays($overlapEnd) + 1);

                $planned += ($dailyRate * $overlapDays);
            }
            $plannedData[] = round($planned, 1);

            // 2. Calculate Actual Hours from Timesheets
            $actual = Timesheet::where('employee_id', $employee->id)
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->sum('hours_spent');
            $actualData[] = round($actual, 1);

            $current->addWeek();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Planned (Est.)',
                    'backgroundColor' => '#6366f1',
                    'data' => $plannedData
                ],
                [
                    'label' => 'Actual (Timesheet)',
                    'backgroundColor' => '#10b981',
                    'data' => $actualData
                ]
            ]
        ];
    }
}
