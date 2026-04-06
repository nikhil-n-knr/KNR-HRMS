<?php

namespace App\Notifications\Compliance;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class StatutoryPaymentDue extends Notification
{
    use Queueable;

    protected $month;
    protected $type;

    public function __construct($month, $type)
    {
        $this->month = $month;
        $this->type = strtoupper($type);
    }

    public function via($notifiable)
    {
        return ['database']; // Add 'mail' if mail configured
    }

    public function toArray($notifiable)
    {
        return [
            'title' => "{$this->type} Payment Due",
            'message' => "The statutory payment for {$this->type} ({$this->month}) is pending. Due date is 15th.",
            'action_url' => route('hr.compliance.index'), // Link to dashboard
            'type' => 'warning'
        ];
    }
}
