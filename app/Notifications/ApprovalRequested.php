<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public $approval;
    public $step;

    /**
     * Create a new notification instance.
     */
    public function __construct($approval, $step = null)
    {
        $this->approval = $approval;
        $this->step = $step;
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
        // Dynamic URL based on type
        $url = route('manager.approvals.index');
        
        if ($this->approval->approvable_type === 'App\Models\Expense') {
            $url = route('manager.approvals.index', ['tab' => 'expenses']);
        } elseif ($this->approval->approvable_type === 'App\Models\Payroll') {
            $url = route('manager.approvals.index', ['tab' => 'payroll']);
        }

        return (new MailMessage)
                    ->subject('Action Required: New Approval Request')
                    ->greeting("Hello {$notifiable->name},")
                    ->line("A new request requires your approval.")
                    ->line("Type: " . class_basename($this->approval->approvable_type))
                    ->line("Requester: " . optional($this->approval->requester)->name)
                    ->action('View Request', $url)
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'approval_id' => $this->approval->id,
            'step_id' => optional($this->step)->id,
            'requester_id' => $this->approval->requester_id,
            'requester_name' => optional($this->approval->requester)->name,
            'type' => class_basename($this->approval->approvable_type),
            'message' => "New approval request from " . optional($this->approval->requester)->name,
        ];
    }
}
