<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Models\WorkAssignment;
use App\Models\Timesheet;
use App\Models\ProjectExtension;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProjectPerformanceController extends Controller
{
    /**
     * Fetch detailed performance metrics for a project.
     */
    public function getMetrics(Project $project)
    {
        $project->load([
            'tasks.assignees',
            'tasks.assignments',
            'tasks.timesheets' => fn ($query) => $query->whereIn('status', ['Approved', 'approved']),
        ]);

        $tasks = $project->tasks;
        $extensions = ProjectExtension::where('project_id', $project->id)->get();

        $estimatedHours = (float) $tasks->sum(fn ($task) => (float) ($task->total_efforts ?: $task->estimated_hours ?: 0));
        $allocatedHours = (float) WorkAssignment::where('project_id', $project->id)->sum('allocated_hours');
        $actualHours = (float) Timesheet::where('project_id', $project->id)
            ->whereIn('status', ['Approved', 'approved'])
            ->sum('hours_spent');
        $totalExtendedHours = (float) $extensions->sum('hours_added');
        $totalExtendedDays = (int) $extensions->sum('days_added');

        $completedOnTime = 0;
        $completedAhead = 0;
        $overdue = 0;
        $efficiencyRatios = [];

        $taskPerformance = $tasks->map(function ($task) use (&$completedOnTime, &$completedAhead, &$overdue, &$efficiencyRatios, $extensions) {
            $plannedHours = (float) ($task->total_efforts ?: $task->estimated_hours ?: 0);
            $allocatedHours = (float) $task->assignments->sum('allocated_hours');
            $actualHours = (float) $task->timesheets->sum('hours_spent');
            $extensionHours = (float) $extensions->where('task_id', $task->id)->sum('hours_added');
            $lastEntryDate = $task->timesheets->max('date');
            $calendarDays = null;

            if ($task->start_date && ($lastEntryDate || $task->due_date)) {
                $calendarDays = max(
                    1,
                    \Carbon\Carbon::parse($task->start_date)->diffInDays(\Carbon\Carbon::parse($lastEntryDate ?: $task->due_date)) + 1
                );
            }

            $efficiencyRatio = $actualHours > 0 ? round($plannedHours / $actualHours, 2) : null;
            if ($efficiencyRatio !== null) {
                $efficiencyRatios[] = $efficiencyRatio;
            }

            $varianceDays = null;
            $statusLabel = 'In Flight';

            if ($task->due_date && $lastEntryDate) {
                $varianceDays = \Carbon\Carbon::parse($task->due_date)
                    ->diffInDays(\Carbon\Carbon::parse($lastEntryDate), false);

                if ($varianceDays > 0) {
                    $statusLabel = 'Late';
                } elseif ($varianceDays < 0) {
                    $statusLabel = 'Ahead';
                } else {
                    $statusLabel = 'On Time';
                }
            } elseif ($task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast() && !in_array(strtolower((string) $task->status), ['done', 'completed', 'testing'], true)) {
                $varianceDays = \Carbon\Carbon::parse($task->due_date)->diffInDays(now());
                $statusLabel = 'Overdue';
            }

            if ($statusLabel === 'On Time') {
                $completedOnTime++;
            } elseif ($statusLabel === 'Ahead') {
                $completedAhead++;
            } elseif ($statusLabel === 'Overdue' || $statusLabel === 'Late') {
                $overdue++;
            }

            return [
                'id' => $task->id,
                'title' => $task->title,
                'assignees' => $task->assignees
                    ->map(fn ($assignee) => trim(($assignee->first_name ?? '') . ' ' . ($assignee->last_name ?? '')))
                    ->filter()
                    ->values(),
                'planned_hours' => round($plannedHours, 2),
                'allocated_hours' => round($allocatedHours, 2),
                'actual_hours' => round($actualHours, 2),
                'deviation_hours' => round($actualHours - $plannedHours, 2),
                'efficiency_ratio' => $efficiencyRatio,
                'status_label' => $statusLabel,
                'completion_variance_days' => $varianceDays,
                'extension_hours' => round($extensionHours, 2),
                'calendar_days' => $calendarDays,
                'burn_rate' => $calendarDays ? round($actualHours / $calendarDays, 2) : 0,
                'last_entry_date' => $lastEntryDate ? \Carbon\Carbon::parse($lastEntryDate)->format('Y-m-d') : null,
            ];
        })->values();

        $taskCount = max(1, $tasks->count());
        $projectCalendarDays = null;
        if ($project->start_date && ($project->deadline || now())) {
            $projectCalendarDays = max(
                1,
                \Carbon\Carbon::parse($project->start_date)->diffInDays(\Carbon\Carbon::parse($project->deadline ?: now())) + 1
            );
        }

        $fastestPerformers = $taskPerformance
            ->filter(fn ($task) => $task['status_label'] === 'Ahead')
            ->pluck('assignees')
            ->flatten()
            ->countBy()
            ->map(fn ($count, $name) => ['name' => $name, 'score' => $count])
            ->values();

        $slowestPerformers = $taskPerformance
            ->filter(fn ($task) => in_array($task['status_label'], ['Late', 'Overdue'], true))
            ->pluck('assignees')
            ->flatten()
            ->countBy()
            ->map(fn ($count, $name) => ['name' => $name, 'score' => $count])
            ->values();

        return response()->json([
            'summary' => [
                'estimated' => round($estimatedHours, 2),
                'allocated' => round($allocatedHours, 2),
                'actual' => round($actualHours, 2),
                'extended_hours' => round($totalExtendedHours, 2),
                'extended_days' => $totalExtendedDays,
                'avg_efficiency_ratio' => round(collect($efficiencyRatios)->avg() ?? 0, 2),
                'tasks_completed_on_time_pct' => round(($completedOnTime / $taskCount) * 100, 1),
                'tasks_completed_ahead_pct' => round(($completedAhead / $taskCount) * 100, 1),
                'tasks_overdue_pct' => round(($overdue / $taskCount) * 100, 1),
            ],
            'averages' => [
                'planned_hours_per_task' => round($estimatedHours / $taskCount, 2),
                'actual_hours_per_task' => round($actualHours / $taskCount, 2),
                'avg_deviation_hours' => round($taskPerformance->avg('deviation_hours') ?? 0, 2),
                'burn_rate' => $projectCalendarDays ? round($actualHours / $projectCalendarDays, 2) : 0,
            ],
            'extensions' => $extensions,
            'performance' => [
                'fastest' => $fastestPerformers,
                'slowest' => $slowestPerformers,
                'tasks' => $taskPerformance,
            ],
        ]);
    }
}
