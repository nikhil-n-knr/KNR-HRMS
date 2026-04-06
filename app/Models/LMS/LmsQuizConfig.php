<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsQuizConfig extends Model
{
    protected $table = 'lms_quiz_configs';

    protected $fillable = [
        'activity_id', 'title', 'instructions', 'duration_minutes', 'max_attempts',
        'pass_mark_pct', 'shuffle_questions', 'shuffle_options', 'questions_to_show',
        'question_pool', 'question_ids', 'enable_negative_marking',
        'feedback_mode', 'show_correct_answers', 'allow_review',
        'cooloff_action', 'cooloff_hours', 'is_proctored',
        'prevent_copy_paste', 'track_tab_switches', 'max_tab_switches',
    ];

    protected $casts = [
        'question_pool'          => 'array',
        'question_ids'           => 'array',
        'shuffle_questions'      => 'boolean',
        'shuffle_options'        => 'boolean',
        'enable_negative_marking'=> 'boolean',
        'show_correct_answers'   => 'boolean',
        'allow_review'           => 'boolean',
        'is_proctored'           => 'boolean',
        'prevent_copy_paste'     => 'boolean',
        'track_tab_switches'     => 'boolean',
    ];

    public function activity() { return $this->belongsTo(LmsActivity::class); }
    public function attempts() { return $this->hasMany(LmsQuizAttempt::class, 'quiz_config_id'); }
}
