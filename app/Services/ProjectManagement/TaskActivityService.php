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

    /**
     * Log field updates with old/new values.
     */
    public function logUpdate(Task $task, array $oldValues, array $newValues): ?TaskActivity
    {
        $changes = [];
        $monitored = ['title', 'status', 'priority', 'due_date', 'estimated_hours', 'scrum_points', 'is_locked'];

        foreach ($monitored as $field) {
            if (isset($oldValues[$field]) && isset($newValues[$field]) && $oldValues[$field] != $newValues[$field]) {
                $changes[$field] = [
                    'old' => $oldValues[$field],
                    'new' => $newValues[$field]
                ];
            }
        }

        if (empty($changes)) return null;

        return $this->log($task, 'update', ['changes' => $changes]);
    }
}
