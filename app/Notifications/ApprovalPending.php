<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalPending extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $instance;

    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    public function via($notifiable)
    {
        return ['database']; // Keep it simple for now
    }

    public function toArray($notifiable)
    {
        return [
            'workflow_instance_id' => $this->instance->id,
            'message' => 'New approval request pending.',
            'entity_type' => $this->instance->entity_type,
            'entity_id' => $this->instance->entity_id
        ];
    }
}
