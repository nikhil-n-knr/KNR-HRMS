<?php

namespace App\Jobs\CRM;

use App\Models\CRM\Meeting;
use App\Models\CRM\Activity;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PostMeetingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $meeting;

    public function __construct(Meeting $meeting)
    {
        $this->meeting = $meeting;
    }

    public function handle()
    {
        // 1. Log CRM Activity Record
        Activity::create([
            'tenant_id' => $this->meeting->tenant_id,
            'subject' => "Session Completed: {$this->meeting->title}",
            'activity_type' => 'Meeting',
            'description' => "Recording: {$this->meeting->link}",
            'status' => 'completed',
            'created_by' => $this->meeting->created_by,
        ]);

        // 2. Automated Follow-up Strategy
        Task::create([
            'tenant_id' => $this->meeting->tenant_id,
            'title' => "Strategy Review - Follow-up",
            'description' => "Post-meeting actions for " . $this->meeting->title,
            'priority' => 'High',
            'status' => 'Pending',
            'due_date' => now()->addDay(),
            'assigned_to' => $this->meeting->employee->user_id ?? $this->meeting->assigned_to,
        ]);
    }
}
