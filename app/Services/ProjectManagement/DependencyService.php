<?php

namespace App\Services\ProjectManagement;

use App\Models\Task;
use Illuminate\Validation\ValidationException;

class DependencyService
{
    /**
     * Check for circular dependencies before assigning a blocker.
     * Logic: If Task A wants to be blocked by Task B, ensure B (or B's blockers) are not blocked by A.
     * 
     * @param int $taskId The task being updated
     * @param int $blockerId The task that will block it
     * @throws ValidationException
     */
    public function validateDependency($taskId, $blockerId)
    {
        if ($taskId == $blockerId) {
            throw ValidationException::withMessages(['blocked_by_task_id' => 'A task cannot block itself.']);
        }

        // Traverse up the chain from Blocker
        $currentBlocker = Task::find($blockerId);
        
        // Depth limit to prevent infinite loops during traversal if data is already corrupt
        $depth = 0;
        $maxDepth = 20;

        while ($currentBlocker && $currentBlocker->blocked_by_task_id && $depth < $maxDepth) {
            if ($currentBlocker->blocked_by_task_id == $taskId) {
                throw ValidationException::withMessages([
                    'blocked_by_task_id' => "Circular dependency detected! Task #{$taskId} is already blocking the dependency chain of Task #{$blockerId}."
                ]);
            }
            
            $currentBlocker = Task::find($currentBlocker->blocked_by_task_id);
            $depth++;
        }
    }
}
