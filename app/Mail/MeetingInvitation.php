<?php

namespace App\Mail;

use App\Models\CRM\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MeetingInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $meeting;
    public $isUpdate;
    public $isCancelled;
    public $fromEmail;
    public $fromName;

    /**
     * Create a new message instance.
     */
    public function __construct(Meeting $meeting, $isUpdate = false, $isCancelled = false, $fromEmail = null, $fromName = null)
    {
        $this->meeting = $meeting;
        $this->isUpdate = $isUpdate;
        $this->isCancelled = $isCancelled;
        $this->fromEmail = $fromEmail ?? config('mail.from.address');
        $this->fromName = $fromName ?? config('mail.from.name');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $prefix = $this->isCancelled ? '[CANCELLED] ' : ($this->isUpdate ? '[UPDATED] ' : '');
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address($this->fromEmail, $this->fromName),
            subject: $prefix . 'Meeting: ' . $this->meeting->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtmlContent()
        );
    }

    private function buildHtmlContent()
    {
        $startTime = \Carbon\Carbon::parse($this->meeting->start_time)->format('l, F j, Y \a\t g:i A');
        $endTime = \Carbon\Carbon::parse($this->meeting->end_time)->format('g:i A');
        
        $locationValue = $this->meeting->type === 'online' 
            ? "<a href='{$this->meeting->conferencing_link}' style='color: #4f46e5; font-weight: bold;'>Join " . ucfirst($this->meeting->integration_provider) . " Meeting</a>" 
            : $this->meeting->location;

        if ($this->meeting->type === 'offline') {
            $details = [];
            if ($this->meeting->venue_building) $details[] = "Building: {$this->meeting->venue_building}";
            if ($this->meeting->venue_room) $details[] = "Room: {$this->meeting->venue_room}";
            if ($this->meeting->venue_phone) $details[] = "Phone: {$this->meeting->venue_phone}";
            
            if (!empty($details)) {
                $locationValue .= "<p style='margin: 8px 0 0 0; font-size: 13px; color: #6b7280; font-weight: normal;'>" . implode(' | ', $details) . "</p>";
            }

            if ($this->meeting->location) {
                $mapUrl = "https://www.google.com/maps/search/?api=1&query=" . urlencode($this->meeting->location);
                $locationValue .= " <br><a href='{$mapUrl}' style='color: #4f46e5; font-size: 12px; font-weight: bold; text-decoration: none;'>🗺️ View on Google Maps</a>";
            }
        }
        
        $statusHeader = $this->isCancelled ? 'CANCELLED' : ($this->isUpdate ? 'UPDATED' : 'INVITATION');
        $headerColor = $this->isCancelled ? '#ef4444' : ($this->isUpdate ? '#f59e0b' : '#4f46e5');

        $html = "<div style='font-family: sans-serif; color: #1f2937; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;'>";
        $html .= "<div style='background-color: {$headerColor}; color: white; padding: 20px; text-align: center;'>";
        $html .= "<p style='margin: 0; font-size: 12px; font-weight: 800; letter-spacing: 0.1em;'>MEETING {$statusHeader}</p>";
        $html .= "<h1 style='margin: 10px 0 0 0; font-size: 24px; font-weight: 900;'>{$this->meeting->title}</h1>";
        $html .= "</div>";
        
        $html .= "<div style='padding: 30px;'>";
        if ($this->isCancelled) {
            $html .= "<p style='color: #ef4444; font-weight: bold;'>This meeting has been cancelled.</p>";
        } else {
            $html .= "<p>Hello,</p>";
            if ($this->isUpdate) {
                $html .= "<p>The details for this meeting have been updated. Please check the new schedule below.</p>";
            } else {
                $html .= "<p>You have been invited to a scheduled meeting. Here are the details:</p>";
            }
        }
        
        $html .= "<div style='background-color: #f9fafb; padding: 20px; border-radius: 12px; margin: 20px 0;'>";
        $html .= "<table style='width: 100%; border-collapse: collapse;'>";
        $html .= "<tr><td style='padding: 8px 0; font-size: 12px; color: #6b7280; font-weight: 800; text-transform: uppercase;'>When</td><td style='padding: 8px 0; font-weight: bold;'>{$startTime} - {$endTime}</td></tr>";
        $html .= "<tr><td style='padding: 8px 0; font-size: 12px; color: #6b7280; font-weight: 800; text-transform: uppercase;'>Where</td><td style='padding: 8px 0; font-weight: bold;'>{$locationValue}</td></tr>";
        if ($this->meeting->description) {
            $html .= "<tr><td style='padding: 8px 0; font-size: 12px; color: #6b7280; font-weight: 800; text-transform: uppercase;'>Agenda</td><td style='padding: 8px 0; color: #4b5563;'>{$this->meeting->description}</td></tr>";
        }
        $html .= "</table>";
        $html .= "</div>";
        
        if (!$this->isCancelled) {
            $acceptUrl = route('meetings.rsvp', ['uuid' => $this->meeting->booking_link, 'status' => 'accepted']);
            $declineUrl = route('meetings.rsvp', ['uuid' => $this->meeting->booking_link, 'status' => 'declined']);

            $html .= "<div style='margin-top: 30px; text-align: center; display: flex; gap: 10px; justify-content: center;'>";
            $html .= "<a href='{$acceptUrl}' style='background-color: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; margin-right: 10px;'>Accept</a>";
            $html .= "<a href='{$declineUrl}' style='background-color: #ef4444; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold;'>Decline</a>";
            $html .= "</div>";
            $html .= "<p style='font-size: 14px; color: #4b5563; margin-top: 20px;'>A calendar invite has been added to your inbox. You can also accept or decline using the buttons above.</p>";
        }
        
        $html .= "<div style='margin-top: 30px; border-top: 1px solid #f3f4f6; pt: 20px; font-size: 14px; color: #9ca3af;'>";
        $html .= "<p>Best regards,<br><strong>" . (auth()->check() ? auth()->user()->name : config('app.name')) . "</strong></p>";
        $html .= "</div>";
        $html .= "</div>";
        $html .= "</div>";
        
        return $html;
    }
}
