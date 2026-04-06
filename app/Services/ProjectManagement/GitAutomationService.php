<?php

namespace App\Services\ProjectManagement;

use App\Models\BugTicket;
use App\Models\Project;
use App\Models\WorkflowStage;
use Illuminate\Support\Facades\Log;

class GitAutomationService
{
    /**
     * Process a Push event.
     * Logic: If commit message contains "fix/bug-{id}", move ticket to "In Progress".
     */
    public function processPush(array $payload, Project $project)
    {
        $commits = $payload['commits'] ?? [];
        foreach ($commits as $commit) {
            $message = $commit['message'] ?? '';
            $this->scanForBugReferences($message, $project, 'push');
        }
    }

    /**
     * Process a Pull Request event.
     * Logic: If PR is merged and references a bug, move to "Ready for QA".
     */
    public function processPullRequest(array $payload, Project $project)
    {
        $action = $payload['action'] ?? '';
        $pr = $payload['pull_request'] ?? [];
        
        if ($action === 'closed' && ($pr['merged'] ?? false)) {
            // PR Merged
            $title = $pr['title'] ?? '';
            $body = $pr['body'] ?? '';
            $branch = $pr['head']['ref'] ?? '';
            
            // Check content
            $this->scanForBugReferences($title . ' ' . $body, $project, 'merge');
            
            // Check branch name (e.g., fix/bug-123)
            $this->scanForBugReferences($branch, $project, 'merge');
        }
    }

    private function scanForBugReferences($text, Project $project, $eventType)
    {
        // Regex to find "bug-123" or "#123"
        // Adjust regex based on convention. Let's support "bug-{id}" and "#{id}"
        preg_match_all('/(bug-|#)(\d+)/i', $text, $matches);
        
        if (!empty($matches[2])) {
            foreach ($matches[2] as $bugId) {
                $this->transitionBug($bugId, $project, $eventType);
            }
        }
    }

    private function transitionBug($bugId, Project $project, $eventType)
    {
        $bug = BugTicket::where('id', $bugId)->where('project_id', $project->id)->first();
        
        if (!$bug) return;

        $targetStageName = '';
        
        if ($eventType === 'push') {
            // Move to "In Progress"
            $targetStageName = 'In Progress';
        } elseif ($eventType === 'merge') {
            // Move to "Ready for QA" or "Resolved"
            $targetStageName = 'Ready for QA'; 
        }

        if ($targetStageName) {
            $stage = WorkflowStage::where('name', $targetStageName)
                ->whereHas('workflow', function($q) {
                     $q->where('entity_type', BugTicket::class);
                })->first();

            // Fallback: If "Ready for QA" doesn't exist, try "Resolved"
            if (!$stage && $targetStageName === 'Ready for QA') {
                $stage = WorkflowStage::where('name', 'Resolved')->first();
            }

            if ($stage && $bug->workflow_stage_id !== $stage->id) {
                $updateData = ['workflow_stage_id' => $stage->id];

                // Metrics: Set Timestamps
                if ($targetStageName === 'In Progress' && !$bug->started_at) {
                    $updateData['started_at'] = now();
                }
                if ($targetStageName === 'Ready for QA' && !$bug->resolved_at) {
                    // Assuming Ready for QA counts as "Code Complete" -> Resolved for Cycle Time
                    $updateData['resolved_at'] = now();
                }

                $bug->update($updateData);
                
                // Log Activity
                $bug->activities()->create([
                    'user_id' => 1, // System User
                    'activity_type' => 'git_automation',
                    'description' => "GitOps: Moved to {$stage->name} via " . ($eventType === 'push' ? 'Commit' : 'PR Merge'),
                ]);
                
                Log::info("GitOps: Moved Bug #{$bug->id} to {$stage->name}");
            }
        }
    }
}
