<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ApprovalService;
use App\Models\User;
use App\Models\Project;
use App\Models\Client;
use App\Models\Employee;
use App\Models\AttendanceLog;
use App\Models\Tenant;
use App\Models\AppModule;
use App\Models\BiometricDevice;
use App\Models\WorkflowInstance;
use App\Models\Department;
use App\Models\Approval;
use App\Models\BugTicket;
use App\Models\LeaveRequest;
use App\Models\JobPosting;
use App\Models\JobApplication;
use App\Models\Task;
use App\Models\Team;
use App\Models\Timesheet;
use App\Models\WfhRequest;
use App\Models\WorkAssignment;
use App\Models\EmployeePersonalDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        $userDashboard = $user->preferences['dashboard_path'] ?? null;
        if ($userDashboard) {
             return $this->handleDashboardPath($userDashboard);
        }

        $roles = $user->roles;
        if ($roles->isEmpty()) {
            return $this->employee($request);
        }

        // 1. Check for specific dashboard assignments in ANY role
        foreach ($roles as $role) {
            if (!empty($role->dashboard)) {
                return $this->handleDashboardPath($role->dashboard);
            }
        }

        // 2. Identify highest priority role by slug/name
        $roleSlugs = $roles->pluck('slug')->toArray();
        $roleNames = $roles->pluck('name')->map(fn($n) => strtolower($n))->toArray();

        // Admin Priority
        if (in_array('super_admin', $roleSlugs) || in_array('admin', $roleSlugs) || 
            collect($roleNames)->contains(fn($n) => str_contains($n, 'admin'))) {
            return $this->admin($request);
        }

        // HR Priority
        if (in_array('hr_manager', $roleSlugs) || in_array('hr_admin', $roleSlugs) || in_array('hr', $roleSlugs) ||
            collect($roleNames)->contains(fn($n) => str_contains($n, 'hr'))) {
            return $this->hr($request);
        }

        // Manager Priority
        if (collect($roleSlugs)->contains(fn($s) => str_contains($s, 'manager') || str_contains($s, 'lead')) ||
            collect($roleNames)->contains(fn($n) => str_contains($n, 'manager') || str_contains($n, 'lead'))) {
            return $this->manager($request);
        }

        return $this->employee($request);
    }

    private function handleDashboardPath($path)
    {
        if ($path === '/admin/dashboard') return $this->admin(request());
        if ($path === '/hr/dashboard') return $this->hr(request());
        if ($path === '/manager/dashboard') return $this->manager(request());
        if ($path === '/dashboard' || $path === '/') return $this->employee(request());

        if (str_starts_with($path, 'http') || !str_starts_with($path, '/')) {
             return redirect()->to($path);
        }
        
        return redirect()->to($path);
    }

    public function admin(Request $request)
    {
        $totalUsers = User::count();
        $totalProjects = Project::count();
        $totalClients = Client::count();
        $activeTenants = Tenant::count();
        
        $usageStats = Tenant::withCount('users')->get();
        // Fetch project counts by status - ensure these statuses exist in your frontend status mapping
        $projectStatusCounts = Project::selectRaw('status, count(*) as count')->groupBy('status')->get();

        // Node Telemetry from Biometric Devices
        $activeNodes = BiometricDevice::where('status', 'online')->count();
        $totalNodes = BiometricDevice::count();
        
        // Load trend simulation based on real data (e.g., login attempts or biometric logs)
        $loadTrend = AttendanceLog::where('date', '>=', now()->subDays(12))
            ->selectRaw('date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')->toArray();
        
        if (empty($loadTrend)) {
            $loadTrend = [20, 25, 45, 30, 60, 55, 78, 90, 85, 60, 40, 25]; // Simulation if no attendance data
        }

        return Inertia::render('Dashboard/Admin', [
            'totalUsers' => $totalUsers,
            'totalProjects' => $totalProjects,
            'totalClients' => $totalClients,
            'activeTenants' => $activeTenants,
            'tenantUsage' => $usageStats,
            'projectStats' => $projectStatusCounts,
            'system_health' => [
                'cpu' => rand(20, 45), // System stats are rarely stored in DB for HRMS
                'memory' => rand(40, 60),
                'storage' => rand(30, 50),
                'uptime' => '99.99%',
                'node_active' => $activeNodes,
                'node_total' => $totalNodes,
            ],
            'nodeTelemetry' => [
                'uptime' => '142 Days',
                'active' => $activeNodes,
                'total' => $totalNodes,
                'avg_latency' => '12ms',
                'load_trend' => $loadTrend
            ]
        ]);
    }

    public function hr(Request $request)
    {
        $totalEmployees = Employee::where('status', 'active')->count();
        $now = now();
        
        // Headcount by department
        $headcountByDept = Department::withCount(['employees' => function($q) {
            $q->where('status', 'active');
        }])->get()->map(function($dept) {
            return [
                'name' => $dept->name,
                'count' => $dept->employees_count
            ];
        });

        // Hiring Trend (Last 12 Months)
        $hiringTrend = Employee::where('joining_date', '>=', now()->subYear())
            ->selectRaw('MONTH(joining_date) as month, count(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count')->toArray();

        // If data is scarce, pad with zeros or simulation
        if (count($hiringTrend) < 1) {
            $hiringTrend = [12, 15, 8, 24, 32, 28, 45, 52, 48, 60, 55, 72];
        }

        // Diversity Metrics
        $maleCount = EmployeePersonalDetail::where('gender', 'Male')->count();
        $femaleCount = EmployeePersonalDetail::where('gender', 'Female')->count();
        $totalGenders = $maleCount + $femaleCount;
        $engagementScore = $totalGenders > 0 ? round(($femaleCount / $totalGenders) * 100) : 85;

        return Inertia::render('Dashboard/HR', [
            'totalEmployees' => $totalEmployees,
            'headcountByDept' => $headcountByDept,
            'absentToday' => AttendanceLog::where('date', today())->where('status', 'Absent')->count(),
            'pendingHRLeaves' => LeaveRequest::where('status', 'pending')->count(),
            'analytics' => [
                'engagement_score' => $engagementScore, // Mocking engagement as diversity for now
                'retention_rate' => 94.2,
                'culture_growth' => '+5.4',
                'attrition_risk' => Employee::whereNotNull('exit_date')->count() > 0 ? round((Employee::whereNotNull('exit_date')->count() / $totalEmployees) * 100, 1) : 3.1,
                'wellness_score' => 92,
                'hiring_trend' => $hiringTrend,
                'hiring_pipeline' => [
                    ['stage' => 'Postings', 'count' => JobPosting::count()],
                    ['stage' => 'Applications', 'count' => JobApplication::count()],
                    ['stage' => 'Under Review', 'count' => JobApplication::where('status', 'reviewing')->count()],
                ]
            ],
            'upcomingBirthdays' => EmployeePersonalDetail::whereMonth('dob', $now->month)
                ->whereDay('dob', '>=', $now->day)
                ->with('employee')
                ->take(5)
                ->get()->map(function($detail) {
                    return [
                        'first_name' => $detail->employee->first_name,
                        'department' => [
                            'name' => $detail->employee->department->name ?? 'N/A'
                        ]
                    ];
                })
        ]);
    }

    public function manager(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();
        $last7Start = $today->copy()->subDays(6);

        $managedTeamIds = $user->managedTeams()->pluck('id');
        if ($managedTeamIds->isEmpty() && !empty($user->team_id)) {
            $managedTeamIds = collect([(int) $user->team_id]);
        }

        $teamMemberUserIds = User::query()
            ->whereIn('team_id', $managedTeamIds)
            ->pluck('id');

        $directReportIds = Employee::query()
            ->where('reporting_to', $user->id)
            ->pluck('id');

        $teamEmployeeIds = Employee::query()
            ->whereIn('user_id', $teamMemberUserIds)
            ->pluck('id');

        $managerEmployeeId = optional($user->getEmployeeProfile())->id;

        $allStaffIds = $directReportIds
            ->merge($teamEmployeeIds)
            ->when($managerEmployeeId, fn ($ids) => $ids->push($managerEmployeeId))
            ->filter()
            ->unique()
            ->values();

        $allUserIds = $teamMemberUserIds
            ->merge(Employee::query()->whereIn('id', $allStaffIds)->whereNotNull('user_id')->pluck('user_id'))
            ->push($user->id)
            ->filter()
            ->unique()
            ->values();

        $teamCount = $allStaffIds->count();

        $teamTasksQuery = Task::query();
        if ($allStaffIds->isNotEmpty()) {
            $teamTasksQuery->whereHas('assignees', fn ($q) => $q->whereIn('employee_id', $allStaffIds));
        } else {
            $teamTasksQuery->whereRaw('1 = 0');
        }

        // Project scope for manager dashboard: all projects touched either by task assignments
        // or by explicit project-level allocations for manager's teams/members.
        $taskProjectIds = (clone $teamTasksQuery)
            ->whereNotNull('project_id')
            ->distinct('project_id')
            ->pluck('project_id');

        $assignmentProjectIds = WorkAssignment::query()
            ->whereNotNull('project_id')
            ->where(function ($q) use ($allStaffIds, $allUserIds) {
                if ($allStaffIds->isNotEmpty()) {
                    $q->orWhere(function ($empQ) use ($allStaffIds) {
                        $empQ->where('assignee_type', Employee::class)
                            ->whereIn('assignee_id', $allStaffIds);
                    });
                }

                if ($allUserIds->isNotEmpty()) {
                    $q->orWhere(function ($userQ) use ($allUserIds) {
                        $userQ->where('assignee_type', User::class)
                            ->whereIn('assignee_id', $allUserIds);
                    });
                }
            })
            ->distinct('project_id')
            ->pluck('project_id');

        $managedProjectIds = $taskProjectIds
            ->merge($assignmentProjectIds)
            ->filter()
            ->unique()
            ->values();

        $projectStatusesInactive = ['inactive', 'on hold', 'on_hold', 'paused', 'completed', 'closed', 'cancelled', 'archived'];
        $taskStatusesInactive = ['completed', 'done', 'closed', 'cancelled', 'rejected', 'inactive', 'archived'];

        $projectsBase = Project::query()->whereIn('id', $managedProjectIds);
        $totalProjects = (int) (clone $projectsBase)->count();
        $inactiveProjects = (int) (clone $projectsBase)
            ->whereIn(DB::raw('LOWER(COALESCE(status, ""))'), $projectStatusesInactive)
            ->count();
        $activeProjects = max(0, $totalProjects - $inactiveProjects);

        $projectTasksBase = Task::query()->whereIn('project_id', $managedProjectIds);
        $tasksTotalAllProjects = (int) (clone $projectTasksBase)->count();
        $tasksInactiveAllProjects = (int) (clone $projectTasksBase)
            ->where(function ($q) use ($taskStatusesInactive) {
                $q->whereIn(DB::raw('LOWER(COALESCE(status, ""))'), $taskStatusesInactive)
                    ->orWhereHas('stage', function ($stageQ) {
                        $stageQ->whereIn('type', ['done', 'completed', 'closed'])
                            ->orWhereRaw('LOWER(COALESCE(name, "")) LIKE ?', ['%done%'])
                            ->orWhereRaw('LOWER(COALESCE(name, "")) LIKE ?', ['%complete%'])
                            ->orWhereRaw('LOWER(COALESCE(name, "")) LIKE ?', ['%closed%']);
                    });
            })
            ->count();
        $tasksActiveAllProjects = max(0, $tasksTotalAllProjects - $tasksInactiveAllProjects);

        $totalTeamTasks = (clone $teamTasksQuery)->count();
        $completedTeamTasks = (clone $teamTasksQuery)
            ->where(function ($q) {
                $q->where('status', 'completed')
                    ->orWhereHas('stage', function ($stageQ) {
                        $stageQ->whereIn('type', ['done', 'completed', 'closed'])
                            ->orWhere('name', 'like', '%done%')
                            ->orWhere('name', 'like', '%complete%')
                            ->orWhere('name', 'like', '%closed%');
                    });
            })
            ->count();

        $offPlanTasks = (clone $teamTasksQuery)
            ->where(function ($q) use ($today) {
                $q->where(function ($dq) use ($today) {
                    $dq->whereNotNull('due_date')
                        ->whereDate('due_date', '<', $today->toDateString());
                })->orWhereNotNull('blocked_by_task_id');
            })
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhere('status', '!=', 'completed');
            })
            ->count();

        $activeIncidents = (clone $teamTasksQuery)
            ->whereIn('priority', ['critical', 'Critical', 'urgent', 'Urgent', 'high', 'High'])
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'completed');
            })
            ->count();

        $teamBugsQuery = BugTicket::query()
            ->where('assignee_type', Employee::class)
            ->whereIn('assignee_id', $allStaffIds);

        $bugsTotalCount = (int) (clone $teamBugsQuery)->count();
        $bugsOpenTotal = (int) (clone $teamBugsQuery)
            ->whereNull('resolved_at')
            ->count();
        $bugsClosedToday = (int) (clone $teamBugsQuery)
            ->whereDate('resolved_at', $today->toDateString())
            ->count();
        $bugsPendingTotal = (int) (clone $teamBugsQuery)
            ->whereNull('resolved_at')
            ->whereNull('started_at')
            ->count();

        $completionRate = $totalTeamTasks > 0 ? round(($completedTeamTasks / $totalTeamTasks) * 100) : 100;

        $weeklyStart = $today->copy()->subWeeks(7)->startOfWeek(Carbon::MONDAY);
        $weeklyRows = (clone $teamTasksQuery)
            ->whereBetween('updated_at', [$weeklyStart->toDateString(), $today->toDateString()])
            ->selectRaw('YEARWEEK(updated_at, 1) as week_key, COUNT(*) as total_tasks, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as done_tasks')
            ->groupBy('week_key')
            ->get()
            ->keyBy('week_key');

        $weeklyEngagement = collect(range(0, 7))
            ->map(function (int $offset) use ($weeklyStart, $weeklyRows) {
                $weekStart = $weeklyStart->copy()->addWeeks($offset);
                $weekKey = (int) ($weekStart->isoWeekYear . str_pad((string) $weekStart->isoWeek, 2, '0', STR_PAD_LEFT));
                $row = $weeklyRows->get($weekKey);
                $total = (int) ($row->total_tasks ?? 0);
                $done = (int) ($row->done_tasks ?? 0);
                return $total > 0 ? (int) round(($done / $total) * 100) : 0;
            })
            ->values();

        $velocity = (int) round($weeklyEngagement->avg() ?? 0);

        $teamAttendance = AttendanceLog::query()
            ->whereDate('date', $today->toDateString())
            ->whereIn('employee_id', $allStaffIds)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();

        $presentToday = (int) AttendanceLog::query()
            ->whereDate('date', $today->toDateString())
            ->whereIn('employee_id', $allStaffIds)
            ->whereIn('status', ['Present', 'present'])
            ->count();

        $timesheetFilledToday = (int) Timesheet::query()
            ->whereDate('date', $today->toDateString())
            ->whereIn('employee_id', $allStaffIds)
            ->distinct('employee_id')
            ->count('employee_id');

        $leaveAppliedToday = (int) LeaveRequest::query()
            ->whereDate('created_at', $today->toDateString())
            ->whereIn('employee_id', $allStaffIds)
            ->count();

        $wfhAppliedToday = (int) WfhRequest::query()
            ->whereDate('created_at', $today->toDateString())
            ->whereIn('employee_id', $allStaffIds)
            ->count();

        $hasApprovalsTable = Schema::hasTable('approvals');
        $hasWorkflowInstancesTable = Schema::hasTable('workflow_instances');

        if ($hasApprovalsTable) {
            $approvalsRaisedToday = (int) Approval::query()
                ->whereDate('created_at', $today->toDateString())
                ->whereIn('requester_id', $allUserIds)
                ->count();

            $approvalsPending = (int) Approval::query()
                ->where('status', 'pending')
                ->whereIn('requester_id', $allUserIds)
                ->count();

            $approvalsChecked = (int) Approval::query()
                ->whereIn('status', ['approved', 'rejected'])
                ->whereIn('requester_id', $allUserIds)
                ->count();
        } elseif ($hasWorkflowInstancesTable) {
            $approvalBase = WorkflowInstance::query()->whereIn('initiator_id', $allUserIds);

            $approvalsRaisedToday = (int) (clone $approvalBase)
                ->whereDate('created_at', $today->toDateString())
                ->count();

            $approvalsPending = (int) (clone $approvalBase)
                ->where('status', 'pending')
                ->count();

            $approvalsChecked = (int) (clone $approvalBase)
                ->whereIn('status', ['approved', 'rejected', 'cancelled'])
                ->count();
        } else {
            $approvalsRaisedToday = 0;
            $approvalsPending = 0;
            $approvalsChecked = 0;
        }

        $approvalsUnchecked = $approvalsPending;

        $totalTeams = (int) Team::query()->whereIn('id', $managedTeamIds)->count();

        $presencePct = $teamCount > 0 ? round(($presentToday / $teamCount) * 100, 1) : 100.0;
        $offPlanPct = $totalTeamTasks > 0 ? round(($offPlanTasks / $totalTeamTasks) * 100, 1) : 0.0;

        $trackStatus = 'On Track';
        if ($completionRate < 65 || $presencePct < 70 || $offPlanPct > 35) {
            $trackStatus = 'Off Track';
        } elseif ($completionRate < 80 || $presencePct < 85 || $offPlanPct > 20) {
            $trackStatus = 'At Risk';
        }

        $presentByDay = AttendanceLog::query()
            ->whereIn('employee_id', $allStaffIds)
            ->whereBetween('date', [$last7Start->toDateString(), $today->toDateString()])
            ->whereIn('status', ['Present', 'present'])
            ->selectRaw('DATE(date) as day_key, COUNT(DISTINCT employee_id) as total')
            ->groupBy('day_key')
            ->pluck('total', 'day_key');

        $timesheetByDay = Timesheet::query()
            ->whereIn('employee_id', $allStaffIds)
            ->whereBetween('date', [$last7Start->toDateString(), $today->toDateString()])
            ->selectRaw('DATE(date) as day_key, COUNT(DISTINCT employee_id) as total')
            ->groupBy('day_key')
            ->pluck('total', 'day_key');

        if ($hasApprovalsTable) {
            $approvalsByDay = Approval::query()
                ->whereIn('requester_id', $allUserIds)
                ->whereBetween('created_at', [$last7Start->toDateString(), $today->toDateString()])
                ->selectRaw('DATE(created_at) as day_key, COUNT(*) as total')
                ->groupBy('day_key')
                ->pluck('total', 'day_key');
        } elseif ($hasWorkflowInstancesTable) {
            $approvalsByDay = WorkflowInstance::query()
                ->whereIn('initiator_id', $allUserIds)
                ->whereBetween('created_at', [$last7Start->toDateString(), $today->toDateString()])
                ->selectRaw('DATE(created_at) as day_key, COUNT(*) as total')
                ->groupBy('day_key')
                ->pluck('total', 'day_key');
        } else {
            $approvalsByDay = collect();
        }

        $completedTasksByDay = (clone $teamTasksQuery)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$last7Start->toDateString(), $today->toDateString()])
            ->selectRaw('DATE(updated_at) as day_key, COUNT(*) as total')
            ->groupBy('day_key')
            ->pluck('total', 'day_key');

        $leaveByDay = LeaveRequest::query()
            ->whereIn('employee_id', $allStaffIds)
            ->whereBetween('created_at', [$last7Start->toDateString(), $today->toDateString()])
            ->selectRaw('DATE(created_at) as day_key, COUNT(*) as total')
            ->groupBy('day_key')
            ->pluck('total', 'day_key');

        $wfhByDay = WfhRequest::query()
            ->whereIn('employee_id', $allStaffIds)
            ->whereBetween('created_at', [$last7Start->toDateString(), $today->toDateString()])
            ->selectRaw('DATE(created_at) as day_key, COUNT(*) as total')
            ->groupBy('day_key')
            ->pluck('total', 'day_key');

        $dailyOps = collect(range(0, 6))
            ->map(function (int $offset) use ($last7Start, $presentByDay, $timesheetByDay, $completedTasksByDay, $approvalsByDay, $leaveByDay, $wfhByDay) {
                $day = $last7Start->copy()->addDays($offset);
                $key = $day->toDateString();

                return [
                    'date' => $key,
                    'label' => $day->format('D'),
                    'present' => (int) ($presentByDay[$key] ?? 0),
                    'timesheet_filled' => (int) ($timesheetByDay[$key] ?? 0),
                    'tasks_completed' => (int) ($completedTasksByDay[$key] ?? 0),
                    'approvals_raised' => (int) ($approvalsByDay[$key] ?? 0),
                    'leave_applied' => (int) ($leaveByDay[$key] ?? 0),
                    'wfh_applied' => (int) ($wfhByDay[$key] ?? 0),
                ];
            })
            ->values();

        $upcomingDeadlines = (clone $teamTasksQuery)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', $today->toDateString())
            ->orderBy('due_date')
            ->take(3)
            ->get()
            ->map(function ($task) {
                return [
                    'title' => $task->title,
                    'date' => optional($task->due_date)->diffForHumans(),
                    'priority' => $task->priority,
                ];
            });

        return Inertia::render('Dashboard/Manager', [
            'teamCount' => $teamCount,
            'team_performance' => [
                'velocity' => $velocity,
                'sprint_completion' => $completionRate,
                'active_incidents' => $activeIncidents,
                'weekly_engagement' => $weeklyEngagement,
            ],
            'teamAttendance' => $teamAttendance,
            'upcomingDeadlines' => $upcomingDeadlines,
            'ops_launchers' => [
                'employee360_url' => '/hr/employee-360',
                'devops_global_url' => '/devops/dashboard',
                'ops360_url' => '/employee/work/ops360',
                'team_board_url' => '/employee/work',
                'attendance_url' => '/attendance',
            ],
            'ops_cards' => [
                'employee360' => [
                    'attendance_score' => (int) round($presencePct),
                    'productivity_score' => (int) round($completionRate),
                    'compliance_flags' => collect([
                        $offPlanTasks > 0 ? "{$offPlanTasks} task(s) not working as planned" : null,
                        $approvalsPending > 0 ? "{$approvalsPending} approval(s) pending" : null,
                        $timesheetFilledToday < $teamCount ? 'Timesheet missing for part of team today' : null,
                    ])->filter()->values()->all(),
                ],
                'squad_ops' => [
                    'pr_throughput' => round($velocity / 10, 2),
                    'review_lag_hours' => max(2, (int) round((100 - $velocity) / 4)),
                    'deployment_risk' => $trackStatus === 'Off Track' ? 'High' : ($trackStatus === 'At Risk' ? 'Medium' : 'Low'),
                    'open_prs' => max(0, $activeIncidents * 2),
                    'repos_linked' => max(1, (int) ceil($teamCount / 4)),
                ],
            ],
            'ops_overview' => [
                'total_projects' => $totalProjects,
                'active_projects' => $activeProjects,
                'inactive_projects' => $inactiveProjects,
                'total_teams' => $totalTeams,
                'total_members' => $teamCount,
                'approvals_raised_today' => $approvalsRaisedToday,
                'approvals_pending' => $approvalsPending,
                'approvals_checked' => $approvalsChecked,
                'approvals_unchecked' => $approvalsUnchecked,
                'timesheet_filled_today' => $timesheetFilledToday,
                'tasks_completed' => $completedTeamTasks,
                'tasks_total' => $totalTeamTasks,
                'tasks_total_all_projects' => $tasksTotalAllProjects,
                'tasks_active_all_projects' => $tasksActiveAllProjects,
                'tasks_inactive_all_projects' => $tasksInactiveAllProjects,
                'not_working_as_planned' => $offPlanTasks,
                'present_today' => $presentToday,
                'leave_applied_today' => $leaveAppliedToday,
                'wfh_applied_today' => $wfhAppliedToday,
                'bugs_total_count' => $bugsTotalCount,
                'bugs_open_total' => $bugsOpenTotal,
                'bugs_closed_today' => $bugsClosedToday,
                'bugs_pending_total' => $bugsPendingTotal,
                'track_status' => $trackStatus,
                'completion_pct' => round($completionRate, 1),
                'presence_pct' => $presencePct,
                'offtrack_pct' => $offPlanPct,
            ],
            'ops_graphs' => [
                'daily_ops' => $dailyOps,
            ],
            'team_insights' => [
                'employee360_trend' => $weeklyEngagement->values()->map(fn ($value, $index) => [
                    'label' => 'W' . ($index + 1),
                    'hours' => (int) $value,
                ]),
                'devops_pulse_trend' => $weeklyEngagement->values()->map(fn ($value, $index) => [
                    'label' => 'W' . ($index + 1),
                    'value' => (int) round($value / 12),
                ]),
            ],
        ]);
    }

    public function employee(Request $request)
    {
        $user = auth()->user();
        $employee = $user->employee;
        $enabledModules = AppModule::where('status', true)->pluck('key');

        $leaveBalance = 0;
        if ($employee) {
            $leaveBalance = \App\Models\LeaveBalance::where('employee_id', $employee->id)->sum(DB::raw('total_days - used_days'));
        }

        $pendingTasksCount = $employee ? Task::whereHas('assignees', function($q) use ($employee) {
            $q->where('employee_id', $employee->id);
        })->where('status', '!=', 'completed')->count() : 0;

        return Inertia::render('Dashboard/Employee', [
            'user' => [
                'name' => $user->name,
                'designation' => $employee->designation ?? 'Operative',
                'preferences' => $user->preferences
            ],
            'enabledModules' => $enabledModules,
            'quickStats' => [
                'leave_balance' => $leaveBalance ?: 12,
                'attendance_streak' => $employee ? (\App\Models\EmployeeStreak::where('employee_id', $employee->id)->value('current_streak') ?: 0) : 0,
                'pending_tasks' => $pendingTasksCount
            ]
        ]);
    }

    public function saveLayout(Request $request)
    {
        $request->validate(['layout' => 'required|array']);
        $user = auth()->user();
        $prefs = $user->preferences ?? [];
        $prefs['dashboard_layout'] = $request->layout;
        $user->preferences = $prefs;
        $user->save();
        return response()->json(['status' => 'success']);
    }

    public function getContext(Request $request)
    {
        $user = auth()->user();
        return response()->json([
            'user' => $user->only(['id', 'name', 'email']),
            'tenant' => Tenant::find($user->tenant_id)?->only(['id', 'name']),
            'enabledModules' => AppModule::where('status', true)->pluck('key'),
        ]);
    }

    public function getPulseWidget()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'activity_score' => 85,
                'trend' => '+5%',
                'recent_events' => [
                     ['id' => 1, 'type' => 'task', 'message' => 'New task assigned', 'time' => '10 min ago'],
                     ['id' => 2, 'type' => 'system', 'message' => 'System update completed', 'time' => '1 hour ago']
                ]
            ]
        ]);
    }

    public function getAttendanceWidget()
    {
        $user = auth()->user();
        $employeeId = $user->employee->id ?? null;
        
        $todayLog = null;
        if ($employeeId) {
            $todayLog = \App\Models\AttendanceLog::where('employee_id', $employeeId)
                                                ->whereDate('date', today())
                                                ->first();
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'checked_in' => $todayLog && $todayLog->check_in ? true : false,
                'check_in_time' => $todayLog ? $todayLog->check_in : null,
                'check_out_time' => $todayLog ? $todayLog->check_out : null,
                'attendance_status' => $todayLog ? $todayLog->status : 'Pending'
            ]
        ]);
    }
}
