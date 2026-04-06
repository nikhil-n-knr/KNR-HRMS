<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\ApprovalStep;
use App\Models\Workflow;
use App\Models\WorkflowStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalService
{
    /**
     * Initiate a workflow for a given entity.
     */
    public function initiate(Model $entity, User $requester, string $event)
    {
        $workflow = Workflow::where('trigger_event', $event)->where('is_active', true)->first();

        if (!$workflow) {
            return null;
        }

        return DB::transaction(function () use ($workflow, $entity, $requester) {
            // Create Approval Instance
            $approval = Approval::create([
                'workflow_id' => $workflow->id,
                'approvable_type' => get_class($entity),
                'approvable_id' => $entity->id,
                'requester_id' => $requester->id,
                'status' => 'pending',
                'current_stage_order' => 1
            ]);

            // Create First Step
            $this->createStepForStage($approval, 1);

            // Dispatch Event
            \App\Events\WorkflowInitiated::dispatch($approval);

            return $approval;
        });
    }

    /**
     * Process an approval action on a step.
     */
    public function approve(ApprovalStep $step, User $actor, ?string $comments = null)
    {
        if ($step->status !== 'pending') {
            throw ValidationException::withMessages(['step' => 'This step is already processed.']);
        }
        
        return DB::transaction(function () use ($step, $actor, $comments) {
            $step->update([
                'status' => 'approved',
                'actioned_at' => now(),
                'comments' => $comments,
                'approver_id' => $actor->id 
            ]);

            // Dispatch Action Taken Event (before moving to next stage, capturing the action)
            \App\Events\ApprovalActionTaken::dispatch($step);

            // Check for next stage
            $approval = $step->approval;
            $nextOrder = $approval->current_stage_order + 1;
            
            $nextStageExists = $approval->workflow->stages()->where('order', $nextOrder)->exists();

            if ($nextStageExists) {
                $approval->update(['current_stage_order' => $nextOrder]);
                $this->createStepForStage($approval, $nextOrder);
            } else {
                // Workflow Complete
                $approval->update(['status' => 'approved']);
            }

            return $approval;
        });
    }

    public function reject(ApprovalStep $step, User $actor, ?string $comments = null)
    {
         if ($step->status !== 'pending') {
            throw ValidationException::withMessages(['step' => 'This step is already processed.']);
        }

        return DB::transaction(function () use ($step, $actor, $comments) {
            $step->update([
                'status' => 'rejected',
                'actioned_at' => now(),
                'comments' => $comments,
                'approver_id' => $actor->id
            ]);

            $step->approval->update(['status' => 'rejected']);
            
            \App\Events\ApprovalActionTaken::dispatch($step);

            return $step->approval;
        });
    }

    protected function createStepForStage(Approval $approval, int $order)
    {
        $stage = $approval->workflow->stages()->where('order', $order)->first();
        
        if (!$stage) return; // Should not happen if logic flows correctly

        $approverId = $this->resolveApprover($stage, $approval->requester);

        ApprovalStep::create([
            'approval_id' => $approval->id,
            'stage_id' => $stage->id,
            'approver_id' => $approverId, // Can be null if using Role-based general pool (advanced)
            'status' => 'pending'
        ]);
    }

    protected function resolveApprover(WorkflowStage $stage, User $requester)
    {
        if ($stage->approver_type === 'specific_user') {
            return $stage->user_id;
        }

        if ($stage->approver_type === 'manager') {
            // Requester's Manager
            return $requester->manager_id;
        }

        if ($stage->approver_type === 'team_lead') {
            // Requester's Team Manager
            return $requester->team?->manager_id;
        }
        
        if ($stage->approver_type === 'role') {
            // Return null to allow any user with role to pick it up (Pool Mechanism)
            // The SendApprovalNotification listener will handle notifying the pool.
            return null;
        }

        return null; // System Admin Fallback?
    }

    /**
     * Get pending approvals for a specific user.
     */
    public function getPendingApprovals(User $user)
    {
        return ApprovalStep::where('approver_id', $user->id)
            ->where('status', 'pending')
            ->with(['approval.requester', 'approval.approvable'])
            ->latest()
            ->get();
    }

    /**
     * Get requests initiated by the user.
     */
    public function getMyRequests(User $user)
    {
        return Approval::where('requester_id', $user->id)
            ->with(['currentStep.approver', 'workflow'])
            ->latest()
            ->get();
    }
}
