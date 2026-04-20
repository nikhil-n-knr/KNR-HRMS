<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BugStageChangedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $bug, public $oldStage, public $newStage)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Bug Status Update: #' . $this->bug->id)
                    ->line('The status of bug ticket #' . $this->bug->id . ' has changed.')
                    ->line('From: ' . $this->oldStage)
                    ->line('To: ' . $this->newStage)
                    ->action('View Ticket', url('/projects/bugs?id=' . $this->bug->id))
                    ->line('Thank you for your attention.');
    }

    public function toArray(object $notifiable): array
    {
        $projectName = $this->bug->project?->name ?? null;

        return [
            'bug_id' => $this->bug->id,
            'subject' => $this->bug->subject,
            'old_stage' => $this->oldStage,
            'new_stage' => $this->newStage,
            'priority' => $this->bug->priority,
            'project_id' => $this->bug->project_id,
            'project_name' => $projectName,
            'type' => 'bug_stage_changed',
            'changes' => [
                'stage' => [
                    'old' => $this->oldStage,
                    'new' => $this->newStage,
                ],
            ],
            'message' => "Bug #{$this->bug->id} [{$this->bug->subject}] moved from {$this->oldStage} to {$this->newStage}" . ($projectName ? " in {$projectName}." : '.'),
            'url' => '/projects/bugs?tab=tracker&bug=' . $this->bug->id
        ];
    }
}
