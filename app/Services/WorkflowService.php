<?php

namespace App\Services;

use App\Models\{Workflow, WorkflowInstance, WorkflowStage, WorkflowApproval, User};
use App\Notifications\ApprovalPending;
use App\Events\{WorkflowApproved, WorkflowRejected, WorkflowCompleted};
use Illuminate\Support\Facades\DB;

class WorkflowService
{
    /**
     * Initialize a new workflow instance for an entity
     */
    public function initializeWorkflow(string $entityType, int $entityId, User $initiator): ?WorkflowInstance
{
        $workflow = Workflow::getActiveForEntity($entityType);
        
        if (!$workflow || $workflow->stages()->count() === 0) {
            return null;
        }

        return DB::transaction(function() use ($workflow, $entityType, $entityId, $initiator) {
            // Create instance
            $instance = WorkflowInstance::create([
                'workflow_id' => $workflow->id,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'initiator_id' => $initiator->id,
                'status' => 'pending',
                'started_at' => now(),
            ]);

            // Start with first stage
            $firstStage = $workflow->stages()->first();
            $instance->update(['current_stage_id' => $firstStage->id]);

            // Create approval records for first stage
            $this->createApprovalsForStage($instance, $firstStage, $initiator);

            return $instance;
        });
    }

    /**
     * Approve an approval request
     */
    public function approve(WorkflowApproval $approval, User $approver, ?string $comments = null): void
    {
        if ($approval->approver_id !== $approver->id) {
            throw new \Exception('Unauthorized approver');
        }

        DB::transaction(function() use ($approval, $approver, $comments) {
            $approval->approve($approver, $comments);

            $instance = $approval->workflowInstance;
            $stage = $approval->stage;

            // Strategy Check
            $shouldMove = false;
            if ($stage->approval_strategy === 'any_can_approve') {
                $shouldMove = true;
                
                // Cancel other pending approvals for this stage
                $instance->approvals()
                    ->where('stage_id', $stage->id)
                    ->where('status', 'pending')
                    ->where('id', '!=', $approval->id)
                    ->update(['status' => 'skipped']);
                    
            } else {
                // Default: All Must Approve
                $currentStageApprovals = $instance->approvals()
                    ->where('stage_id', $instance->current_stage_id)
                    ->get();
                
                if ($currentStageApprovals->every(fn($a) => $a->status === 'approved' || $a->status === 'skipped')) {
                    $shouldMove = true;
                }
            }

            if ($shouldMove) {
                $this->moveToNextStage($instance, $stage);
            }

            // Fire event for notifications
            event(new WorkflowApproved($approval, $approver));
        });
    }

    /**
     * Reject an approval request
     */
    public function reject(WorkflowApproval $approval, User $approver, string $comments): void
    {
        if ($approval->approver_id !== $approver->id) {
            throw new \Exception('Unauthorized approver');
        }

        DB::transaction(function() use ($approval, $approver, $comments) {
            $approval->reject($approver, $comments);

            $instance = $approval->workflowInstance;
            $instance->update([
                'status' => 'rejected',
                'completed_at' => now()
            ]);

            // Auto-Sync Entity Status (Rejection)
            if ($rejectedStatus = $instance->workflow->rejected_status) {
                 $this->syncEntityStatus($instance, $rejectedStatus);
            }

            event(new WorkflowRejected($approval, $approver));
        });
    }

    /**
     * Move workflow instance to next stage
     */
    private function moveToNextStage(WorkflowInstance $instance, ?WorkflowStage $currentStage = null): void
    {
        $currentStage = $currentStage ?? $instance->currentStage;
        $nextStage = $instance->workflow->stages()
            ->where('stage_order', '>', $currentStage->stage_order)
            ->orderBy('stage_order')
            ->first();

        if ($nextStage) {
            // Move to next stage
            $instance->update(['current_stage_id' => $nextStage->id]);
            $this->createApprovalsForStage($instance, $nextStage, $instance->initiator);
        } else {
            // Workflow complete
            $instance->update([
                'status' => 'approved',
                'completed_at' => now()
            ]);

            // Auto-Sync Entity Status (Approval)
            if ($approvedStatus = $instance->workflow->approved_status) {
                $this->syncEntityStatus($instance, $approvedStatus);
            }

            event(new WorkflowCompleted($instance));
        }
    }

    /**
     * Sync status to the related entity
     */
    private function syncEntityStatus(WorkflowInstance $instance, string $status): void
    {
        // Dynamically find model based on entity_type?
        // Or assume entity_type maps to a Model config? 
        // For now, let's try to guess Model class or use a map.
        // Simple convention: 'timesheet' -> \App\Models\Timesheet
        
        $modelClass = $this->getModelClass($instance->entity_type);
        if ($modelClass && class_exists($modelClass)) {
            $entity = $modelClass::find($instance->entity_id);
            if ($entity) {
                // Check if model has 'status' column
                // We'll require models to have a 'status' column or equivalent
                if (\Schema::hasColumn($entity->getTable(), 'status')) {
                    $entity->update(['status' => $status]);
                }
            }
        }
    }

    private function getModelClass(string $entityType): ?string
    {
        // Simple mapping
        $map = [
            'timesheet' => \App\Models\Timesheet::class,
            'leave_request' => \App\Models\LeaveRequest::class,
            'asset_request' => \App\Models\AssetRequest::class,
            'asset_request' => \App\Models\AssetRequest::class,
            'shift_swap' => \App\Models\ShiftSwap::class,
            'shift_swap' => \App\Models\ShiftSwap::class,
            'attendance_regularization' => \App\Models\AttendanceRegularization::class,
            'floating_holiday' => \App\Models\FloatingHolidayRequest::class,
            'overtime' => \App\Models\OvertimeRequest::class,
            'wfh' => \App\Models\WfhRequest::class,
            'payroll' => \App\Models\Payroll::class,
            'expense' => \App\Models\Expense::class,
        ];
        
        return $map[$entityType] ?? null;
    }

    /**
     * Create approval records for a stage
     */
    private function createApprovalsForStage(WorkflowInstance $instance, WorkflowStage $stage, User $employee): void
    {
        $approvers = $stage->resolveAllApprovers($employee);

        // Filter out initiator if self-approval is not allowed
        if (!$stage->allow_self_approval) {
            $approvers = $approvers->reject(function ($approver) use ($instance) {
                 return $approver->id === $instance->initiator_id;
            });
        }

        if ($approvers->isEmpty()) {
            \Log::warning("No approvers found for stage {$stage->name} in workflow {$instance->workflow->name}. Auto-advancing.", [
                'instance_id' => $instance->id,
                'entity_id' => $instance->entity_id,
                'entity_type' => $instance->entity_type
            ]);
            $this->moveToNextStage($instance, $stage);
            return;
        }

        foreach ($approvers as $approver) {
            WorkflowApproval::create([
                'workflow_instance_id' => $instance->id,
                'stage_id' => $stage->id,
                'approver_id' => $approver->id,
                'status' => 'pending'
            ]);

            // Send notification to approver
            try {
                $approver->notify(new ApprovalPending($instance));
            } catch (\Exception $e) {
                // Notification failed but don't block workflow
                \Log::warning('Failed to send approval notification', [
                    'approver_id' => $approver->id,
                    'instance_id' => $instance->id
                ]);
            }
        }
    }

    /**
     * Cancel a workflow instance
     */
    public function cancel(WorkflowInstance $instance, User $user): void
    {
        if ($instance->initiator_id !== $user->id && !$user->hasRole('admin')) {
            throw new \Exception('Unauthorized to cancel workflow');
        }

        $instance->update([
            'status' => 'cancelled',
            'completed_at' => now()
        ]);
    }

    /**
     * Auto-approve an approval request due to timeout
     */
    public function autoApprove(WorkflowApproval $approval, string $reason = 'Auto-approved due to timeout'): void
    {
        // Use a system user or the approver themselves as the actor?
        // Ideally, we log it as system action, but for audit trail, let's use the designated approver 
        // effectively "forcing" their hand, or use a system flag.
        // For simplicity, we'll act as the approver but include the reason in comments.
        
        $approver = User::find($approval->approver_id);
        
        if (!$approver) {
            // Fallback if user deleted
            \Log::error("Approver not found for auto-approval: {$approval->id}");
            return;
        }

        DB::transaction(function() use ($approval, $approver, $reason) {
            $approval->approve($approver, $reason);

            $instance = $approval->workflowInstance;
            $stage = $approval->stage;

            // Strategy Check (reuse logic or refactor? For now, duplicate simpler check)
            $shouldMove = false;
            
            if ($stage->approval_strategy === 'any_can_approve') {
                $shouldMove = true;
                // Cancel others
                $instance->approvals()
                    ->where('stage_id', $stage->id)
                    ->where('status', 'pending')
                    ->where('id', '!=', $approval->id)
                    ->update(['status' => 'skipped']);
            } else {
                 $currentStageApprovals = $instance->approvals()
                    ->where('stage_id', $instance->current_stage_id)
                    ->get();
                
                if ($currentStageApprovals->every(fn($a) => $a->status === 'approved' || $a->status === 'skipped')) {
                    $shouldMove = true;
                }
            }

            if ($shouldMove) {
                $this->moveToNextStage($instance);
            }

            event(new WorkflowApproved($approval, $approver));
        });
    }
}
