<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BugReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Find tickets in non-final stages
        $stagnantBugs = \App\Models\BugTicket::whereHas('stage', function($q) {
                // Filter by stages that have a reminder SLA and are not final
                $q->where('is_final', false)
                  ->whereNotNull('reminder_hours');
            })
            ->with(['stage', 'assignee', 'project'])
            ->get();

        foreach ($stagnantBugs as $bug) {
            $hours = $bug->stage->reminder_hours;
            $deadline = $bug->updated_at->addHours($hours);

            if (now()->greaterThan($deadline)) {
                // Send Notification
                // For now, we'll log it or use a Notification class if available.
                // Assuming we have a NotificationService or similar, or just Log for MVP.
                
                \Illuminate\Support\Facades\Log::info("Bug #{$bug->id} '{$bug->subject}' is stagnant. Stage: {$bug->stage->name}. Assigned to: " . ($bug->assignee ? $bug->assignee->id : 'Unassigned'));

                // Future: Send Email/Slack
                // Notification::send($bug->assignee, new StagnantBugNotification($bug));
                
                if ($bug->stage->notify_incharge) {
                   // Notify Project Lead / Manager
                }
            }
        }
    }
}
