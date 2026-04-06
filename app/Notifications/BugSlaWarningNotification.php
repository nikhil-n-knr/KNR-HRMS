<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BugSlaWarningNotification extends Notification
{
    use Queueable;

    public function __construct(public $bug, public $level)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $levels = [
            'level_1' => 'Action Required (SLA Warning)',
            'level_2' => 'Manager Escalation (SLA Breach)',
            'level_3' => 'SYSTEM DEFENSE: Resource Lock Initiated'
        ];

        $subject = $levels[$this->level] ?? 'Bug Alert';

        return (new MailMessage)
                    ->subject("[$subject] Bug #{$this->bug->id}")
                    ->line("This is an automated SLA alert for Bug #{$this->bug->id}.")
                    ->line("Severity: {$this->bug->severity}")
                    ->line("Subject: {$this->bug->subject}")
                    ->action('View Ticket', url('/projects/bugs?id=' . $this->bug->id))
                    ->line('Please take immediate action.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'bug_id' => $this->bug->id,
            'subject' => $this->bug->subject,
            'message' => "SLA Alert ({$this->level}) for Bug #{$this->bug->id}",
            'action_url' => '/projects/bugs?id=' . $this->bug->id
        ];
    }
}
