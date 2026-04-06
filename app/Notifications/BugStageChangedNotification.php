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
        return [
            'bug_id' => $this->bug->id,
            'subject' => $this->bug->subject,
            'message' => "Bug #{$this->bug->id} moved to {$this->newStage}",
            'action_url' => '/projects/bugs?id=' . $this->bug->id
        ];
    }
}
