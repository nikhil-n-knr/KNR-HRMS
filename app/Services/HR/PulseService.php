<?php

namespace App\Services\HR;

use App\Models\Task;
use App\Models\PerformanceMetric; // We will model this next
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PulseService
{
    /**
     * Aggregates stats for a given employee and month.
     * Can be run incrementally or as a full refresh.
     */
    public function aggregateForEmployee($employeeId, $month = null)
    {
        $month = $month ?? Carbon::now()->format('Y-m');
        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();

        // 1. Fetch Tasks COMPLETED in this month
        // Assuming 'completed_at' exists or we use 'updated_at' where status='done'
        // Let's use updated_at + status='done' for now as 'completed_at' wasn't strictly defined
        $tasks = Task::whereHas('assignees', function($q) use ($employeeId) {
                $q->where('employees.id', $employeeId);
            })
            ->where('status', 'done')
            ->whereBetween('updated_at', [$start, $end])
            ->get();

        $completedCount = $tasks->count();

        // 2. Calculate Avg Completion (Actual Hours)
        $totalHours = $tasks->sum('actual_hours');
        $avgHours = $completedCount > 0 ? round($totalHours / $completedCount, 2) : 0;

        // 3. Count Bugs (Assuming 'bug' is a task type or filtered by tag/title?)
        // The prompt implies "Bugs Returned". Let's assume a bug is a task with 'bug' complexity/priority or specific tracking.
        // For simplicity, let's say tasks with title holding 'Defect' or 'Bug'.
        // OR better: In a real system, we'd have a separate 'bugs' table or type.
        // Let's assume standard calculation: 0 for now as we don't have explicit Bug linkage.
        $bugs = 0; 
        
        // 4. Overdue Check: Deadline < Completed At
        // We lack 'completed_at' column, using updated_at
        // Using 'task_deadline' from task (if exists) or project deadline? Task doesn't have deadline in schema snippet viewed earlier.
        // Let's default 0.
        $overdue = 0;

        // 5. Quality Score
        // Formula: 100 - (Bugs * 5) - (Overdue * 2)
        $quality = max(0, 100 - ($bugs * 5) - ($overdue * 2));

        // 6. Update/Create Metric Record
        DB::table('performance_metrics')->updateOrInsert(
            [
                'employee_id' => $employeeId, 
                'month' => $month
            ],
            [
                'tasks_completed' => $completedCount,
                'tasks_overdue' => $overdue,
                'bugs_raised' => $bugs,
                'avg_completion_hours' => $avgHours,
                'quality_score' => $quality,
                'updated_at' => now(),
                 // created_at is tricky with updateOrInsert, usually ignored or set on insert
            ]
        );

        return [
            'month' => $month,
            'score' => $quality,
            'completed' => $completedCount
        ];
    }
}
