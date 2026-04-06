<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BugRatingReceivedNotification extends Notification
{
    use Queueable;

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
                    ->subject('CSAT Rating Received: #' . $this->bug->id)
                    ->line('A client has rated the resolution of a bug ticket.')
                    ->line('Ticket: ' . $this->bug->subject)
                    ->line('Rating: ' . $this->bug->rating . ' / 5')
                    ->line('Feedback: ' . ($this->bug->rating_feedback ?? 'No verbal feedback provided.'))
                    ->action('Review Performance', url('/projects/bugs/' . $this->bug->id))
                    ->line('This input is vital for our quality assurance protocol.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'bug_id' => $this->bug->id,
            'rating' => $this->bug->rating,
            'message' => "CSAT received: {$this->bug->rating}/5 stars for ticket #{$this->bug->id}",
            'action_url' => '/projects/bugs/' . $this->bug->id
        ];
    }
}
