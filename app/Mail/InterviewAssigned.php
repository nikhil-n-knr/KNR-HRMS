<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterviewAssigned extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $interview)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Interview Assigned: ' . $this->interview->application->candidate->first_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.interview.assigned',
            with: [
                'candidateName' => $this->interview->application->candidate->first_name . ' ' . $this->interview->application->candidate->last_name,
                'candidateLink' => route('talent.start'), // Redirect to dashboard or detail
                'date' => $this->interview->scheduled_at->format('M d, Y h:i A'),
                'type' => $this->interview->type,
                'meetingLink' => $this->interview->meeting_link,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
