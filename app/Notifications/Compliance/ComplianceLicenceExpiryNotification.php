<?php

namespace App\Notifications\Compliance;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ComplianceLicenceExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $licence;
    protected $daysRemaining;

    public function __construct($licence, $daysRemaining)
    {
        $this->licence = $licence;
        $this->daysRemaining = $daysRemaining;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('URGENT: Statutory Licence Expiring Soon')
            ->line("The licence '{$this->licence->name}' for state '{$this->licence->state_code}' is expiring in {$this->daysRemaining} days.")
            ->line("Expiry Date: " . $this->licence->expiry_date->format('d M Y'))
            ->action('View Compliance Hub', url('/hr/compliance?tab=modules&sub=licences'))
            ->line('Please ensure renewal is initiated to avoid legal penalties.');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Licence Expiry Alert',
            'message' => "{$this->licence->name} expires on {$this->licence->expiry_date->format('d M Y')}",
            'licence_id' => $this->licence->id,
            'type' => 'compliance_alert'
        ];
    }
}
