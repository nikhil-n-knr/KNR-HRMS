<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessWorkflowTimeouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-workflow-timeouts';

    protected $description = 'Process workflow approvals that have exceeded their timeout duration';

    public function handle()
    {
        $this->info('Starting workflow timeout processing...');

        // Find stages with auto-approve configured
        // We need approval records that are 'pending'
        // Join with workflow_instances -> current_stage -> has auto_approve_after_hours
        
        // Better: Query pending approvals where the related stage has a timeout
        $pendingApprovals = \App\Models\WorkflowApproval::where('status', 'pending')
            ->whereHas('stage', function($q) {
                $q->whereNotNull('auto_approve_after_hours')
                  ->where('auto_approve_after_hours', '>', 0);
            })
            ->with(['stage', 'workflowInstance'])
            ->get();

        $count = 0;

        foreach ($pendingApprovals as $approval) {
            $hours = $approval->stage->auto_approve_after_hours;
            $deadline = $approval->created_at->addHours($hours);

            if (now()->greaterThan($deadline)) {
                $this->info("Auto-approving approval #{$approval->id} (Expired at {$deadline})");
                
                try {
                    app(\App\Services\WorkflowService::class)->autoApprove(
                        $approval, 
                        "Auto-approved by system after {$hours} hours inactivity."
                    );
                    $count++;
                } catch (\Exception $e) {
                    $this->error("Failed to auto-approve #{$approval->id}: " . $e->getMessage());
                    \Log::error("Auto-approval failed", ['approval_id' => $approval->id, 'error' => $e->getMessage()]);
                }
            }
        }

        $this->info("Processed {$count} timed-out approvals.");
    }
}
