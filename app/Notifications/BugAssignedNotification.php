<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BugAssignedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $bug)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('New Bug Assigned: #' . $this->bug->id)
                    ->line('You have been assigned a new bug ticket.')
                    ->line('Subject: ' . $this->bug->subject)
                    ->line('Priority: ' . ucfirst($this->bug->priority))
                    ->action('View Ticket', url('/projects/bugs?id=' . $this->bug->id))
                    ->line('Please review it at your earliest convenience.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'bug_id' => $this->bug->id,
            'subject' => $this->bug->subject,
            'message' => 'New bug assigned: #' . $this->bug->id,
            'action_url' => '/projects/bugs?id=' . $this->bug->id
        ];
    }
}
