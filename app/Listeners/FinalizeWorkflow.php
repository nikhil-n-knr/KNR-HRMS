<?php

namespace App\Listeners;

use App\Events\ApprovalActionTaken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class FinalizeWorkflow implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ApprovalActionTaken $event): void
    {
        $step = $event->step;
        $approval = $step->approval;
        
        // Refresh to get latest status
        $approval->refresh();

        $target = $approval->approvable; // e.g. LeaveRequest

        if ($approval->status === 'approved') {
            // Workflow Complete: Mark Target as Approved
            if (method_exists($target, 'markAsApproved')) {
                $target->markAsApproved();
            } else {
                // Fallback standard column
                $target->update(['status' => 'approved']);
            }
            Log::info("Finalized Target: " . get_class($target) . " #{$target->id} -> Approved");
        } elseif ($approval->status === 'rejected') {
             // Workflow Rejected
             if (method_exists($target, 'markAsRejected')) {
                $target->markAsRejected();
            } else {
                $target->update(['status' => 'rejected']);
            }
            Log::info("Finalized Target: " . get_class($target) . " #{$target->id} -> Rejected");
        }
    }
}
