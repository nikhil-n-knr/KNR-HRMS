<?php

namespace App\Jobs\ProjectManagement;

use App\Models\BugTicket;
use App\Models\WorkflowStage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BugAutoCloseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info("Running Bug Auto-Close Job");

        // Find stages that have auto-close enabled
        $autoCloseStages = WorkflowStage::whereNotNull('auto_close_days')
            ->where('auto_close_days', '>', 0)
            ->get();

        foreach ($autoCloseStages as $stage) {
            // Find bugs in this stage that haven't been updated in X days
            $staleBugs = BugTicket::where('workflow_stage_id', $stage->id)
                ->where('updated_at', '<', now()->subDays($stage->auto_close_days))
                ->get();

            foreach ($staleBugs as $bug) {
                // Determine the next stage (Ideally "Closed" or a specific "Auto-Closed" stage)
                // For this implementation, we'll look for a stage marked as 'is_final'
                $finalStage = WorkflowStage::where('is_final', true)->first();

                if ($finalStage) {
                    $bug->update([
                        'workflow_stage_id' => $finalStage->id,
                        'system_closed_at' => now(),
                        'resolution_summary' => "System Auto-Closed after {$stage->auto_close_days} days of inactivity."
                    ]);
                    
                    Log::info("Auto-closed bug #{$bug->id} due to inactivity.");
                }
            }
        }
    }
}
