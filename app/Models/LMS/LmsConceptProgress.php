<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsConceptProgress extends Model
{
    protected $table = 'lms_concept_progress';

    protected $fillable = [
        'user_id', 'enrollment_id', 'concept_id', 'chapter_id', 'module_id', 'course_id',
        'status', 'completion_pct', 'time_spent_seconds', 'video_watch_seconds',
        'video_completed', 'reading_completed', 'quiz_passed', 'quiz_best_score',
        'assignment_submitted', 'assignment_approved',
        'completed_at', 'last_activity_at',
    ];

    protected $casts = [
        'video_completed'       => 'boolean',
        'reading_completed'     => 'boolean',
        'quiz_passed'           => 'boolean',
        'assignment_submitted'  => 'boolean',
        'assignment_approved'   => 'boolean',
        'completed_at'          => 'datetime',
        'last_activity_at'      => 'datetime',
    ];

    public function concept()    { return $this->belongsTo(LmsConcept::class); }
    public function enrollment() { return $this->belongsTo(LmsEnrollment::class); }
    public function user()       { return $this->belongsTo(\App\Models\User::class); }
}
