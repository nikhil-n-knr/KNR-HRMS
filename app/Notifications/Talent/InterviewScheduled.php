<?php

namespace App\Notifications\Talent;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Interview;
use App\Mail\InterviewAssigned;

class InterviewScheduled extends Notification
{
    use Queueable;

    public $interview;

    public function __construct(Interview $interview)
    {
        $this->interview = $interview;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        // Leverage the existing Mailable
        return (new InterviewAssigned($this->interview))->to($notifiable->email);
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'interview_scheduled',
            'interview_id' => $this->interview->id,
            'candidate_name' => $this->interview->application->candidate->first_name . ' ' . $this->interview->application->candidate->last_name,
            'scheduled_at' => $this->interview->scheduled_at,
            'round' => $this->interview->round,
            'message' => "New Interview Scheduled: {$this->interview->round} with {$this->interview->application->candidate->first_name}",
            'url' => route('talent.candidates.index', ['open_id' => $this->interview->application->candidate->id])
        ];
    }
}
