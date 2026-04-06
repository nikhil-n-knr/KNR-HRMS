<?php

namespace App\Notifications\LMS;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $courseTitle,
        public string $dueDate,
        public string $actionUrl
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Training Assigned: {$this->courseTitle}")
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("A new training course has been assigned to you: {$this->courseTitle}")
            ->line("Please complete this training by {$this->dueDate}")
            ->action('Start Course', $this->actionUrl)
            ->line('Thank you for keeping your skills up to date!');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => "New Training Assigned: {$this->courseTitle}",
            'message' => "Please complete this training by {$this->dueDate}",
            'action_url' => $this->actionUrl,
            'type' => 'course_assigned'
        ];
    }
}
