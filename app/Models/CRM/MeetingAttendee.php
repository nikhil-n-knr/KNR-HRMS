<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class MeetingAttendee extends Model
{
    protected $table = 'meeting_attendees';

    protected $fillable = [
        'meeting_id',
        'attendee_id',
        'attendee_type',
        'name',
        'email',
        'status',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function attendee()
    {
        return $this->morphTo(); // Employee, Contact, Lead
    }
}
