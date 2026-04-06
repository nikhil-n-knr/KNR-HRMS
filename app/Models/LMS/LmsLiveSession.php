<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsLiveSession extends Model
{
    protected $table = 'lms_live_sessions';

    protected $fillable = [
        'activity_id', 'course_id', 'module_id', 'title', 'agenda',
        'host_user_id', 'co_host_ids', 'scheduled_at', 'duration_minutes',
        'provider', 'meeting_url', 'meeting_id', 'meeting_password', 'provider_config',
        'min_attendance_pct', 'auto_record', 'recording_url', 'recording_path',
        'status', 'started_at', 'ended_at', 'actual_duration_minutes',
        'peak_attendees', 'settings',
    ];

    protected $casts = [
        'co_host_ids'    => 'array',
        'provider_config'=> 'array',
        'settings'       => 'array',
        'auto_record'    => 'boolean',
        'scheduled_at'   => 'datetime',
        'started_at'     => 'datetime',
        'ended_at'       => 'datetime',
    ];

    public function host()       { return $this->belongsTo(\App\Models\User::class, 'host_user_id'); }
    public function course()     { return $this->belongsTo(\App\Models\LmsCourse::class); }
}
