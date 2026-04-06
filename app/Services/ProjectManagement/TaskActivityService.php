<?php

namespace App\Services\ProjectManagement;

use App\Models\Task;
use App\Models\TaskActivity;

class TaskActivityService
{
    /**
     * Log a task activity.
     *
     * @param Task $task
     * @param string $type
     * @param array|null $details
     * @param int|null $userId
     * @return TaskActivity
     */
    public function log(Task $task, string $type, ?array $details = null, ?int $userId = null): TaskActivity
    {
        return $task->activities()->create([
            'user_id' => $userId ?? auth()->id(),
            'type' => $type,
            'details' => $details
        ]);
    }

    /**
     * Log a status change (move).
     */
    public function logMove(Task $task, string $fromStage, string $toStage): TaskActivity
    {
        return $this->log($task, 'move', [
            'from' => $fromStage,
            'to' => $toStage
        ]);
    }
}
