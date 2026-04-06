<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Interview extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'last_reminder_sent_at' => 'datetime',
        'reminder_history' => 'array',
        'no_show' => 'boolean'
    ];

    protected $fillable = [
        'job_application_id', 'interviewer_id', 'scheduled_at', 
        'duration', 'status', 'type', 'meeting_link', 'location', 
        'round', 'round_title', 'message_body', 'reminder_count',
        'last_reminder_sent_at', 'reminder_history',
        'result', 'cancellation_reason', 'reschedule_reason', 'no_show'
    ];

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function interviewer()
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(InterviewFeedback::class);
    }

    // Helper for latest/single feedback
    public function feedback()
    {
        return $this->hasOne(InterviewFeedback::class)->latest();
    }
}
