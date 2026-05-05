<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\Timesheet;
use App\Models\WorkAssignment;
use App\Services\Attendance\WorkingDayResolverService;
use App\Services\Infrastructure\LoggerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProjectManagement\TaskAnalyticsExport;

class ProjectAnalyticsController extends Controller
{
    protected $logger;
    protected $workingDayResolver;

    public function __construct(LoggerService $logger, WorkingDayResolverService $workingDayResolver)
    {
        $this->logger = $logger;
        $this->workingDayResolver = $workingDayResolver;
    }

    /**
     * Display Task Analytics Page.
     */
    public function show(Request $request, Project $project, Task $task)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : now()->startOfWeek();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : $startDate->copy()->endOfWeek();

        $data = $this->aggregateData($project, $task, $startDate, $endDate);
        
        return Inertia::render('Project/Task/Analytics', [
            'project' => $project,
            'task' => $task->load(['stage', 'module', 'creator']),
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'analytics' => $data
        ]);
    }

    /**
     * Export Task Analytics to Excel.
     */
    public function export(Request $request, Project $project, Task $task)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : $task->start_date;
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : $task->due_date;

        $startDate = $startDate ?? now()->startOfWeek();
        $endDate = $endDate ?? now()->endOfWeek();

        $data = $this->aggregateData($project, $task, $startDate, $endDate);
        $fileName = "Task_Performance_{$task->id}_" . now()->format('YmdHis') . ".xlsx";
        
        return Excel::download(new TaskAnalyticsExport($project, $task, $data), $fileName);
    }

    /**
     * Aggregate all necessary data for task analytics.
     */
    private function aggregateData(Project $project, Task $task, Carbon $startDate, Carbon $endDate)
    {
        // 1. OVERALL LIFECYCLE (Task Total)
        $overallApproved = Timesheet::where('task_id', $task->id)->whereIn('status', ['Approved', 'approved'])->sum('hours_spent');
        $overallPending = Timesheet::where('task_id', $task->id)->whereIn('status', ['Pending', 'pending', 'Submitted', 'submitted'])->sum('hours_spent');
        $overallPlanned = WorkAssignment::where('task_id', $task->id)->sum('allocated_hours');
        
        $lifecycleDays = 0;
        if ($task->start_date && $task->due_date) {
            $curr = $task->start_date->copy();
            while ($curr->lte($task->due_date)) {
                if (!$this->workingDayResolver->isNonWorkingDay($curr)) $lifecycleDays++;
                $curr->addDay();
            }
        }

        // --- PERIODIC (FILTERED) DATA ---
        $periodicApproved = Timesheet::where('task_id', $task->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereIn('status', ['Approved', 'approved'])->sum('hours_spent');

        $periodicPlanned = 0;
        $assignments = WorkAssignment::where('task_id', $task->id)->get();
        
        $periodicContributors = $assignments->groupBy(function($a) {
            return $a->assignee_type === \App\Models\Employee::class ? $a->assignee->user_id : $a->assignee_id;
        })->map(function($userAssignments, $userId) use ($task, $startDate, $endDate) {
            $first = $userAssignments->first();
            $user = ($first->assignee_type === \App\Models\Employee::class) ? $first->assignee->user : $first->assignee;
            if (!$user) return null;

            $pPlanned = 0;
            foreach ($userAssignments as $pa) {
                if (is_array($pa->daily_allocations)) {
                    foreach ($pa->daily_allocations as $date => $hours) {
                        $d = Carbon::parse($date);
                        if ($d->between($startDate, $endDate)) $pPlanned += (float)$hours;
                    }
                }
            }

            $pActual = Timesheet::where('task_id', $task->id)
                ->whereHas('employee', fn($q) => $q->where('user_id', $userId))
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('status', ['Approved', 'approved'])->sum('hours_spent');

            if ($pPlanned == 0 && $pActual == 0) return null;

            return [
                'id' => $user->id, 'name' => $user->name, 'avatar' => $user->profile_photo_url,
                'planned' => round($pPlanned, 2), 'actual' => round($pActual, 2),
                'deviation' => round($pActual - $pPlanned, 2)
            ];
        })->filter()->values();

        foreach ($assignments as $pa) {
            if (is_array($pa->daily_allocations)) {
                foreach ($pa->daily_allocations as $date => $hours) {
                    $d = Carbon::parse($date);
                    if ($d->between($startDate, $endDate)) $periodicPlanned += (float)$hours;
                }
            }
        }

        // 3. CONTRIBUTORS (OVERALL)
        $contributors = $assignments->groupBy(function($a) {
            return $a->assignee_type === \App\Models\Employee::class ? $a->assignee->user_id : $a->assignee_id;
        })->map(function($userAssignments, $userId) use ($task) {
            $first = $userAssignments->first();
            $user = ($first->assignee_type === \App\Models\Employee::class) ? $first->assignee->user : $first->assignee;
            if (!$user) return null;

            $planned = $userAssignments->sum('allocated_hours');
            $actual = Timesheet::where('task_id', $task->id)
                ->whereHas('employee', fn($q) => $q->where('user_id', $userId))
                ->whereIn('status', ['Approved', 'approved'])->sum('hours_spent');

            return [
                'id' => $user->id, 'name' => $user->name, 'avatar' => $user->profile_photo_url,
                'planned' => round($planned, 2), 'actual' => round($actual, 2),
                'remaining' => max(0, round($planned - $actual, 2)),
                'over_consumption' => $actual > $planned ? round($actual - $planned, 2) : 0,
            ];
        })->filter()->values();

        // 4. OTHER DATA
        $checklists = $task->checklists()->get();
        $extensions = $task->extensions()->with('creator')->get();
        $activities = $task->activities()->with('user')->orderBy('created_at', 'desc')->get();

        return [
            'overall' => [
                'planned' => (float)$overallPlanned,
                'actual' => (float)$overallApproved,
                'pending' => (float)$overallPending,
                'days' => $lifecycleDays,
                'scrum_points' => (int)$task->scrum_points,
                'drift' => [
                    'hours' => round($task->current_effort_hours - $task->baseline_effort_hours, 2),
                    'days' => round($task->current_duration_days - $task->baseline_duration_days, 2),
                ]
            ],
            'periodic' => [
                'planned' => (float)$periodicPlanned,
                'actual' => (float)$periodicApproved,
                'contributors' => $periodicContributors,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'meta' => [
                'comments_count' => $task->comments()->count(),
                'git_branch_url' => $task->git_branch_url,
                'git_pr_url' => $task->git_pr_url,
                'estimated_hours' => (float)$task->estimated_hours,
            ],
            'contributors' => $contributors,
            'checklists' => $checklists,
            'extensions' => $extensions,
            'activities' => $activities,
        ];
    }

    /**
     * Display Project Analytics Page (Weekly Report).
     */
    public function projectAnalytics(Request $request, Project $project)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : now()->startOfWeek();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : $startDate->copy()->endOfWeek();

        $data = $this->aggregateProjectData($project, $startDate, $endDate);

        return Inertia::render('Project/ProjectAnalytics', [
            'project' => $project,
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'analytics' => $data
        ]);
    }

    /**
     * Export Project Weekly Report to Excel.
     */
    public function exportProjectWeekly(Request $request, Project $project)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : now()->startOfWeek();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : $startDate->copy()->endOfWeek();

        $data = $this->aggregateProjectData($project, $startDate, $endDate);
        $fileName = "Project_Weekly_Report_{$project->id}_" . now()->format('YmdHis') . ".xlsx";
        
        return Excel::download(new \App\Exports\ProjectManagement\ProjectWeeklyExport($project, $startDate, $endDate, $data), $fileName);
    }

    private function aggregateProjectData(Project $project, Carbon $startDate, Carbon $endDate)
    {
        // 1. Get all contributors who worked or were planned for this project in the range
        $userIds = collect();

        // From WorkAssignments (Planned)
        $plannedAssignments = WorkAssignment::where('project_id', $project->id)
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate]);
            })->get();

        foreach ($plannedAssignments as $pa) {
            $userIds->push($pa->assignee_type === \App\Models\Employee::class ? $pa->assignee->user_id : $pa->assignee_id);
        }

        // From Timesheets (Actual)
        $actualTimesheets = Timesheet::whereHas('task', fn($q) => $q->where('project_id', $project->id))
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with('employee.user')
            ->get();

        foreach ($actualTimesheets as $ts) {
            if ($ts->employee?->user_id) {
                $userIds->push($ts->employee->user_id);
            }
        }

        $uniqueUserIds = $userIds->unique()->filter();

        // 2. Aggregate per user
        $report = $uniqueUserIds->map(function($userId) use ($project, $startDate, $endDate, $plannedAssignments, $actualTimesheets) {
            $user = \App\Models\User::find($userId);
            if (!$user) return null;

            // Calculate Planned Hours from Matrix (daily_allocations)
            $planned = 0;
            $userPlanned = $plannedAssignments->filter(function($pa) use ($userId) {
                $id = $pa->assignee_type === \App\Models\Employee::class ? $pa->assignee->user_id : $pa->assignee_id;
                return (int)$id === (int)$userId;
            });

            foreach ($userPlanned as $pa) {
                if (is_array($pa->daily_allocations)) {
                    foreach ($pa->daily_allocations as $date => $hours) {
                        $d = Carbon::parse($date);
                        if ($d->between($startDate, $endDate)) {
                            $planned += (float)$hours;
                        }
                    }
                } else {
                    // Fallback to average if no daily breakdown
                    $totalDays = $pa->start_date->diffInDays($pa->end_date) + 1;
                    $avg = $pa->allocated_hours / $totalDays;
                    $curr = $startDate->copy();
                    while ($curr->lte($endDate)) {
                        if ($curr->between($pa->start_date, $pa->end_date)) {
                            $planned += $avg;
                        }
                        $curr->addDay();
                    }
                }
            }

            // Calculate Actual Hours
            $actual = $actualTimesheets->filter(function($ts) use ($userId) {
                return (int)$ts->employee?->user_id === (int)$userId;
            })->sum('hours_spent');

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->profile_photo_url,
                'planned' => round($planned, 2),
                'actual' => round($actual, 2),
                'deviation' => round($actual - $planned, 2),
                'status' => $actual > $planned ? 'Over' : ($actual < $planned ? 'Under' : 'Exact')
            ];
        })->filter()->values();

        return [
            'report' => $report,
            'totals' => [
                'planned' => $report->sum('planned'),
                'actual' => $report->sum('actual'),
                'deviation' => $report->sum('deviation'),
            ]
        ];
    }
}
