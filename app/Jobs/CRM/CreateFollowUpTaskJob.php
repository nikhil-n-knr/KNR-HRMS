<?php

namespace App\Jobs\CRM;

use App\Models\CRM\EmailMessage;
use App\Models\CRM\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateFollowUpTaskJob implements ShouldQueue
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
        $cutoff = now()->subHours(24);

        // Find outbound messages sent > 24h ago that aren't opened
        \App\Models\CRM\EmailMessage::where('direction', 'outbound')
            ->where('created_at', '<', $cutoff)
            ->where('status', 'sent') // Status might be 'sent' but not 'opened'
            ->whereDoesntHave('stats', function ($q) {
                $q->where('event_type', 'open');
            })
            ->chunk(100, function ($messages) {
                foreach ($messages as $message) {
                    $contact = $message->thread->trackable;
                    if ($contact instanceof \App\Models\CRM\Contact) {
                        \App\Models\CRM\Activity::create([
                            'tenant_id' => $message->thread->tenant_id,
                            'assigned_to' => $message->thread->account->user_id ?? 1,
                            'subject' => 'Follow up: ' . $message->thread->subject,
                            'description' => "Email sent to {$contact->first_name} was not opened within 24 hours. Automated follow-up required.",
                            'due_date' => now()->addDay(),
                            'type' => 'call',
                            'priority' => 'high',
                            'activityable_type' => \App\Models\CRM\Contact::class,
                            'activityable_id' => $contact->id,
                            'created_by' => 1, // System
                        ]);
                        
                        // Mark as followed up to avoid duplicate tasks
                        $message->update(['status' => 'follow_up_created']);
                    }
                }
            });
    }
}
