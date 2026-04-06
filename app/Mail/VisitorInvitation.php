<?php

namespace App\Mail;

use App\Models\VisitorPass;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VisitorInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $visitorPass;

    /**
     * Create a new message instance.
     */
    public function __construct(VisitorPass $visitorPass)
    {
        $this->visitorPass = $visitorPass;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Exclusive Invitation: ' . $this->visitorPass->event->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.visitors.invitation',
            with: [
                'name' => $this->visitorPass->visitor->name,
                'eventTitle' => $this->visitorPass->event->title,
                'location' => $this->visitorPass->event->location,
                'startTime' => $this->visitorPass->event->start_time->toDayDateTimeString(),
                'qrCode' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . $this->visitorPass->pass_code,
                'link' => route('visitors.guest.pre-checkin', $this->visitorPass->pass_code),
            ],
        );
    }
}
