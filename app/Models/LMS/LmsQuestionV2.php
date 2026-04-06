<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsQuestionV2 extends Model
{
    protected $table = 'lms_questions_v2';

    protected $fillable = [
        'bank_id', 'course_id', 'concept_id', 'category_id',
        'type', 'question_text', 'question_image', 'options',
        'correct_answer', 'marks', 'negative_marks', 'explanation',
        'explanation_video_url', 'difficulty', 'tags',
        'estimated_time_seconds', 'created_by', 'usage_count', 'avg_accuracy',
    ];

    protected $casts = [
        'options'         => 'array',
        'correct_answer'  => 'array',
        'tags'            => 'array',
    ];

    public function bank()    { return $this->belongsTo(LmsQuestionBank::class, 'bank_id'); }
    public function concept() { return $this->belongsTo(LmsConcept::class); }
    public function responses(){ return $this->hasMany(LmsQuizResponse::class, 'question_id'); }
}
