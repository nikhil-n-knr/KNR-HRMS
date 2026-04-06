<?php

namespace App\Notifications\Talent;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Interview;
use App\Mail\InterviewCancelled;

class InterviewCancelledNotification extends Notification
{
    use Queueable;

    public $interview;
    public $reason;

    public function __construct(Interview $interview, $reason)
    {
        $this->interview = $interview;
        $this->reason = $reason;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new InterviewCancelled($this->interview, $this->reason))->to($notifiable->email);
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'interview_cancelled',
            'interview_id' => $this->interview->id,
            'candidate_name' => $this->interview->application->candidate->first_name,
            'reason' => $this->reason,
            'message' => "Interview Cancelled: {$this->interview->round} (Reason: {$this->reason})",
            'url' => route('talent.candidates.index', ['open_id' => $this->interview->application->candidate->id])
        ];
    }
}
