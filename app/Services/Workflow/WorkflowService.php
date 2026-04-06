<?php

namespace App\Services\Workflow;

use App\Models\Workflow;
use App\Models\WorkflowStage;
use App\Notifications\ApprovalRequested;
use App\Notifications\RequestProcessed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class WorkflowService
{
    /**
     * Start a workflow for an entity (Expense, Payroll, LeaveRequest, etc.)
     */
    public function initiate(Model $entity)
    {
        // 1. Resolve Workflow
        $workflow = null;

        // Case A: Entity has a 'category' which has a workflow (e.g. Expense)
        if (method_exists($entity, 'category') && $entity->category && $entity->category->workflow) {
            $workflow = $entity->category->workflow;
        } 
        // Case B: Entity has a direct workflow (e.g. Payroll linked to a workflow setting)
        elseif (method_exists($entity, 'workflow') && $entity->workflow) {
            $workflow = $entity->workflow;
        }
        // Case C: Global Default? (Future)

        if (!$workflow) {
             // No workflow = Auto Approve or Default Pending
             // If field exists, set to Pending/Approved
             if (in_array('status', $entity->getFillable())) {
                  $entity->update(['status' => 'Pending', 'current_stage_id' => null]);
             }
             return;
        }

        $firstStage = $workflow->stages()->orderBy('stage_order')->first();

        if ($firstStage) {
            $entity->update([
                'status' => 'Processing',
                'current_stage_id' => $firstStage->id
            ]);
            
            $this->notifyApprover($entity, $firstStage);

        } else {
             // Workflow exists but empty steps? Auto Approve.
             $entity->update(['status' => 'Approved']);
        }
    }

    /**
     * Process an Action (Approve/Reject)
     */
    public function processAction(Model $entity, $action, $user, $remarks = null)
    {
        // 1. Validation: Can this user approve?
        $stage = $entity->currentStage;
        if (!$stage) {
            // Fallback for Admin Override on stuck items
            if ($user->hasRole(['Admin', 'Super Admin'])) {
                 if ($action === 'approve') $entity->update(['status' => 'Approved']);
                 else $entity->update(['status' => 'Rejected', 'rejection_reason' => $remarks]);
                 return true;
            }
            throw new \Exception('No pending workflow stage.');
        }
        
        // Check Approver Rules
        $isAuthorized = false;
        
        if ($user->hasRole(['Admin', 'Super Admin'])) $isAuthorized = true; // Override
        
        else if ($stage->approver_type === 'ROLE') {
             if ($user->hasRole($stage->role->name)) $isAuthorized = true;
        } 
        
        elseif ($stage->approver_type === 'DYNAMIC') {
             if ($stage->dynamic_rule === 'reporting_manager') {
                 // Check if user is manager of entity owner
                 // Assumes entity has 'employee' relation
                 if ($entity->employee && $entity->employee->reporting_manager_id === $user->employee_id) {
                     $isAuthorized = true;
                 }
             }
             elseif ($stage->dynamic_rule === 'department_head') {
                 // Future
             }
        }
        elseif ($stage->approver_type === 'USER') {
            $usersToNotify = $stage->users;
        }
        
        elseif ($stage->approver_type === 'USER') {
            if ($stage->users()->where('users.id', $user->id)->exists()) {
                $isAuthorized = true;
            }
        }
        elseif ($stage->approver_type === 'USER') {
            $usersToNotify = $stage->users;
        }
        
        elseif ($stage->approver_type === 'USER') {
            if ($stage->users()->where('users.id', $user->id)->exists()) {
                $isAuthorized = true;
            }
        }
        
        elseif ($stage->approver_type === 'USER') {
            if ($stage->users()->where('users.id', $user->id)->exists()) {
                $isAuthorized = true;
            }
        }
        
        elseif ($stage->approver_type === 'USER') {
            // Check if user is in the pivot table for this stage
            if ($stage->users()->where('users.id', $user->id)->exists()) {
                $isAuthorized = true;
            }
        }
        
        elseif ($stage->approver_type === 'USER') {
            // Check if user is in the pivot table for this stage
            if ($stage->users()->where('users.id', $user->id)->exists()) {
                $isAuthorized = true;
            }
        }
        
        elseif ($stage->approver_type === 'USER') {
            // Check if user is in the pivot table for this stage
            if ($stage->users()->where('users.id', $user->id)->exists()) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            throw new \Exception('You are not authorized to approve this stage.');
        }
        
        // 2. Process Action
        if ($action === 'reject') {
            $entity->update([
                'status' => 'Rejected',
                'rejection_reason' => $remarks ?? 'Rejected by ' . $user->name,
                'current_stage_id' => null
            ]);

            // Notify Requester of Rejection
            if ($entity->employee && $entity->employee->user) {
                $entity->employee->user->notify(new RequestProcessed($entity, 'Rejected', $remarks));
            }
            return;
        }

        // 3. Move to Next Stage
        $nextStage = WorkflowStage::where('workflow_id', $stage->workflow_id)
            ->where('stage_order', '>', $stage->stage_order)
            ->orderBy('stage_order')
            ->first();

        if ($nextStage) {
            $entity->update([
                'current_stage_id' => $nextStage->id,
                'status' => 'Processing'
            ]);
            
            $this->notifyApprover($entity, $nextStage);
            
        } else {
            // Final Approval
             $entity->update([
                'current_stage_id' => null,
                'status' => 'Approved',
                'approved_by' => $user->id
            ]);
            
            // Notify Requester of Final Approval
            if ($entity->employee && $entity->employee->user) {
                $entity->employee->user->notify(new RequestProcessed($entity, 'Approved'));
            }

            // Trigger Final Hooks (e.g. Schedule Payment) via Events?
        }
    }

    protected function notifyApprover(Model $entity, WorkflowStage $stage)
    {
        // Find users to notify
        $usersToNotify = collect([]);

        if ($stage->approver_type === 'ROLE') {
            // Get users with this role
            // Assuming Spatie Permission
            $usersToNotify = \App\Models\User::role($stage->role->name)->get();
        } 
        elseif ($stage->approver_type === 'DYNAMIC') {
            if ($stage->dynamic_rule === 'reporting_manager') {
                if ($entity->employee && $entity->employee->reportingManager) {
                     $managerUser = $entity->employee->reportingManager->user; // Assuming Employee -> User relation
                     if ($managerUser) $usersToNotify->push($managerUser);
                }
            }
        }
        elseif ($stage->approver_type === 'USER') {
            $usersToNotify = $stage->users;
        }

        if ($usersToNotify->isNotEmpty()) {
            Notification::send($usersToNotify, new ApprovalRequested($entity, $stage));
        }
    }
}
