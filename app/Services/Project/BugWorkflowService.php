<?php

namespace App\Services\Project;

use App\Models\BugTicket;
use App\Models\WorkflowStage;
use App\Models\BugTicketTransition;
use App\Models\User;
use App\Models\Role;
use App\Notifications\BugStageChangedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\Infrastructure\LoggerService;

class BugWorkflowService
{
    /**
     * Transition a bug ticket to a new stage.
     */
    public function transition(BugTicket $bug, WorkflowStage $newStage, ?string $note = null, ?array $additionalData = [])
    {
        return DB::transaction(function () use ($bug, $newStage, $note, $additionalData) {
            $oldStageId = $bug->workflow_stage_id;
            $oldStageName = $bug->stage->name ?? 'Initial';
            
            // 1. Update Bug Ticket
            $updateData = [
                'workflow_stage_id' => $newStage->id,
                'is_client_visible' => $newStage->is_client_visible ?? $bug->is_client_visible,
            ];

            // Metrics Logic (Phase 10)
            if (!$bug->started_at && stripos($newStage->name, 'Progress') !== false) {
                $updateData['started_at'] = now();
            }
            if ($newStage->is_final && !$bug->resolved_at) {
                $updateData['resolved_at'] = now();
            }

            // Optional: Handle Bulk Assignee Updates if passed in additionalData
            if (isset($additionalData['assignee_id'])) {
                $updateData['assignee_id'] = $additionalData['assignee_id'];
                $updateData['assignee_type'] = \App\Models\Employee::class;
            }

            $bug->update($updateData);

            // 2. Log Transition (Pizza Tracker)
            BugTicketTransition::create([
                'bug_ticket_id' => $bug->id,
                'from_stage_id' => $oldStageId,
                'to_stage_id' => $newStage->id,
                'actor_id' => Auth::id(),
                'actor_type' => User::class
            ]);

            // 3. Log System Activity
            $this->logActivity($bug, 'p_change', "Stage advanced to {$newStage->name}");

            if ($note) {
                $bug->comments()->create([
                    'user_id' => Auth::id(),
                    'body' => "<strong>Transition Note:</strong> " . $note,
                    'is_public' => false
                ]);
            }

            // 4. Fire Notifications
            $this->notifyPersonnel($bug, $oldStageName, $newStage);

            return $bug;
        });
    }

    /**
     * Notify internal personnel and optionally the client.
     */
    protected function notifyPersonnel(BugTicket $bug, string $oldStageName, WorkflowStage $newStage)
    {
        $personnel = $this->resolvePersonnel($newStage);
        $newStageName = $newStage->name;

        // Internal Notifications
        foreach ($personnel as $user) {
            if ($user->id !== Auth::id()) {
                $user->notify(new BugStageChangedNotification($bug, $oldStageName, $newStageName));
            }
        }

        // Client Notification (If enabled for this stage)
        if ($newStage->notify_client && $bug->reporter && !($bug->reporter instanceof \App\Models\Employee)) {
            // Check if reporter is a Client User (reporter_type is likely ClientUser)
            $bug->reporter->notify(new BugStageChangedNotification($bug, $oldStageName, $newStageName));
        }
    }

    /**
     * Resolve stage personnel (Roles + Specific Users).
     */
    protected function resolvePersonnel(WorkflowStage $stage)
    {
        $users = collect();

        // Check modern personnel_config first
        if (!empty($stage->stage_personnel)) {
            foreach ($stage->stage_personnel as $item) {
                if ($item['type'] === 'user') {
                    if ($user = User::find($item['id'])) $users->push($user);
                } elseif ($item['type'] === 'role') {
                    $roleUsers = User::whereHas('roles', function($q) use ($item) {
                        $q->where('roles.id', $item['id']);
                    })->get();
                    $users = $users->concat($roleUsers);
                }
            }
        }

        // Fallback/Legacy Logic (Existing fields)
        if ($stage->user_id) {
            if ($user = User::find($stage->user_id)) $users->push($user);
        }
        if ($stage->role_id) {
            $roleUsers = User::whereHas('roles', function($q) use ($stage) {
                $q->where('roles.id', $stage->role_id);
            })->get();
            $users = $users->concat($roleUsers);
        }

        return $users->unique('id');
    }

    protected function logActivity(BugTicket $bug, $type, $description)
    {
        if (method_exists($bug, 'activities')) {
            $bug->activities()->create([
                'actor_id' => Auth::id(),
                'actor_type' => User::class,
                'activity_type' => $type,
                'description' => $description,
            ]);
        }
    }
}
