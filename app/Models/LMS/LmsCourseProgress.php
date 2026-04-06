<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsCourseProgress extends Model
{
    protected $table = 'lms_course_progress';

    protected $fillable = [
        'user_id', 'enrollment_id', 'course_id',
        'completion_pct', 'modules_total', 'modules_completed',
        'concepts_total', 'concepts_completed',
        'total_watch_seconds', 'total_time_seconds',
        'avg_quiz_score', 'assignments_submitted', 'live_sessions_attended',
        'is_completed', 'certificate_issued',
        'completed_at', 'last_activity_at',
    ];

    protected $casts = [
        'is_completed'       => 'boolean',
        'certificate_issued' => 'boolean',
        'completed_at'       => 'datetime',
        'last_activity_at'   => 'datetime',
    ];

    public function course()     { return $this->belongsTo(\App\Models\LmsCourse::class); }
    public function enrollment() { return $this->belongsTo(LmsEnrollment::class); }
    public function user()       { return $this->belongsTo(\App\Models\User::class); }
}
