<?php

namespace App\Mail\CRM;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\CRM\Meeting;

class MeetingInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $meeting;
    public $joinUrl;

    public function __construct(Meeting $meeting, $joinUrl = null)
    {
        $this->meeting = $meeting;
        $this->joinUrl = $joinUrl ?? $meeting->link;
    }

    public function build()
    {
        return $this->subject("Meeting Invitation: {$this->meeting->title}")
            ->markdown('emails.crm.meeting-invitation')
            ->with([
                'joinUrl' => $this->joinUrl,
                'startTime' => $this->meeting->start_time->toDayDateTimeString(),
            ]);
    }
}
