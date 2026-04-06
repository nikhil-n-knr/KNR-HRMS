<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestProcessed extends Notification implements ShouldQueue
{
    use Queueable;

    public $entity;
    public $status;
    public $message;
    public $url;

    /**
     * Create a new notification instance.
     * 
     * @param mixed $entity The approved/rejected model (LeaveRequest, Expense, etc.)
     * @param string $status Approved or Rejected
     * @param string|null $customMessage Optional custom message
     * @param string|null $url Redirect URL for details
     */
    public function __construct($entity, $status, $customMessage = null, $url = null)
    {
        $this->entity = $entity;
        $this->status = $status;
        $this->message = $customMessage;
        $this->url = $url;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $type = class_basename($this->entity);
        $subject = "Your {$type} has been " . $this->status;

        $mail = (new MailMessage)
                    ->subject($subject)
                    ->greeting("Hello {$notifiable->name},")
                    ->line("Your request for {$type} has been " . strtolower($this->status) . ".");

        if ($this->message) {
            $mail->line("Remarks: " . $this->message);
        }

        if ($this->url) {
            $mail->action('View Details', $this->url);
        }

        return $mail->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $type = class_basename($this->entity);
        
        return [
            'entity_id' => $this->entity->id,
            'entity_type' => get_class($this->entity),
            'status' => $this->status,
            'type' => 'request_processed',
            'message' => $this->message ?? "Your {$type} request was {$this->status}.",
            'url' => $this->url
        ];
    }
}
