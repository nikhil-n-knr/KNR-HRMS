<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsQuizAttempt extends Model
{
    protected $table = 'lms_quiz_attempts';

    protected $fillable = [
        'quiz_config_id', 'activity_id', 'user_id', 'attempt_number',
        'status', 'question_sequence', 'score_obtained', 'max_score',
        'percentage', 'is_passed', 'time_spent_seconds', 'tab_switches_count',
        'violations', 'started_at', 'submitted_at', 'expires_at',
    ];

    protected $casts = [
        'question_sequence' => 'array',
        'violations'        => 'array',
        'is_passed'         => 'boolean',
        'started_at'        => 'datetime',
        'submitted_at'      => 'datetime',
        'expires_at'        => 'datetime',
    ];

    public function quizConfig() { return $this->belongsTo(LmsQuizConfig::class); }
    public function user()       { return $this->belongsTo(\App\Models\User::class); }
    public function responses()  { return $this->hasMany(LmsQuizResponse::class, 'attempt_id'); }
}
