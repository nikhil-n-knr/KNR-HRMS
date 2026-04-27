<?php

namespace App\Notifications;

use App\Models\BugTicket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BugManualReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public BugTicket $bug, public User $sender)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bug Reminder: #' . $this->bug->id)
            ->line('A reminder has been sent for bug #' . $this->bug->id . '.')
            ->line('Subject: ' . $this->bug->subject)
            ->line('Priority: ' . ucfirst((string) $this->bug->priority))
            ->line('Reminder sent by: ' . $this->sender->name)
            ->action('View Bug', url('/projects/bugs?tab=tracker&bug=' . $this->bug->id))
            ->line('This reminder can only be sent once per day per bug.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'bug_manual_reminder',
            'bug_id' => $this->bug->id,
            'subject' => $this->bug->subject,
            'priority' => $this->bug->priority,
            'severity' => $this->bug->severity,
            'project_id' => $this->bug->project_id,
            'project_name' => $this->bug->project?->name ?? null,
            'sent_by' => $this->sender->name,
            'message' => "Reminder: #{$this->bug->id} [{$this->bug->priority}] {$this->bug->subject}",
            'url' => '/projects/bugs?tab=tracker&bug=' . $this->bug->id,
        ];
    }
}
