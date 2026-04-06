<?php

namespace App\Jobs;

use App\Models\CRM\Meeting;
use App\Models\CRM\Activity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PostMeetingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $meeting;

    /**
     * Create a new job instance.
     */
    public function __construct(Meeting $meeting)
    {
        $this->meeting = $meeting;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $statusLabel = $this->meeting->status === 'no_show' ? '⚠️ NO-SHOW' : 'COMPLETED';
        $prio = $this->meeting->status === 'no_show' ? 'high' : 'medium';

        // 1. Create Activity Log / Task
        Activity::create([
            'tenant_id' => $this->meeting->tenant_id,
            'assigned_to' => $this->meeting->assigned_to,
            'created_by' => $this->meeting->created_by,
            'activityable_type' => $this->meeting->trackable_type,
            'activityable_id' => $this->meeting->trackable_id,
            'type' => 'meeting_summary',
            'subject' => "{$statusLabel}: {$this->meeting->title}",
            'description' => "Post-meeting auto-followup. 
                            Recording: {$this->meeting->conferencing_link} (if available). 
                            Survey link: https://satisfaction.survey/m-{$this->meeting->id}",
            'is_completed' => ($this->meeting->status !== 'no_show'), // Leave open if no-show
            'completed_at' => ($this->meeting->status === 'no_show' ? null : now()),
        ]);

        // 2. Auto-close scheduled meetings
        if (in_array($this->meeting->status, ['scheduled', 'confirmed'])) {
            $this->meeting->update(['status' => 'completed']);
        }
    }
}
