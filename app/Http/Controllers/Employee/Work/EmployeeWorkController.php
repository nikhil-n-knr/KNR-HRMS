<?php

namespace App\Http\Controllers\Employee\Work;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\AttendanceLog;
use App\Models\BugTicket;
use App\Models\Employee;
use App\Models\GitPrReview;
use App\Models\GitPullRequest;
use App\Models\GitRepository;
use App\Models\LeaveRequest;
use App\Models\Project;
use App\Models\ProjectStage;
use App\Models\Task;
use App\Models\Team;
use App\Models\Timesheet;
use App\Models\User;
use App\Models\WfhRequest;
use App\Models\WorkAssignment;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeWorkController extends Controller
{
    private ?array $taskChecklistColumns = null;

    public function index(Request $request): Response
    {
        $user = $request->user();
        $employeeId = $this->resolveEmployeeId($user);
        $defaultFilters = $this->userWorkDefaults($user);
        $today = Carbon::today();

        $search = trim((string) $this->effectiveFilter($request, $defaultFilters, 'q', ''));
        $projectId = $this->normalizeNullableInt($this->effectiveFilter($request, $defaultFilters, 'project_id'));
        $priority = $this->normalizeNullableString($this->effectiveFilter($request, $defaultFilters, 'priority'));
        $taskStatusFilter = $this->normalizeNullableString($this->effectiveFilter($request, $defaultFilters, 'task_status'));
        $bugStageFilter = $this->normalizeNullableString($this->effectiveFilter($request, $defaultFilters, 'bug_stage'));
        $quickFilter = $this->normalizeNullableString($this->effectiveFilter($request, $defaultFilters, 'quick_filter'));
        $focusMode = $this->normalizeBoolean($this->effectiveFilter($request, $defaultFilters, 'focus_mode', false));
        $dateFrom = $this->normalizeNullableString($this->effectiveFilter($request, $defaultFilters, 'date_from'));
        $dateTo = $this->normalizeNullableString($this->effectiveFilter($request, $defaultFilters, 'date_to'));
        $rangeStart = $this->normalizeDateBoundary($dateFrom, 'start');
        $rangeEnd = $this->normalizeDateBoundary($dateTo, 'end');

        if ($rangeStart && $rangeEnd && $rangeStart->greaterThan($rangeEnd)) {
            [$rangeStart, $rangeEnd] = [$rangeEnd->copy()->startOfDay(), $rangeStart->copy()->endOfDay()];
            $dateFrom = $rangeStart->toDateString();
            $dateTo = $rangeEnd->toDateString();
        }

        $rangeLabel = $this->buildRangeLabel($rangeStart, $rangeEnd);

        $defaultTab = (string) ($defaultFilters['tab'] ?? 'tasks');
        $requestedTab = (string) $this->effectiveFilter($request, $defaultFilters, 'tab', $defaultTab);
        $activeTab = in_array($requestedTab, ['tasks', 'bugs'], true) ? $requestedTab : 'tasks';

        $hasChecklistPlannedMinutes = $this->taskChecklistColumnExists('planned_minutes');
        $hasChecklistActualMinutes = $this->taskChecklistColumnExists('actual_minutes');
        $hasChecklistWorkDate = $this->taskChecklistColumnExists('work_date');
        $hasChecklistCompletedAt = $this->taskChecklistColumnExists('completed_at');

        $taskQuery = Task::query()
            ->with(['project:id,name', 'stage:id,name,color,type'])
            ->withCount([
                'checklists as checklist_total',
                'checklists as checklist_completed' => function (Builder $q) {
                    $q->where('is_completed', true);
                },
            ])
            ->withMax('activities as last_activity_at', 'created_at')
            ->withSum('timesheets as timesheet_hours_total', 'hours_spent')
            ->where(function (Builder $query) use ($user) {
                $this->applyTaskAssignmentScope($query, $user);
            });

        if ($employeeId) {
            $taskQuery->withSum([
                'timesheets as timesheet_hours_me' => function (Builder $q) use ($employeeId) {
                    $q->where('employee_id', $employeeId);
                },
            ], 'hours_spent');

            $taskQuery->withCount([
                'timesheets as worked_today_count' => function (Builder $q) use ($employeeId, $today) {
                    $q->where('employee_id', $employeeId)
                        ->whereDate('date', $today);
                },
            ]);

            $taskQuery->withSum([
                'timesheets as timesheet_hours_today' => function (Builder $q) use ($employeeId, $today) {
                    $q->where('employee_id', $employeeId)
                        ->whereDate('date', $today);
                },
            ], 'hours_spent');
        }

        if ($hasChecklistPlannedMinutes) {
            $taskQuery->withSum('checklists as checklist_planned_minutes_total', 'planned_minutes');
        }

        if ($hasChecklistActualMinutes) {
            $taskQuery->withSum('checklists as checklist_actual_minutes_total', 'actual_minutes');
        }

        if ($hasChecklistWorkDate) {
            $taskQuery->withCount([
                'checklists as checklist_worked_today_count' => function (Builder $q) use ($today) {
                    $q->whereDate('work_date', $today);
                },
            ]);
        }

        if ($hasChecklistCompletedAt) {
            $taskQuery->withCount([
                'checklists as checklist_done_today_count' => function (Builder $q) use ($today) {
                    $q->where('is_completed', true)
                        ->whereDate('completed_at', $today);
                },
            ]);
        }

        if ($search !== '') {
            $taskQuery->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($projectId) {
            $taskQuery->where('project_id', $projectId);
        }

        if ($priority) {
            $taskQuery->where('priority', $priority);
        }

        if ($taskStatusFilter) {
            $taskStatus = $taskStatusFilter;
            $taskQuery->whereHas('stage', function (Builder $q) use ($taskStatus) {
                if (is_numeric($taskStatus)) {
                    $q->where('id', (int) $taskStatus);
                    return;
                }

                $q->where('name', $taskStatus);
            });
        }

        if ($focusMode) {
            $taskQuery->where(function (Builder $q) {
                $q->whereIn('priority', ['Critical', 'critical', 'High', 'high', 'Urgent', 'urgent', 'P0', 'P1'])
                    ->orWhereDate('due_date', '<=', Carbon::today()->addDay());
            });
        }

        if ($quickFilter) {
            $this->applyTaskQuickFilter($taskQuery, $quickFilter, $today, $employeeId, $hasChecklistWorkDate, $hasChecklistCompletedAt);
        }

        if ($rangeStart || $rangeEnd) {
            $taskQuery->where(function (Builder $q) use ($rangeStart, $rangeEnd) {
                $q->where(function (Builder $dateQ) use ($rangeStart, $rangeEnd) {
                    $dateQ->whereNotNull('due_date');
                    if ($rangeStart) {
                        $dateQ->whereDate('due_date', '>=', $rangeStart->toDateString());
                    }
                    if ($rangeEnd) {
                        $dateQ->whereDate('due_date', '<=', $rangeEnd->toDateString());
                    }
                })->orWhereNull('due_date');
            });
        }

        $baselineTaskQuery = clone $taskQuery;
        $taskReportingQuery = clone $taskQuery;

        $tasks = $taskQuery
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->paginate(12, ['*'], 'tasks_page')
            ->withQueryString()
            ->through(function (Task $task) use ($today) {
                $totalChecklist = (int) ($task->checklist_total ?? 0);
                $doneChecklist = (int) ($task->checklist_completed ?? 0);
                $plannedMinutes = (int) ($task->checklist_planned_minutes_total ?? 0);
                $actualMinutes = (int) ($task->checklist_actual_minutes_total ?? 0);
                $estimatedHours = (float) ($task->estimated_hours ?? 0);
                $timesheetHoursTotal = (float) ($task->timesheet_hours_total ?? 0);
                $timesheetHoursMe = (float) ($task->timesheet_hours_me ?? 0);
                $timesheetHoursToday = (float) ($task->timesheet_hours_today ?? 0);
                $workedToday = ((int) ($task->worked_today_count ?? 0) > 0) || ((int) ($task->checklist_worked_today_count ?? 0) > 0);
                $doneToday = (int) ($task->checklist_done_today_count ?? 0) > 0;
                $isDone = $this->isTaskDone($task, $doneChecklist, $totalChecklist);
                $isOverdue = !$isDone && $task->due_date && Carbon::parse($task->due_date)->isBefore($today);
                $hasNoEffort = $timesheetHoursMe <= 0 && $actualMinutes <= 0;
                $overrunWarning = $plannedMinutes > 0 && $actualMinutes > (int) round($plannedMinutes * 1.2);

                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'is_locked' => (bool) $task->is_locked,
                    'priority' => $task->priority,
                    'due_date' => $task->due_date,
                    'estimated_hours' => $estimatedHours,
                    'blocked_by_task_id' => $task->blocked_by_task_id,
                    'project_id' => $task->project_id,
                    'project' => $task->project ? [
                        'id' => $task->project->id,
                        'name' => $task->project->name,
                    ] : null,
                    'stage' => $task->stage ? [
                        'id' => $task->stage->id,
                        'name' => $task->stage->name,
                        'color' => $task->stage->color,
                        'type' => $task->stage->type,
                    ] : null,
                    'is_done' => $isDone,
                    'signals' => [
                        'worked_today' => $workedToday,
                        'done_today' => $doneToday,
                        'blocked' => !empty($task->blocked_by_task_id),
                        'no_effort_logged' => $hasNoEffort,
                        'overdue' => $isOverdue,
                        'overrun_warning' => $overrunWarning,
                        'last_activity_at' => $task->last_activity_at,
                    ],
                    'checklist' => [
                        'completed' => $doneChecklist,
                        'total' => $totalChecklist,
                        'progress_percent' => $totalChecklist > 0 ? round(($doneChecklist / $totalChecklist) * 100, 1) : 0,
                        'planned_minutes_total' => $plannedMinutes,
                        'actual_minutes_total' => $actualMinutes,
                    ],
                    'reporting' => [
                        'task_estimated_hours' => $estimatedHours,
                        'checklist_estimated_hours' => round($plannedMinutes / 60, 2),
                        'checklist_actual_hours' => round($actualMinutes / 60, 2),
                        'timesheet_hours_total' => round($timesheetHoursTotal, 2),
                        'timesheet_hours_me' => round($timesheetHoursMe, 2),
                        'timesheet_hours_today' => round($timesheetHoursToday, 2),
                        'variance_hours' => round($timesheetHoursTotal - $estimatedHours, 2),
                    ],
                ];
            });

        $bugQuery = BugTicket::query()
            ->with(['project:id,name', 'stage:id,name'])
            ->where(function (Builder $query) use ($user) {
                $this->applyBugAssignmentScope($query, $user);
            });

        if ($search !== '') {
            $bugQuery->where(function (Builder $q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($projectId) {
            $bugQuery->where('project_id', $projectId);
        }

        if ($rangeStart || $rangeEnd) {
            $bugQuery->where(function (Builder $q) use ($rangeStart, $rangeEnd) {
                if ($rangeStart) {
                    $q->whereDate('created_at', '>=', $rangeStart->toDateString());
                }
                if ($rangeEnd) {
                    $q->whereDate('created_at', '<=', $rangeEnd->toDateString());
                }
            });
        }

        if ($priority) {
            $bugQuery->where('priority', $priority);
        }

        if ($bugStageFilter) {
            $bugStage = $bugStageFilter;
            $bugQuery->whereHas('stage', function (Builder $q) use ($bugStage) {
                if (is_numeric($bugStage)) {
                    $q->where('id', (int) $bugStage);
                    return;
                }

                $q->where('name', $bugStage);
            });
        }

        $bugs = $bugQuery
            ->latest()
            ->paginate(12, ['*'], 'bugs_page')
            ->withQueryString()
            ->through(function (BugTicket $bug) {
                return [
                    'id' => $bug->id,
                    'subject' => $bug->subject,
                    'severity' => $bug->severity,
                    'priority' => $bug->priority,
                    'project_id' => $bug->project_id,
                    'project' => $bug->project ? [
                        'id' => $bug->project->id,
                        'name' => $bug->project->name,
                    ] : null,
                    'stage' => $bug->stage ? [
                        'id' => $bug->stage->id,
                        'name' => $bug->stage->name,
                    ] : null,
                    'is_done' => $this->isBugDone($bug),
                    'created_at' => $bug->created_at,
                ];
            });

        $taskReportingRows = $taskReportingQuery->get();

        $taskIds = $taskReportingRows->pluck('id')->filter()->values();

        $assignmentRows = WorkAssignment::query()
            ->whereIn('task_id', $taskIds)
            ->whereNotNull('allocated_hours')
            ->where(function (Builder $q) use ($employeeId, $user) {
                if ($employeeId) {
                    $q->orWhere(function (Builder $empQ) use ($employeeId) {
                        $empQ->where('assignee_type', Employee::class)
                            ->where('assignee_id', $employeeId);
                    });
                }

                $q->orWhere(function (Builder $userQ) use ($user) {
                    $userQ->where('assignee_type', User::class)
                        ->where('assignee_id', $user->id);
                });
            })
            ->get(['project_id', 'task_id', 'allocated_hours', 'start_date', 'end_date']);

        $plannedHoursByProject = $assignmentRows
            ->groupBy('project_id')
            ->map(function ($rows) use ($rangeStart, $rangeEnd, $today) {
                return round((float) $rows->sum(function (WorkAssignment $assignment) use ($rangeStart, $rangeEnd, $today) {
                    return $this->assignmentPlannedHoursForRange($assignment, $rangeStart, $rangeEnd, $today);
                }), 2);
            });

        $projectPlanRows = $taskReportingRows
            ->groupBy('project_id')
            ->map(function ($projectTasks) use ($today, $plannedHoursByProject) {
                $firstTask = $projectTasks->first();
                $taskEstimate = (float) $projectTasks->sum(fn ($task) => (float) ($task->estimated_hours ?? 0));
                $checklistPlannedMinutes = (int) $projectTasks->sum(fn ($task) => (int) ($task->checklist_planned_minutes_total ?? 0));
                $checklistActualMinutes = (int) $projectTasks->sum(fn ($task) => (int) ($task->checklist_actual_minutes_total ?? 0));
                $timesheetHours = (float) $projectTasks->sum(fn ($task) => (float) ($task->timesheet_hours_me ?? $task->timesheet_hours_total ?? 0));
                $todayTasks = $projectTasks->filter(fn ($task) => $task->due_date && Carbon::parse($task->due_date)->isSameDay($today))->count();
                $plannedHours = (float) ($plannedHoursByProject[$firstTask?->project_id] ?? 0);

                return [
                    'project_id' => $firstTask?->project_id,
                    'project_name' => $firstTask?->project?->name ?? 'Unknown Project',
                    'task_count' => $projectTasks->count(),
                    'today_due_count' => $todayTasks,
                    'estimated_hours' => round($plannedHours, 2),
                    'task_estimated_hours' => round($taskEstimate, 2),
                    'checklist_planned_hours' => round($checklistPlannedMinutes / 60, 2),
                    'checklist_actual_hours' => round($checklistActualMinutes / 60, 2),
                    'timesheet_hours' => round($timesheetHours, 2),
                    'variance_hours' => round($timesheetHours - $plannedHours, 2),
                ];
            })
            ->sortByDesc('estimated_hours')
            ->values();

        $todayTasksCount = $taskReportingRows->filter(fn ($task) => $task->due_date && Carbon::parse($task->due_date)->isSameDay($today))->count();
        $overdueTaskCount = $taskReportingRows->filter(fn ($task) => $task->due_date && Carbon::parse($task->due_date)->isBefore($today))->count();
        $blockedTaskCount = $taskReportingRows->filter(fn ($task) => !empty($task->blocked_by_task_id))->count();
        $todayEstimatedHours = (float) $taskReportingRows
            ->filter(fn ($task) => $task->due_date && Carbon::parse($task->due_date)->isSameDay($today))
            ->sum(fn ($task) => (float) ($task->estimated_hours ?? 0));

        // Include active allocations for today for both Employee and User assignees.
        $todayAllocationHours = (float) WorkAssignment::query()
            ->where(function (Builder $q) use ($employeeId, $user) {
                if ($employeeId) {
                    $q->orWhere(function (Builder $empQ) use ($employeeId) {
                        $empQ->where('assignee_type', Employee::class)
                            ->where('assignee_id', $employeeId);
                    });
                }

                $q->orWhere(function (Builder $userQ) use ($user) {
                    $userQ->where('assignee_type', User::class)
                        ->where('assignee_id', $user->id);
                });
            })
            ->where(function (Builder $q) use ($today) {
                $q->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', $today->toDateString());
            })
            ->where(function (Builder $q) use ($today) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $today->toDateString());
            })
            ->sum('allocated_hours');

        $todayEstimatedHours += $todayAllocationHours;
        $todayTimesheetHours = (float) $taskReportingRows->sum(fn ($task) => (float) ($task->timesheet_hours_today ?? 0));
        $totalEstimatedHours = (float) $projectPlanRows->sum(fn ($row) => (float) ($row['estimated_hours'] ?? 0));

        $todayWorkRows = $baselineTaskQuery->get();
        $todayWorkCount = $todayWorkRows->filter(fn ($task) =>
            ($task->due_date && Carbon::parse($task->due_date)->isSameDay($today))
            || (float) ($task->timesheet_hours_today ?? 0) > 0
            || (int) ($task->worked_today_count ?? 0) > 0
            || (int) ($task->checklist_worked_today_count ?? 0) > 0
        )->count();
        $todayWorkHours = (float) $todayWorkRows->sum(fn ($task) => (float) ($task->timesheet_hours_today ?? 0));
        $todayPlannedHours = (float) $todayWorkRows
            ->filter(fn ($task) => $task->due_date && Carbon::parse($task->due_date)->isSameDay($today))
            ->sum(fn ($task) => (float) ($task->estimated_hours ?? 0));
        $todayPlannedHours += $todayAllocationHours;

        $rangeTaskCount = $taskReportingRows->count();
        $rangeChecklistPlannedHours = round((float) $projectPlanRows->sum('checklist_planned_hours'), 2);
        $rangeChecklistActualHours = round((float) $projectPlanRows->sum('checklist_actual_hours'), 2);
        $rangeTimesheetHours = round((float) $projectPlanRows->sum('timesheet_hours'), 2);
        $rangeVarianceHours = round($rangeTimesheetHours - $totalEstimatedHours, 2);
        $todayVarianceHours = round($todayTimesheetHours - $totalEstimatedHours, 2);
        $todayDoneCount = $taskReportingRows->filter(fn ($task) => (int) ($task->checklist_done_today_count ?? 0) > 0)->count();
        $todayTouchedCount = $taskReportingRows->filter(fn ($task) => ((int) ($task->worked_today_count ?? 0) > 0) || ((int) ($task->checklist_worked_today_count ?? 0) > 0))->count();

        $todayCommandTasks = $taskReportingRows
            ->filter(fn ($task) =>
                ($task->due_date && Carbon::parse($task->due_date)->isSameDay($today))
                || (float) ($task->timesheet_hours_today ?? 0) > 0
                || (int) ($task->worked_today_count ?? 0) > 0
                || (int) ($task->checklist_worked_today_count ?? 0) > 0
                || (!empty($task->blocked_by_task_id) && $task->due_date && Carbon::parse($task->due_date)->isBefore($today))
            )
            ->sortBy([
                fn ($task) => !$task->due_date,
                fn ($task) => $task->due_date ? Carbon::parse($task->due_date)->timestamp : PHP_INT_MAX,
            ])
            ->take(8)
            ->map(function ($task) use ($today) {
                $isOverdue = $task->due_date && Carbon::parse($task->due_date)->isBefore($today);
                $isDueToday = $task->due_date && Carbon::parse($task->due_date)->isSameDay($today);

                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'project_name' => $task->project?->name,
                    'due_date' => $task->due_date,
                    'priority' => $task->priority,
                    'is_overdue' => $isOverdue,
                    'is_due_today' => $isDueToday,
                    'is_blocked' => !empty($task->blocked_by_task_id),
                    'worked_today' => ((int) ($task->worked_today_count ?? 0) > 0) || ((int) ($task->checklist_worked_today_count ?? 0) > 0),
                    'timesheet_hours_today' => round((float) ($task->timesheet_hours_today ?? 0), 2),
                ];
            })
            ->values();

        $todayCommand = [
            'date' => $today->toDateString(),
            'range_label' => $rangeLabel,
            'tasks_due_today' => $todayTasksCount,
            'tasks_touched_today' => $todayTouchedCount,
            'tasks_done_today' => $todayDoneCount,
            'hours_logged_today' => round($todayTimesheetHours, 2),
            'hours_planned_today' => round($todayEstimatedHours, 2),
            'overdue_tasks' => $overdueTaskCount,
            'blocked_tasks' => $blockedTaskCount,
            'attention_tasks' => $todayCommandTasks,
        ];

        $rangeSummary = [
            'label' => $rangeLabel,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'task_count' => $rangeTaskCount,
            'project_count' => $projectPlanRows->count(),
            'task_estimated_hours' => round($totalEstimatedHours, 2),
            'timesheet_hours' => round($todayTimesheetHours, 2),
            'timesheet_hours_total' => $rangeTimesheetHours,
            'checklist_planned_hours' => $rangeChecklistPlannedHours,
            'checklist_actual_hours' => $rangeChecklistActualHours,
            'variance_hours' => $todayVarianceHours,
            'variance_hours_total' => $rangeVarianceHours,
        ];

        $timesheetInsights = [
            'timesheet_url' => url('/attendance?tab=timesheets'),
            'today' => [
                'date' => $today->toDateString(),
                'hours' => 0.0,
                'filled' => false,
            ],
            'last_week' => [
                'start' => null,
                'end' => null,
                'hours' => 0.0,
                'filled_days' => 0,
                'target_days' => 7,
                'filled' => false,
            ],
            'last_7_days' => [],
            'project_breakdown' => [],
            'weekly_overall' => [],
            'analytics' => [
                'avg_hours_last_7' => 0.0,
                'best_day_hours' => 0.0,
                'best_day_label' => '-',
                'consistency_pct' => 0,
                'missing_last_7' => 7,
            ],
            'weekly_analytics' => [
                'avg_hours_last_8_weeks' => 0.0,
                'best_week_hours' => 0.0,
                'best_week_label' => '-',
            ],
        ];

        if ($employeeId) {
            $timesheetBase = Timesheet::query()->where('employee_id', $employeeId);

            $todayHours = round((float) (clone $timesheetBase)
                ->whereDate('date', $today->toDateString())
                ->sum('hours_spent'), 2);

            $lastWeekStart = $today->copy()->subWeek()->startOfWeek(Carbon::MONDAY);
            $lastWeekEnd = $today->copy()->subWeek()->endOfWeek(Carbon::MONDAY);

            $lastWeekByDay = (clone $timesheetBase)
                ->whereBetween('date', [$lastWeekStart->toDateString(), $lastWeekEnd->toDateString()])
                ->selectRaw('DATE(date) as entry_date, SUM(hours_spent) as total_hours')
                ->groupBy('entry_date')
                ->pluck('total_hours', 'entry_date');

            $lastWeekHours = round((float) $lastWeekByDay->sum(), 2);
            $lastWeekFilledDays = (int) $lastWeekByDay->filter(fn ($hours) => (float) $hours > 0)->count();

            $sevenDayStart = $today->copy()->subDays(6);
            $sevenDayByDay = (clone $timesheetBase)
                ->whereBetween('date', [$sevenDayStart->toDateString(), $today->toDateString()])
                ->selectRaw('DATE(date) as entry_date, SUM(hours_spent) as total_hours')
                ->groupBy('entry_date')
                ->pluck('total_hours', 'entry_date');

            $sevenDaySeries = collect(range(0, 6))
                ->map(function (int $offset) use ($sevenDayStart, $sevenDayByDay) {
                    $day = $sevenDayStart->copy()->addDays($offset);
                    $hours = round((float) ($sevenDayByDay[$day->toDateString()] ?? 0), 2);

                    return [
                        'date' => $day->toDateString(),
                        'label' => $day->format('D'),
                        'hours' => $hours,
                        'filled' => $hours > 0,
                    ];
                })
                ->values();

            $bestDay = $sevenDaySeries->sortByDesc('hours')->first();
            $filledLast7 = (int) $sevenDaySeries->where('filled', true)->count();

            $projectRows = (clone $timesheetBase)
                ->whereBetween('date', [$sevenDayStart->toDateString(), $today->toDateString()])
                ->selectRaw('COALESCE(project_id, 0) as project_key, DATE(date) as entry_date, SUM(hours_spent) as total_hours, MAX(project_name) as fallback_project_name')
                ->groupBy('project_key', 'entry_date')
                ->get();

            $projectIds = $projectRows
                ->pluck('project_key')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            $projectNameMap = Project::query()
                ->whereIn('id', $projectIds)
                ->pluck('name', 'id');

            $projectBreakdown = $projectRows
                ->groupBy('project_key')
                ->map(function ($rows, $projectKey) use ($projectNameMap, $sevenDayStart) {
                    $projectId = (int) $projectKey;
                    $dayMap = $rows->pluck('total_hours', 'entry_date');
                    $fallbackName = (string) ($rows->first()->fallback_project_name ?? '');

                    if ($projectId > 0) {
                        $projectName = $projectNameMap[$projectId] ?? ($fallbackName !== '' ? $fallbackName : ('Project #' . $projectId));
                    } else {
                        $projectName = $fallbackName !== '' ? $fallbackName : 'Unassigned';
                    }

                    $dailySeries = collect(range(0, 6))
                        ->map(function (int $offset) use ($sevenDayStart, $dayMap) {
                            $day = $sevenDayStart->copy()->addDays($offset);
                            return round((float) ($dayMap[$day->toDateString()] ?? 0), 2);
                        })
                        ->values();

                    return [
                        'project_id' => $projectId > 0 ? $projectId : null,
                        'project_name' => $projectName,
                        'hours' => round((float) $rows->sum('total_hours'), 2),
                        'daily' => $dailySeries,
                    ];
                })
                ->sortByDesc('hours')
                ->values()
                ->take(6)
                ->values();

            $weeklyStart = $today->copy()->subWeeks(7)->startOfWeek(Carbon::MONDAY);
            $weeklyBuckets = (clone $timesheetBase)
                ->whereBetween('date', [$weeklyStart->toDateString(), $today->toDateString()])
                ->selectRaw('YEARWEEK(date, 1) as week_key, SUM(hours_spent) as total_hours')
                ->groupBy('week_key')
                ->pluck('total_hours', 'week_key');

            $weeklySeries = collect(range(0, 7))
                ->map(function (int $offset) use ($weeklyStart, $weeklyBuckets) {
                    $weekStart = $weeklyStart->copy()->addWeeks($offset);
                    $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
                    $weekKey = (int) ($weekStart->isoWeekYear . str_pad((string) $weekStart->isoWeek, 2, '0', STR_PAD_LEFT));
                    $hours = round((float) ($weeklyBuckets[$weekKey] ?? 0), 2);

                    return [
                        'week_start' => $weekStart->toDateString(),
                        'week_end' => $weekEnd->toDateString(),
                        'label' => $weekStart->format('d M'),
                        'hours' => $hours,
                    ];
                })
                ->values();

            $bestWeek = $weeklySeries->sortByDesc('hours')->first();

            $timesheetInsights = [
                'timesheet_url' => url('/attendance?tab=timesheets'),
                'today' => [
                    'date' => $today->toDateString(),
                    'hours' => $todayHours,
                    'filled' => $todayHours > 0,
                ],
                'last_week' => [
                    'start' => $lastWeekStart->toDateString(),
                    'end' => $lastWeekEnd->toDateString(),
                    'hours' => $lastWeekHours,
                    'filled_days' => $lastWeekFilledDays,
                    'target_days' => 7,
                    'filled' => $lastWeekFilledDays > 0,
                ],
                'last_7_days' => $sevenDaySeries,
                'project_breakdown' => $projectBreakdown,
                'weekly_overall' => $weeklySeries,
                'analytics' => [
                    'avg_hours_last_7' => round($sevenDaySeries->avg('hours') ?? 0, 2),
                    'best_day_hours' => round((float) ($bestDay['hours'] ?? 0), 2),
                    'best_day_label' => $bestDay['label'] ?? '-',
                    'consistency_pct' => (int) round(($filledLast7 / 7) * 100),
                    'missing_last_7' => 7 - $filledLast7,
                ],
                'weekly_analytics' => [
                    'avg_hours_last_8_weeks' => round($weeklySeries->avg('hours') ?? 0, 2),
                    'best_week_hours' => round((float) ($bestWeek['hours'] ?? 0), 2),
                    'best_week_label' => ($bestWeek['label'] ?? '-') . ' week',
                ],
            ];
        }

        $planCards = [
            [
                'key' => 'today-work',
                'label' => "Today's Work",
                'value' => round($todayWorkHours, 2),
                'caption' => 'hours logged today',
                'meta' => [
                    'today_work_count' => $todayWorkCount,
                    'today_planned_hours' => round($todayPlannedHours, 2),
                ],
            ],
            [
                'key' => 'today-plan',
                'label' => 'Today Plan',
                'value' => $todayTasksCount,
                'caption' => 'tasks due today',
                'meta' => [
                    'today_estimated_hours' => round($todayEstimatedHours, 2),
                    'today_timesheet_hours' => round($todayTimesheetHours, 2),
                ],
            ],
            [
                'key' => 'projects',
                'label' => 'Projects In Hand',
                'value' => $projectPlanRows->count(),
                'caption' => 'active assigned projects',
                'meta' => [
                    'names' => $projectPlanRows->pluck('project_name')->take(4)->values(),
                ],
            ],
            [
                'key' => 'planned-effort',
                'label' => 'Planned Effort',
                'value' => round($totalEstimatedHours, 2),
                'caption' => 'total task estimate hours',
                'meta' => [
                    'checklist_plan_hours' => round((float) $projectPlanRows->sum('checklist_planned_hours'), 2),
                    'checklist_actual_hours' => round((float) $projectPlanRows->sum('checklist_actual_hours'), 2),
                ],
            ],
            [
                'key' => 'attention',
                'label' => 'Needs Attention',
                'value' => $overdueTaskCount + $blockedTaskCount,
                'caption' => 'overdue + blocked tasks',
                'meta' => [
                    'overdue' => $overdueTaskCount,
                    'blocked' => $blockedTaskCount,
                ],
            ],
        ];

        $assignedTaskProjectIds = Task::query()
            ->where(function (Builder $query) use ($user) {
                $this->applyTaskAssignmentScope($query, $user);
            })
            ->pluck('project_id');

        $assignedBugProjectIds = BugTicket::query()
            ->where(function (Builder $query) use ($user) {
                $this->applyBugAssignmentScope($query, $user);
            })
            ->pluck('project_id');

        $projectIds = $assignedTaskProjectIds
            ->merge($assignedBugProjectIds)
            ->filter()
            ->unique()
            ->values();

        $projectOptions = Project::query()
            ->whereIn('id', $projectIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function (Project $project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                ];
            })
            ->values();

        $taskPriorityOptions = Task::query()
            ->where(function (Builder $query) use ($user) {
                $this->applyTaskAssignmentScope($query, $user);
            })
            ->whereNotNull('priority')
            ->distinct()
            ->orderBy('priority')
            ->pluck('priority')
            ->values();

        $bugPriorityOptions = BugTicket::query()
            ->where(function (Builder $query) use ($user) {
                $this->applyBugAssignmentScope($query, $user);
            })
            ->whereNotNull('priority')
            ->distinct()
            ->orderBy('priority')
            ->pluck('priority')
            ->values();

        $taskStageOptions = ProjectStage::query()
            ->whereIn('project_id', $projectIds)
            ->orderBy('project_id')
            ->orderBy('order')
            ->get(['id', 'project_id', 'name'])
            ->groupBy('project_id')
            ->map(function ($stages) {
                return $stages->map(function ($stage) {
                    return [
                        'id' => $stage->id,
                        'name' => $stage->name,
                    ];
                })->values();
            });

        $bugStageOptions = WorkflowStage::query()
            ->orderBy('workflow_id')
            ->orderBy('stage_order')
            ->get(['id', 'name'])
            ->map(function ($stage) {
                return [
                    'id' => $stage->id,
                    'name' => $stage->name,
                ];
            })
            ->values();

        $ops360 = $this->buildOps360Snapshot($user, $employeeId, $projectIds);

        return Inertia::render('Employee/Work/Index', [
            'activeTab' => $activeTab,
            'filters' => [
                'q' => $search,
                'project_id' => $projectId,
                'priority' => $priority,
                'task_status' => $taskStatusFilter,
                'bug_stage' => $bugStageFilter,
                'quick_filter' => $quickFilter,
                'focus_mode' => $focusMode,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'tab' => $activeTab,
            ],
            'savedDefaults' => $defaultFilters,
            'savedPresets' => $this->userWorkPresets($user),
            'todayCommand' => $todayCommand,
            'rangeSummary' => $rangeSummary,
            'timesheetInsights' => $timesheetInsights,
            'planCards' => $planCards,
            'projectPlanRows' => $projectPlanRows,
            'summary' => [
                'tasks_count' => $tasks->total(),
                'bugs_count' => $bugs->total(),
            ],
            'tasks' => $tasks,
            'bugs' => $bugs,
            'projectOptions' => $projectOptions,
            'taskPriorityOptions' => $taskPriorityOptions,
            'bugPriorityOptions' => $bugPriorityOptions,
            'taskStageOptions' => $taskStageOptions,
            'bugStageOptions' => $bugStageOptions,
            'ops360' => $ops360,
        ]);
    }

    public function ops360(Request $request): Response
    {
        $user = $request->user();
        $employeeId = $this->resolveEmployeeId($user);

        $assignedTaskProjectIds = Task::query()
            ->where(function (Builder $query) use ($user) {
                $this->applyTaskAssignmentScope($query, $user);
            })
            ->pluck('project_id');

        $assignedBugProjectIds = BugTicket::query()
            ->where(function (Builder $query) use ($user) {
                $this->applyBugAssignmentScope($query, $user);
            })
            ->pluck('project_id');

        $projectIds = $assignedTaskProjectIds
            ->merge($assignedBugProjectIds)
            ->filter()
            ->unique()
            ->values();

        $activeTab = in_array((string) $request->query('tab', 'employee360'), ['employee360', 'squad_ops'], true)
            ? (string) $request->query('tab', 'employee360')
            : 'employee360';

        $canView360 = $this->userHasAnyRole($user, ['Super Admin', 'Admin', 'HR Manager', 'Manager', 'Team Manager', 'HR Admin', 'Team Lead']);
        $employeesList = [];
        if ($canView360) {
            $empQuery = Employee::with('user:id,name')->where('status', 'active');
            // Restrict to direct reports only when reporting_to data is actually configured
            if (!$this->userHasAnyRole($user, ['Super Admin', 'Admin', 'HR Manager', 'HR Admin'])) {
                $hasDirectReports = Employee::where('status', 'active')->where('reporting_to', $user->id)->exists();
                if ($hasDirectReports) {
                    $empQuery->where('reporting_to', $user->id);
                }
                // If no direct reports configured, fall through and show all active employees
            }
            $employeesList = $empQuery->get()->map(fn ($e) => [
                'id'          => $e->id,
                'name'        => optional($e->user)->name ?? trim("{$e->first_name} {$e->last_name}"),
                'designation' => $e->designation ?? '',
            ])->values()->toArray();
        }

        return Inertia::render('Employee/Work/Ops360', [
            'activeTab'       => $activeTab,
            'ops360'          => $this->buildOps360Snapshot($user, $employeeId, $projectIds),
            'employees'       => $employeesList,
            'can_view_360'    => $canView360,
            'date_start'      => now()->subDays(30)->format('Y-m-d'),
            'date_end'        => now()->format('Y-m-d'),
        ]);
    }

    public function saveDefaults(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'project_id' => 'nullable|integer|exists:projects,id',
            'priority' => 'nullable|string|max:100',
            'task_status' => 'nullable|string|max:100',
            'bug_stage' => 'nullable|string|max:100',
            'quick_filter' => 'nullable|string|max:100',
            'focus_mode' => 'nullable|boolean',
            'tab' => 'nullable|in:tasks,bugs',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'save_as_preset' => 'nullable|boolean',
            'preset_name' => 'nullable|string|max:60',
        ]);

        $user = $request->user();
        $preferences = $user->preferences ?? [];

        $preferences['employee_work'] = [
            'filters' => [
                'q' => trim((string) ($validated['q'] ?? '')),
                'project_id' => $validated['project_id'] ?? null,
                'priority' => $this->normalizeNullableString($validated['priority'] ?? null),
                'task_status' => $this->normalizeNullableString($validated['task_status'] ?? null),
                'bug_stage' => $this->normalizeNullableString($validated['bug_stage'] ?? null),
                'quick_filter' => $this->normalizeNullableString($validated['quick_filter'] ?? null),
                'focus_mode' => (bool) ($validated['focus_mode'] ?? false),
                'date_from' => $this->normalizeNullableString($validated['date_from'] ?? null),
                'date_to' => $this->normalizeNullableString($validated['date_to'] ?? null),
                'tab' => in_array(($validated['tab'] ?? 'tasks'), ['tasks', 'bugs'], true) ? ($validated['tab'] ?? 'tasks') : 'tasks',
            ],
            'presets' => $preferences['employee_work']['presets'] ?? [],
        ];

        if (($validated['save_as_preset'] ?? false) && !empty($validated['preset_name'])) {
            $name = trim((string) $validated['preset_name']);
            $existing = collect($preferences['employee_work']['presets'] ?? []);

            $baseKey = Str::slug($name);
            if ($baseKey === '') {
                $baseKey = 'preset';
            }

            $key = $baseKey;
            $suffix = 2;
            while ($existing->contains(fn ($preset) => ($preset['key'] ?? null) === $key && ($preset['name'] ?? '') !== $name)) {
                $key = $baseKey . '-' . $suffix;
                $suffix++;
            }

            $currentFilters = $preferences['employee_work']['filters'] ?? [];
            $upserted = false;
            $presets = $existing->map(function ($preset) use ($key, $name, $currentFilters, &$upserted) {
                if (($preset['key'] ?? null) === $key || (($preset['name'] ?? '') === $name && !$upserted)) {
                    $upserted = true;
                    return [
                        'key' => $key,
                        'name' => $name,
                        'filters' => $currentFilters,
                        'updated_at' => now()->toISOString(),
                    ];
                }

                return $preset;
            });

            if (!$upserted) {
                $presets->push([
                    'key' => $key,
                    'name' => $name,
                    'filters' => $currentFilters,
                    'updated_at' => now()->toISOString(),
                ]);
            }

            $preferences['employee_work']['presets'] = $presets->values()->all();
        }

        $user->preferences = $preferences;
        $user->save();

        return response()->json([
            'message' => (($validated['save_as_preset'] ?? false) && !empty($validated['preset_name']))
                ? 'Default and preset saved.'
                : 'My Work defaults saved.',
            'defaults' => $preferences['employee_work']['filters'],
            'presets' => $this->userWorkPresets($user->fresh()),
        ]);
    }

    public function clearDefaults(Request $request): JsonResponse
    {
        $user = $request->user();
        $preferences = $user->preferences ?? [];
        $presetKey = trim((string) $request->query('preset_key', ''));

        if ($presetKey !== '') {
            $existingPresets = collect($preferences['employee_work']['presets'] ?? []);
            $preferences['employee_work']['presets'] = $existingPresets
                ->reject(fn ($preset) => ($preset['key'] ?? null) === $presetKey)
                ->values()
                ->all();
        } else {
            unset($preferences['employee_work']);
        }

        $user->preferences = $preferences;
        $user->save();

        return response()->json([
            'message' => $presetKey !== '' ? 'Preset deleted.' : 'My Work defaults cleared.',
            'presets' => $this->userWorkPresets($user->fresh()),
        ]);
    }

    public function savePreset(Request $request): JsonResponse
    {
        $request->merge(['save_as_preset' => true, 'preset_name' => $request->input('name')]);
        return $this->saveDefaults($request);
    }

    public function deletePreset(Request $request, string $presetKey): JsonResponse
    {
        $request->query->set('preset_key', $presetKey);
        return $this->clearDefaults($request);
    }

    private function applyTaskAssignmentScope(Builder $query, User $user): void
    {
        $employeeId = $this->resolveEmployeeId($user);

        $query->where(function (Builder $q) use ($user) {
            $q->whereHas('assignments', function (Builder $a) use ($user) {
                $a->where('assignee_type', User::class)
                    ->where('assignee_id', $user->id);
            });
        })->when($employeeId, function (Builder $q) use ($employeeId) {
            $q->orWhereHas('assignees', function (Builder $a) use ($employeeId) {
                $a->where('employees.id', $employeeId);
            })->orWhereHas('assignments', function (Builder $a) use ($employeeId) {
                $a->where('assignee_type', Employee::class)
                    ->where('assignee_id', $employeeId);
            });
        });
    }

    private function assignmentPlannedHoursForRange(WorkAssignment $assignment, ?Carbon $rangeStart, ?Carbon $rangeEnd, Carbon $today): float
    {
        $hoursPerDay = (float) ($assignment->allocated_hours ?? 0);
        if ($hoursPerDay <= 0) {
            return 0.0;
        }

        $assignmentStart = $assignment->start_date ? Carbon::parse($assignment->start_date)->startOfDay() : null;
        $assignmentEnd = $assignment->end_date ? Carbon::parse($assignment->end_date)->endOfDay() : null;

        $effectiveRangeStart = $rangeStart ? $rangeStart->copy()->startOfDay() : $today->copy()->startOfDay();
        $effectiveRangeEnd = $rangeEnd ? $rangeEnd->copy()->endOfDay() : $today->copy()->endOfDay();

        if ($assignmentEnd && $assignmentEnd->lt($effectiveRangeStart)) {
            return 0.0;
        }

        if ($assignmentStart && $assignmentStart->gt($effectiveRangeEnd)) {
            return 0.0;
        }

        return round($hoursPerDay, 2);
    }

    private function buildOps360Snapshot(User $user, ?int $employeeId, $projectIds): array
    {
        $today = Carbon::today();
        $canViewEmployee360 = $this->userHasAnyRole($user, ['Super Admin', 'Admin', 'HR Manager', 'HR Admin', 'Manager', 'Team Manager', 'Team Lead']);
        $canViewDevOpsGlobal = $this->userHasAnyRole($user, ['Super Admin', 'Admin']);
        $canViewProjectDevOps = $this->userHasAnyRole($user, ['Super Admin', 'Admin', 'HR Manager', 'HR Admin', 'Manager', 'Team Manager', 'Team Lead']);

        $projects = Project::query()
            ->whereIn('id', $projectIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        $projectDevOpsLinks = $projects
            ->map(fn (Project $project) => [
                'id' => $project->id,
                'name' => $project->name,
                'url' => route('projects.devops.index', ['project' => $project->id]),
            ])
            ->take(8)
            ->values();

        $employeeTrend = collect();
        $employeeAttendanceScore = 0;
        $employeeProductivityScore = 0;
        $employeeComplianceFlags = [];
        $employeeFilledDays30 = 0;
        $employeeFilledToday = 0;

        $managedTeamIds = $user->managedTeams()->pluck('id');
        if ($managedTeamIds->isEmpty() && !empty($user->team_id)) {
            $managedTeamIds = collect([(int) $user->team_id]);
        }

        $teamUserIds = User::query()
            ->whereIn('team_id', $managedTeamIds)
            ->pluck('id');

        $directEmployeeIds = Employee::query()
            ->where('reporting_to', $user->id)
            ->pluck('id');

        $teamEmployeeIds = Employee::query()
            ->whereIn('user_id', $teamUserIds)
            ->pluck('id');

        $allEmployeeIds = $directEmployeeIds
            ->merge($teamEmployeeIds)
            ->when($employeeId, fn ($ids) => $ids->push($employeeId))
            ->filter()
            ->unique()
            ->values();

        $allUserIds = $teamUserIds
            ->merge(Employee::query()->whereIn('id', $directEmployeeIds)->whereNotNull('user_id')->pluck('user_id'))
            ->push($user->id)
            ->filter()
            ->unique()
            ->values();

        if ($employeeId) {
            $windowStart = $today->copy()->subDays(29);

            $filledDays30 = Timesheet::query()
                ->where('employee_id', $employeeId)
                ->whereBetween('date', [$windowStart->toDateString(), $today->toDateString()])
                ->distinct('date')
                ->count('date');

            $filledLast7 = Timesheet::query()
                ->where('employee_id', $employeeId)
                ->whereBetween('date', [$today->copy()->subDays(6)->toDateString(), $today->toDateString()])
                ->distinct('date')
                ->count('date');

            $missingLast7 = max(0, 7 - $filledLast7);
            $employeeFilledToday = Timesheet::query()
                ->where('employee_id', $employeeId)
                ->whereDate('date', $today->toDateString())
                ->exists() ? 1 : 0;

            $taskScope = Task::query()->where(function (Builder $query) use ($user) {
                $this->applyTaskAssignmentScope($query, $user);
            });

            $taskTotal = (clone $taskScope)->count();
            $taskDone = (clone $taskScope)
                ->whereHas('stage', function (Builder $q) {
                    $q->whereIn('type', ['done', 'completed', 'closed'])
                        ->orWhere('name', 'like', '%done%')
                        ->orWhere('name', 'like', '%complete%')
                        ->orWhere('name', 'like', '%closed%');
                })
                ->count();

            $taskOverdue = (clone $taskScope)
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', $today->toDateString())
                ->count();

            $employeeAttendanceScore = (int) round(($filledDays30 / 30) * 100);
            $employeeProductivityScore = $taskTotal > 0 ? (int) round(($taskDone / $taskTotal) * 100) : 0;
            $employeeFilledDays30 = (int) $filledDays30;

            if ($missingLast7 > 0) {
                $employeeComplianceFlags[] = "Timesheet missing {$missingLast7} day(s) in last 7";
            }
            if ($taskOverdue > 0) {
                $employeeComplianceFlags[] = "{$taskOverdue} overdue task(s) need closure";
            }
            if ($employeeProductivityScore < 50) {
                $employeeComplianceFlags[] = 'Productivity score below 50%';
            }

            $weeklyStart = $today->copy()->subWeeks(7)->startOfWeek(Carbon::MONDAY);
            $weeklyBuckets = Timesheet::query()
                ->where('employee_id', $employeeId)
                ->whereBetween('date', [$weeklyStart->toDateString(), $today->toDateString()])
                ->selectRaw('YEARWEEK(date, 1) as week_key, SUM(hours_spent) as total_hours')
                ->groupBy('week_key')
                ->pluck('total_hours', 'week_key');

            $employeeTrend = collect(range(0, 7))
                ->map(function (int $offset) use ($weeklyStart, $weeklyBuckets) {
                    $weekStart = $weeklyStart->copy()->addWeeks($offset);
                    $weekKey = (int) ($weekStart->isoWeekYear . str_pad((string) $weekStart->isoWeek, 2, '0', STR_PAD_LEFT));

                    return [
                        'label' => $weekStart->format('d M'),
                        'hours' => round((float) ($weeklyBuckets[$weekKey] ?? 0), 2),
                    ];
                })
                ->values();
        }

        $totalTeams = (int) Team::query()->whereIn('id', $managedTeamIds)->count();
        $totalMembers = (int) $allEmployeeIds->count();
        $totalProjects = (int) $projects->count();

        $teamTaskScope = Task::query();
        if ($allEmployeeIds->isNotEmpty()) {
            $teamTaskScope->whereHas('assignees', function (Builder $q) use ($allEmployeeIds) {
                $q->whereIn('employees.id', $allEmployeeIds);
            });
        } else {
            $teamTaskScope->where(function (Builder $query) use ($user) {
                $this->applyTaskAssignmentScope($query, $user);
            });
        }

        $totalTasks = (clone $teamTaskScope)->count();
        $completedTasks = (clone $teamTaskScope)
            ->where(function (Builder $q) {
                $q->where('status', 'completed')
                    ->orWhereHas('stage', function (Builder $stageQ) {
                        $stageQ->whereIn('type', ['done', 'completed', 'closed'])
                            ->orWhere('name', 'like', '%done%')
                            ->orWhere('name', 'like', '%complete%')
                            ->orWhere('name', 'like', '%closed%');
                    });
            })
            ->count();

        $offTrackTasks = (clone $teamTaskScope)
            ->where(function (Builder $q) use ($today) {
                $q->where(function (Builder $dq) use ($today) {
                    $dq->whereNotNull('due_date')
                        ->whereDate('due_date', '<', $today->toDateString());
                })->orWhereNotNull('blocked_by_task_id');
            })
            ->where(function (Builder $q) {
                $q->whereNull('status')
                    ->orWhere('status', '!=', 'completed');
            })
            ->count();

        $teamBugScope = BugTicket::query()
            ->where('assignee_type', Employee::class)
            ->whereIn('assignee_id', $allEmployeeIds);

        $bugsTotalCount = $allEmployeeIds->isNotEmpty()
            ? (int) (clone $teamBugScope)->count()
            : 0;
        $bugsOpenTotal = $allEmployeeIds->isNotEmpty()
            ? (int) (clone $teamBugScope)->whereNull('resolved_at')->count()
            : 0;
        $bugsClosedToday = $allEmployeeIds->isNotEmpty()
            ? (int) (clone $teamBugScope)->whereDate('resolved_at', $today->toDateString())->count()
            : 0;
        $bugsPendingTotal = $allEmployeeIds->isNotEmpty()
            ? (int) (clone $teamBugScope)->whereNull('resolved_at')->whereNull('started_at')->count()
            : 0;

        $presentToday = $allEmployeeIds->isNotEmpty()
            ? AttendanceLog::query()
                ->whereIn('employee_id', $allEmployeeIds)
                ->whereDate('date', $today->toDateString())
                ->whereIn('status', ['Present', 'present'])
                ->count()
            : 0;

        $timesheetFilledToday = $allEmployeeIds->isNotEmpty()
            ? Timesheet::query()
                ->whereIn('employee_id', $allEmployeeIds)
                ->whereDate('date', $today->toDateString())
                ->distinct('employee_id')
                ->count('employee_id')
            : $employeeFilledToday;

        $leaveAppliedToday = $allEmployeeIds->isNotEmpty()
            ? LeaveRequest::query()
                ->whereIn('employee_id', $allEmployeeIds)
                ->whereDate('created_at', $today->toDateString())
                ->count()
            : 0;

        $wfhAppliedToday = $allEmployeeIds->isNotEmpty()
            ? WfhRequest::query()
                ->whereIn('employee_id', $allEmployeeIds)
                ->whereDate('created_at', $today->toDateString())
                ->count()
            : 0;

        $hasApprovalsTable = Schema::hasTable('approvals');
        $hasWorkflowInstancesTable = Schema::hasTable('workflow_instances');

        if ($allUserIds->isEmpty()) {
            $approvalsRaisedToday = 0;
            $approvalsPending = 0;
            $approvalsChecked = 0;
        } elseif ($hasApprovalsTable) {
            $approvalsRaisedToday = (int) Approval::query()
                ->whereIn('requester_id', $allUserIds)
                ->whereDate('created_at', $today->toDateString())
                ->count();

            $approvalsPending = (int) Approval::query()
                ->whereIn('requester_id', $allUserIds)
                ->where('status', 'pending')
                ->count();

            $approvalsChecked = (int) Approval::query()
                ->whereIn('requester_id', $allUserIds)
                ->whereIn('status', ['approved', 'rejected'])
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

        $completionPct = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 100;
        $presencePct = $totalMembers > 0 ? ($presentToday / $totalMembers) * 100 : 100;
        $offTrackPct = $totalTasks > 0 ? ($offTrackTasks / $totalTasks) * 100 : 0;

        $trackStatus = 'On Track';
        if ($completionPct < 65 || $presencePct < 70 || $offTrackPct > 35) {
            $trackStatus = 'Off Track';
        } elseif ($completionPct < 80 || $presencePct < 85 || $offTrackPct > 20) {
            $trackStatus = 'At Risk';
        }

        $last7Start = $today->copy()->subDays(6);

        $presentByDay = $allEmployeeIds->isNotEmpty()
            ? AttendanceLog::query()
                ->whereIn('employee_id', $allEmployeeIds)
                ->whereBetween('date', [$last7Start->toDateString(), $today->toDateString()])
                ->whereIn('status', ['Present', 'present'])
                ->selectRaw('DATE(date) as day_key, COUNT(DISTINCT employee_id) as total')
                ->groupBy(DB::raw('DATE(date)'))
                ->pluck('total', 'day_key')
            : collect();

        $timesheetByDay = $allEmployeeIds->isNotEmpty()
            ? Timesheet::query()
                ->whereIn('employee_id', $allEmployeeIds)
                ->whereBetween('date', [$last7Start->toDateString(), $today->toDateString()])
                ->selectRaw('DATE(date) as day_key, COUNT(DISTINCT employee_id) as total')
                ->groupBy(DB::raw('DATE(date)'))
                ->pluck('total', 'day_key')
            : collect();

        if ($allUserIds->isEmpty()) {
            $approvalsByDay = collect();
        } elseif ($hasApprovalsTable) {
            $approvalsByDay = Approval::query()
                ->whereIn('requester_id', $allUserIds)
                ->whereBetween('created_at', [$last7Start->toDateString(), $today->toDateString()])
                ->selectRaw('DATE(created_at) as day_key, COUNT(*) as total')
                ->groupBy(DB::raw('DATE(created_at)'))
                ->pluck('total', 'day_key');
        } elseif ($hasWorkflowInstancesTable) {
            $approvalsByDay = WorkflowInstance::query()
                ->whereIn('initiator_id', $allUserIds)
                ->whereBetween('created_at', [$last7Start->toDateString(), $today->toDateString()])
                ->selectRaw('DATE(created_at) as day_key, COUNT(*) as total')
                ->groupBy(DB::raw('DATE(created_at)'))
                ->pluck('total', 'day_key');
        } else {
            $approvalsByDay = collect();
        }

        $taskDoneByDay = (clone $teamTaskScope)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$last7Start->toDateString(), $today->toDateString()])
            ->selectRaw('DATE(updated_at) as day_key, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(updated_at)'))
            ->pluck('total', 'day_key');

        $leaveByDay = $allEmployeeIds->isNotEmpty()
            ? LeaveRequest::query()
                ->whereIn('employee_id', $allEmployeeIds)
                ->whereBetween('created_at', [$last7Start->toDateString(), $today->toDateString()])
                ->selectRaw('DATE(created_at) as day_key, COUNT(*) as total')
                ->groupBy(DB::raw('DATE(created_at)'))
                ->pluck('total', 'day_key')
            : collect();

        $wfhByDay = $allEmployeeIds->isNotEmpty()
            ? WfhRequest::query()
                ->whereIn('employee_id', $allEmployeeIds)
                ->whereBetween('created_at', [$last7Start->toDateString(), $today->toDateString()])
                ->selectRaw('DATE(created_at) as day_key, COUNT(*) as total')
                ->groupBy(DB::raw('DATE(created_at)'))
                ->pluck('total', 'day_key')
            : collect();

        $dailyOpsSeries = collect(range(0, 6))
            ->map(function (int $offset) use ($last7Start, $presentByDay, $timesheetByDay, $taskDoneByDay, $approvalsByDay, $leaveByDay, $wfhByDay) {
                $day = $last7Start->copy()->addDays($offset);
                $key = $day->toDateString();

                return [
                    'date' => $key,
                    'label' => $day->format('D'),
                    'present' => (int) ($presentByDay[$key] ?? 0),
                    'timesheet_filled' => (int) ($timesheetByDay[$key] ?? 0),
                    'tasks_completed' => (int) ($taskDoneByDay[$key] ?? 0),
                    'approvals_raised' => (int) ($approvalsByDay[$key] ?? 0),
                    'leave_applied' => (int) ($leaveByDay[$key] ?? 0),
                    'wfh_applied' => (int) ($wfhByDay[$key] ?? 0),
                ];
            })
            ->values();

        $repoIds = GitRepository::query()
            ->whereIn('project_id', $projectIds)
            ->pluck('id');

        $devopsThroughput = 0.0;
        $devopsReviewLag = 0.0;
        $devopsDeploymentRisk = 'Low';
        $devopsOpenPrs = 0;
        $devopsTrend = collect();

        if ($repoIds->isNotEmpty()) {
            $since30 = now()->subDays(30);
            $mergedPrs = GitPullRequest::query()
                ->whereIn('git_repository_id', $repoIds)
                ->where('state', 'merged')
                ->where('merged_at', '>=', $since30)
                ->count();

            $openPrs = GitPullRequest::query()
                ->whereIn('git_repository_id', $repoIds)
                ->where('state', 'open')
                ->count();

            $staleOpen = GitPullRequest::query()
                ->whereIn('git_repository_id', $repoIds)
                ->where('state', 'open')
                ->where('created_at_provider', '<=', now()->subDays(7))
                ->count();

            $failurePrs = GitPullRequest::query()
                ->whereIn('git_repository_id', $repoIds)
                ->where('state', 'merged')
                ->where('merged_at', '>=', $since30)
                ->where(function (Builder $q) {
                    $q->where('title', 'like', '%fix%')
                        ->orWhere('title', 'like', '%bug%')
                        ->orWhere('title', 'like', '%revert%');
                })
                ->count();

            $reviewLagValues = GitPrReview::query()
                ->whereHas('pullRequest', function (Builder $q) use ($repoIds) {
                    $q->whereIn('git_repository_id', $repoIds)
                        ->whereNotNull('created_at_provider');
                })
                ->whereNotNull('submitted_at')
                ->with(['pullRequest:id,created_at_provider'])
                ->get()
                ->map(function (GitPrReview $review) {
                    $createdAt = $review->pullRequest?->created_at_provider;
                    if (!$createdAt || !$review->submitted_at) {
                        return null;
                    }
                    return $createdAt->diffInHours($review->submitted_at);
                })
                ->filter();

            $staleRatio = $openPrs > 0 ? ($staleOpen / $openPrs) * 100 : 0;
            $failureRatio = $mergedPrs > 0 ? ($failurePrs / $mergedPrs) * 100 : 0;
            $riskScore = round(($staleRatio * 0.6) + ($failureRatio * 0.4), 1);

            if ($riskScore >= 40) {
                $devopsDeploymentRisk = 'High';
            } elseif ($riskScore >= 20) {
                $devopsDeploymentRisk = 'Medium';
            }

            $devopsThroughput = round($mergedPrs / 4, 2);
            $devopsReviewLag = round((float) ($reviewLagValues->avg() ?? 0), 2);
            $devopsOpenPrs = $openPrs;

            $weeklyStart = now()->copy()->subWeeks(7)->startOfWeek(Carbon::MONDAY);
            $mergedWeekly = GitPullRequest::query()
                ->whereIn('git_repository_id', $repoIds)
                ->where('state', 'merged')
                ->whereBetween('merged_at', [$weeklyStart->toDateString(), now()->toDateString()])
                ->selectRaw('YEARWEEK(merged_at, 1) as week_key, COUNT(*) as merged_count')
                ->groupBy('week_key')
                ->pluck('merged_count', 'week_key');

            $devopsTrend = collect(range(0, 7))
                ->map(function (int $offset) use ($weeklyStart, $mergedWeekly) {
                    $weekStart = $weeklyStart->copy()->addWeeks($offset);
                    $weekKey = (int) ($weekStart->isoWeekYear . str_pad((string) $weekStart->isoWeek, 2, '0', STR_PAD_LEFT));

                    return [
                        'label' => $weekStart->format('d M'),
                        'value' => (int) ($mergedWeekly[$weekKey] ?? 0),
                    ];
                })
                ->values();
        }

        return [
            'launchers' => [
                'can_view_employee360' => $canViewEmployee360,
                'can_view_devops_global' => $canViewDevOpsGlobal,
                'can_view_project_devops' => $canViewProjectDevOps,
                'employee360_url' => route('hr.employee-360.index'),
                'devops_global_url' => ($canViewDevOpsGlobal && Route::has('devops.dashboard')) ? route('devops.dashboard') : null,
                'project_devops' => $canViewProjectDevOps ? $projectDevOpsLinks : collect(),
                'ops360_url' => route('employee.work.ops360'),
            ],
            'cards' => [
                'employee360' => [
                    'attendance_score' => $employeeAttendanceScore,
                    'productivity_score' => $employeeProductivityScore,
                    'compliance_flags' => $employeeComplianceFlags,
                    'filled_days_30' => $employeeFilledDays30,
                ],
                'squad_ops' => [
                    'pr_throughput' => $devopsThroughput,
                    'review_lag_hours' => $devopsReviewLag,
                    'deployment_risk' => $devopsDeploymentRisk,
                    'open_prs' => $devopsOpenPrs,
                    'repos_linked' => $repoIds->count(),
                ],
            ],
            'insights' => [
                'employee360_trend' => $employeeTrend,
                'devops_pulse_trend' => $devopsTrend,
            ],
            'overview' => [
                'total_projects' => $totalProjects,
                'total_teams' => $totalTeams,
                'total_members' => $totalMembers,
                'approvals_raised_today' => $approvalsRaisedToday,
                'approvals_pending' => $approvalsPending,
                'approvals_checked' => $approvalsChecked,
                'approvals_unchecked' => $approvalsUnchecked,
                'timesheet_filled_today' => $timesheetFilledToday,
                'tasks_completed' => $completedTasks,
                'tasks_total' => $totalTasks,
                'not_working_as_planned' => $offTrackTasks,
                'present_today' => $presentToday,
                'leave_applied_today' => $leaveAppliedToday,
                'wfh_applied_today' => $wfhAppliedToday,
                'bugs_total_count' => $bugsTotalCount,
                'bugs_open_total' => $bugsOpenTotal,
                'bugs_closed_today' => $bugsClosedToday,
                'bugs_pending_total' => $bugsPendingTotal,
                'track_status' => $trackStatus,
                'completion_pct' => round($completionPct, 1),
                'presence_pct' => round($presencePct, 1),
                'offtrack_pct' => round($offTrackPct, 1),
            ],
            'graphs' => [
                'daily_ops' => $dailyOpsSeries,
            ],
        ];
    }

    private function userHasAnyRole(User $user, array $roles): bool
    {
        return method_exists($user, 'hasRole') ? (bool) $user->hasRole($roles) : false;
    }

    private function applyBugAssignmentScope(Builder $query, User $user): void
    {
        $employeeId = $this->resolveEmployeeId($user);

        $query->where(function (Builder $q) use ($user, $employeeId) {
            $q->where(function (Builder $primary) use ($user) {
                $primary->where('assignee_type', User::class)
                    ->where('assignee_id', $user->id);
            })->orWhereHas('assignees', function (Builder $multi) use ($user) {
                $multi->where('assignee_type', User::class)
                    ->where('assignee_id', $user->id);
            });

            if ($employeeId) {
                $q->orWhere(function (Builder $primary) use ($employeeId) {
                    $primary->where('assignee_type', Employee::class)
                        ->where('assignee_id', $employeeId);
                })->orWhereHas('assignees', function (Builder $multi) use ($employeeId) {
                    $multi->where('assignee_type', Employee::class)
                        ->where('assignee_id', $employeeId);
                });
            }
        });
    }

    private function resolveEmployeeId(User $user): ?int
    {
        if ($user->relationLoaded('employee') && $user->employee) {
            return (int) $user->employee->id;
        }

        if ($user->employee) {
            return (int) $user->employee->id;
        }

        if ($user->employee_id) {
            $employee = Employee::query()->where('employee_code', $user->employee_id)->first();
            if ($employee) {
                return (int) $employee->id;
            }
        }

        return null;
    }

    private function taskChecklistColumnExists(string $column): bool
    {
        if ($this->taskChecklistColumns === null) {
            $this->taskChecklistColumns = Schema::getColumnListing('task_checklists');
        }

        return in_array($column, $this->taskChecklistColumns, true);
    }

    private function userWorkDefaults(User $user): array
    {
        $preferences = $user->preferences ?? [];
        $defaults = $preferences['employee_work']['filters'] ?? [];

        return [
            'q' => trim((string) ($defaults['q'] ?? '')),
            'project_id' => $this->normalizeNullableInt($defaults['project_id'] ?? null),
            'priority' => $this->normalizeNullableString($defaults['priority'] ?? null),
            'task_status' => $this->normalizeNullableString($defaults['task_status'] ?? null),
            'bug_stage' => $this->normalizeNullableString($defaults['bug_stage'] ?? null),
            'quick_filter' => $this->normalizeNullableString($defaults['quick_filter'] ?? null),
            'focus_mode' => (bool) ($defaults['focus_mode'] ?? false),
            'date_from' => $this->normalizeNullableString($defaults['date_from'] ?? null),
            'date_to' => $this->normalizeNullableString($defaults['date_to'] ?? null),
            'tab' => in_array(($defaults['tab'] ?? 'tasks'), ['tasks', 'bugs'], true) ? ($defaults['tab'] ?? 'tasks') : 'tasks',
        ];
    }

    private function userWorkPresets(User $user): array
    {
        $preferences = $user->preferences ?? [];

        return collect($preferences['employee_work']['presets'] ?? [])
            ->filter(fn ($preset) => !empty($preset['key']) && !empty($preset['name']) && is_array($preset['filters'] ?? null))
            ->map(function ($preset) {
                return [
                    'key' => (string) $preset['key'],
                    'name' => (string) $preset['name'],
                    'filters' => [
                        'q' => trim((string) ($preset['filters']['q'] ?? '')),
                        'project_id' => $this->normalizeNullableInt($preset['filters']['project_id'] ?? null),
                        'priority' => $this->normalizeNullableString($preset['filters']['priority'] ?? null),
                        'task_status' => $this->normalizeNullableString($preset['filters']['task_status'] ?? null),
                        'bug_stage' => $this->normalizeNullableString($preset['filters']['bug_stage'] ?? null),
                        'quick_filter' => $this->normalizeNullableString($preset['filters']['quick_filter'] ?? null),
                        'focus_mode' => (bool) ($preset['filters']['focus_mode'] ?? false),
                        'date_from' => $this->normalizeNullableString($preset['filters']['date_from'] ?? null),
                        'date_to' => $this->normalizeNullableString($preset['filters']['date_to'] ?? null),
                        'tab' => in_array(($preset['filters']['tab'] ?? 'tasks'), ['tasks', 'bugs'], true) ? ($preset['filters']['tab'] ?? 'tasks') : 'tasks',
                    ],
                    'updated_at' => $preset['updated_at'] ?? null,
                ];
            })
            ->values()
            ->all();
    }

    private function effectiveFilter(Request $request, array $defaults, string $key, mixed $fallback = null): mixed
    {
        if ($request->has($key)) {
            return $request->input($key);
        }

        return $defaults[$key] ?? $fallback;
    }

    private function normalizeNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);
        return $normalized === '' ? null : $normalized;
    }

    private function normalizeBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    private function normalizeDateBoundary(?string $value, string $boundary): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            $date = Carbon::parse($value);
            return $boundary === 'end' ? $date->endOfDay() : $date->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function buildRangeLabel(?Carbon $rangeStart, ?Carbon $rangeEnd): string
    {
        if (!$rangeStart && !$rangeEnd) {
            return 'All Dates';
        }

        if ($rangeStart && $rangeEnd) {
            return $rangeStart->format('d M Y') . ' - ' . $rangeEnd->format('d M Y');
        }

        if ($rangeStart) {
            return 'From ' . $rangeStart->format('d M Y');
        }

        return 'Until ' . $rangeEnd?->format('d M Y');
    }

    private function isTaskDone(Task $task, int $doneChecklist, int $totalChecklist): bool
    {
        $stageType = strtolower((string) ($task->stage?->type ?? ''));
        if (in_array($stageType, ['done', 'completed', 'closed', 'resolved'], true)) {
            return true;
        }

        return $totalChecklist > 0 && $doneChecklist >= $totalChecklist;
    }

    private function isBugDone(BugTicket $bug): bool
    {
        $stage = strtolower((string) ($bug->stage?->name ?? ''));
        return in_array($stage, ['done', 'completed', 'closed', 'resolved', 'fixed'], true);
    }

    private function applyTaskQuickFilter(Builder $query, string $quickFilter, Carbon $today, ?int $employeeId, bool $hasChecklistWorkDate, bool $hasChecklistCompletedAt): void
    {
        switch ($quickFilter) {
            case 'overdue':
                $query->whereDate('due_date', '<', $today);
                break;

            case 'high_priority':
                $query->whereIn('priority', ['Critical', 'critical', 'High', 'high', 'Urgent', 'urgent', 'P0', 'P1']);
                break;

            case 'no_progress_today':
                $query->where(function (Builder $q) use ($today, $employeeId, $hasChecklistWorkDate) {
                    if ($employeeId) {
                        $q->whereDoesntHave('timesheets', function (Builder $timesheet) use ($today, $employeeId) {
                            $timesheet->where('employee_id', $employeeId)
                                ->whereDate('date', $today);
                        });
                    }

                    if ($hasChecklistWorkDate) {
                        $q->whereDoesntHave('checklists', function (Builder $checklist) use ($today) {
                            $checklist->whereDate('work_date', $today);
                        });
                    }
                });
                break;

            case 'done_today':
                if ($hasChecklistCompletedAt) {
                    $query->whereHas('checklists', function (Builder $checklist) use ($today) {
                        $checklist->where('is_completed', true)
                            ->whereDate('completed_at', $today);
                    });
                }
                break;
        }
    }
}
