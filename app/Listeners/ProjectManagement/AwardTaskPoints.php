<?php

namespace App\Listeners\ProjectManagement;

use App\Events\ProjectManagement\TaskCompleted;
use App\Services\Gamification\GamificationService;
use Carbon\Carbon;

class AwardTaskPoints
{
    protected $gamification;

    /**
     * Create the event listener.
     */
    public function __construct(GamificationService $gamification)
    {
        $this->gamification = $gamification;
    }

    /**
     * Handle the event.
     */
    public function handle(TaskCompleted $event): void
    {
        $task = $event->task;
        $user = $event->user;

        if (!$user->employee) {
            return; // Only Employees get points
        }

        // 1. Base Completion Award (e.g. 10pts)
        // Check if rule exists, otherwise fallback or skip
        $this->gamification->award($user->employee, 'task_completed', "Task:{$task->id}");

        // 2. Deadline Bonus (Early Delivery)
        // If task has deadline/due_date and finished before it.
        // Assuming Project Deadline for now or if we add due_date to Task later.
        $deadline = $task->project->deadline; // Or individual task deadline if exists
        
        if ($deadline && Carbon::now()->lessThan($deadline)) {
             $daysEarly = Carbon::now()->diffInDays($deadline);
             if ($daysEarly >= 1) {
                 $this->gamification->award($user->employee, 'task_early_completion', "Task:{$task->id} ({$daysEarly} days early)");
             }
        }

        // 3. Complexity Bonus (God Mode)
        if ($task->complexity === 'god_mode') {
            $this->gamification->award($user->employee, 'task_god_mode', "create_miracle:Task:{$task->id}");
        }
    }
}
