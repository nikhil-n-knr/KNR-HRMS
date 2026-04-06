<?php

namespace App\Listeners;

use App\Events\WorkflowInitiated;
use App\Events\ApprovalActionTaken;
use App\Notifications\ApprovalRequested; // Newly created
use App\Services\Infrastructure\LoggerService; // Using the mandated Logger
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendApprovalNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        try {
            if ($event instanceof WorkflowInitiated) {
                $this->notifyNextApprover($event->approval);
            } elseif ($event instanceof ApprovalActionTaken) {
                $this->handleActionTaken($event->step);
            }
        } catch (\Exception $e) {
            $this->logger->logError('Notification Dispatch Failed', [
                'event' => class_basename($event),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function notifyNextApprover($approval)
    {
        $this->logger->logInfo('Notifying Next Approver', ['approval_id' => $approval->id]);

        // Get pending steps
        $pendingSteps = $approval->steps()
            ->where('status', 'pending')
            ->with(['approver', 'stage']) // Eager load stage for role-based
            ->get();

        foreach ($pendingSteps as $step) {
            if ($step->approver) {
                // Specific Approver Assigned
                $this->logger->logInfo("Sending Notification to Specific User", [
                    'approver_id' => $step->approver->id,
                    'step_id' => $step->id
                ]);
                $step->approver->notify(new ApprovalRequested($approval, $step));
            } elseif ($step->stage && $step->stage->approver_type === 'role') {
                // Role-Based Pool (No specific user yet)
                $roleId = $step->stage->role_id;
                
                $potentialApprovers = \App\Models\User::whereHas('roles', function($q) use ($roleId) {
                    $q->where('id', $roleId);
                })->get();

                $this->logger->logInfo("Sending Notification to Role Pool", [
                    'role_id' => $roleId,
                    'count' => $potentialApprovers->count(),
                    'step_id' => $step->id
                ]);

                foreach ($potentialApprovers as $user) {
                    $user->notify(new ApprovalRequested($approval, $step));
                }
            }
        }
    }

    protected function handleActionTaken($step)
    {
        $approval = $step->approval;
        $requester = $approval->requester;

        // 1. Log the action
        $this->logger->logInfo("Approval Action Processed", [
            'step_id' => $step->id,
            'approver' => $step->approver_id,
            'status' => $step->status
        ]);

        // 2. Notify Requester (TODO: Create ApprovalStatusNotification for requester)
        // For now, we focus on the chain.

        // 3. If approved, check if workflow moves to next stage
        $approval->refresh(); 
        
        if ($approval->status === 'pending') {
            // New steps might have been created for the NEXT stage
            $this->notifyNextApprover($approval);
        }
    }
}
