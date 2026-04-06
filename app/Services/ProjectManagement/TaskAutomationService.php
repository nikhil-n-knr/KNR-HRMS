<?php

namespace App\Services\ProjectManagement;

use App\Models\GitPullRequest;
use App\Models\ProjectStage;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class TaskAutomationService
{
    /**
     * Link Git PR to Task and Move Stage if rules apply.
     */
    public function handlePrUpdate(GitPullRequest $pr)
    {
        // 1. Identify Task from PR Status (Regex: Fixes #123, Task-123)
        $taskId = $this->extractTaskId($pr->title) ?? $this->extractTaskId($pr->external_id); // Often branches named task-123
        
        if (!$taskId) return;

        $task = Task::find($taskId);
        if (!$task) return;

        // 2. Link Task to PR (If not already)
        // Check pivot
        if (!$task->git_pr_url) {
            $task->update([
                'git_pr_url' => '#', // Placeholder or real URL if we had it
                'git_branch_url' => '#' 
            ]);
            // Also update pivot if we have many-to-many
        }

        // 3. Automation Rules
        // State: open -> Move to "In Review"
        // State: merged -> Move to "QA Ready" or "Done"
        
        $targetStageName = null;

        if ($pr->state === 'open') {
            $targetStageName = 'In Review';
        } elseif ($pr->state === 'merged' || $pr->merged_at) {
            $targetStageName = 'QA Ready'; // or 'Done'
        }

        if ($targetStageName) {
            $this->moveTaskToStage($task, $targetStageName);
        }
    }

    protected function extractTaskId($string)
    {
        // Patterns: #123, Task-123, TASK-123
        if (preg_match('/#(\d+)/', $string, $matches)) {
            return $matches[1];
        }
        if (preg_match('/Task-(\d+)/i', $string, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function moveTaskToStage(Task $task, $stageName)
    {
        // Find stage in THIS project
        $stage = ProjectStage::where('project_id', $task->project_id)
            ->where('name', 'LIKE', "%{$stageName}%")
            ->first();

        if ($stage && $task->stage_id !== $stage->id) {
            $oldStage = $task->stage->name ?? 'Unknown';
            $task->update(['stage_id' => $stage->id]);
            
            // Log Activity
            Log::info("Automation: Moved Task #{$task->id} from {$oldStage} to {$stage->name} via PR Action.");
            
            // Optional: Create Task Activity entry
            // TaskActivity::create(...)
        }
    }
}
