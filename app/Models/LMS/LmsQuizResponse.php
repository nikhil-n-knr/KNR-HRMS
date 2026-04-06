<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsQuizResponse extends Model
{
    protected $table = 'lms_quiz_responses';

    protected $fillable = [
        'attempt_id', 'question_id', 'selected_answer',
        'is_correct', 'score', 'time_spent_seconds',
        'is_skipped', 'is_flagged',
    ];

    protected $casts = [
        'selected_answer' => 'array',
        'is_correct'      => 'boolean',
        'is_skipped'      => 'boolean',
        'is_flagged'      => 'boolean',
    ];

    public function attempt()  { return $this->belongsTo(LmsQuizAttempt::class); }
    public function question() { return $this->belongsTo(LmsQuestionV2::class, 'question_id'); }
}
