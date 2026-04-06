<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsQuestion extends Model
{
    protected $fillable = [
        'course_id', 'content_id', 'type', 'question_text', 'image_path',
        'scenario_context', 'options', 'score_weight', 'max_score',
        'explanation', 'require_manual_grading', 'is_active'
    ];
    
    protected $casts = [
        'options' => 'array',
        'require_manual_grading' => 'boolean',
        'is_active' => 'boolean',
    ];
    
    public function course()
    {
        return $this->belongsTo(LmsCourse::class, 'course_id');
    }
    
    public function content()
    {
        return $this->belongsTo(LmsCourseContent::class, 'content_id');
    }
}
