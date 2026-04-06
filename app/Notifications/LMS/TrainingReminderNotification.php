<?php

namespace App\Notifications\LMS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainingReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $courseTitle,
        public int $daysBefore,
        public string $actionUrl
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $urgency = $this->daysBefore === 1 ? 'tomorrow' : "in {$this->daysBefore} days";
        
        return (new MailMessage)
            ->subject("Training Reminder: {$this->courseTitle}")
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("This is a reminder about your pending training: {$this->courseTitle}")
            ->line("This training is due {$urgency}. Please complete it soon.")
            ->action('Complete Training', $this->actionUrl)
            ->line('Avoid missing the deadline!');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => "Training Reminder: {$this->courseTitle}",
            'message' => "This training is due in {$this->daysBefore} days. Please complete it soon.",
            'action_url' => $this->actionUrl,
            'type' => 'training_reminder'
        ];
    }
}
