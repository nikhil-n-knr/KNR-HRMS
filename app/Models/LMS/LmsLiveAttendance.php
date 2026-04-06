<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsLiveAttendance extends Model
{
    protected $table = 'lms_live_attendance';

    protected $fillable = [
        'session_id', 'user_id', 'joined_at', 'left_at',
        'total_minutes_present', 'attendance_pct', 'is_counted', 'join_leave_log',
    ];

    protected $casts = [
        'join_leave_log' => 'array',
        'is_counted'     => 'boolean',
        'joined_at'      => 'datetime',
        'left_at'        => 'datetime',
    ];

    public function session() { return $this->belongsTo(LmsLiveSession::class); }
}
