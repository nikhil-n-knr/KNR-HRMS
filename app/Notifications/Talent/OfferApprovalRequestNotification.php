<?php

namespace App\Notifications\Talent;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\OfferLetter;

class OfferApprovalRequestNotification extends Notification
{
    use Queueable;

    public $offer;

    /**
     * Create a new notification instance.
     */
    public function __construct($offer)
    {
        $this->offer = $offer;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $candidateName = $this->offer->jobApplication->candidate->first_name . ' ' . $this->offer->jobApplication->candidate->last_name;
        
        return (new MailMessage)
                    ->subject('Offer Approval Required: ' . $candidateName)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line("An offer letter for candidate **{$candidateName}** requires your approval.")
                    ->line('Please review the offer details, salary breakdown, and documents.')
                    ->action('Review & Approve', route('talent.offers.approval', $this->offer->id))
                    ->line('Thank you for your prompt attention.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'offer_approval',
            'offer_id' => $this->offer->id,
            'candidate_id' => $this->offer->jobApplication->candidate_id,
            'message' => 'Offer approval requested for ' . $this->offer->jobApplication->candidate->first_name,
            'url' => route('talent.offers.approval', $this->offer->jobApplication->candidate_id)
        ];
    }
}
