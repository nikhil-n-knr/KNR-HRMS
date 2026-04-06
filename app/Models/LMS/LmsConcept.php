<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LmsConcept extends Model
{
    use SoftDeletes;

    protected $table = 'lms_concepts';

    protected $fillable = [
        'chapter_id', 'module_id', 'course_id',
        'title', 'description', 'learning_outcomes', 'standard_code',
        'sort_order', 'is_mandatory', 'unlock_condition',
        'estimated_duration_minutes', 'min_time_seconds',
        'has_micro_quiz', 'has_micro_certificate',
        'completion_criteria', 'is_active',
    ];

    protected $casts = [
        'learning_outcomes'  => 'array',
        'unlock_condition'   => 'array',
        'completion_criteria'=> 'array',
        'is_mandatory'       => 'boolean',
        'has_micro_quiz'     => 'boolean',
        'has_micro_certificate' => 'boolean',
        'is_active'          => 'boolean',
    ];

    public function chapter()    { return $this->belongsTo(LmsChapter::class, 'chapter_id'); }
    public function module()     { return $this->belongsTo(LmsModule::class, 'module_id'); }
    public function course()     { return $this->belongsTo(\App\Models\LmsCourse::class, 'course_id'); }

    public function activities()
    {
        return $this->hasMany(LmsActivity::class, 'concept_id')->orderBy('sort_order');
    }

    public function videoActivities()
    {
        return $this->hasMany(LmsActivity::class, 'concept_id')->where('type', 'video');
    }

    public function quizActivities()
    {
        return $this->hasMany(LmsActivity::class, 'concept_id')->where('type', 'quiz');
    }

    public function assignmentActivities()
    {
        return $this->hasMany(LmsActivity::class, 'concept_id')->where('type', 'assignment');
    }

    public function progress()
    {
        return $this->hasMany(LmsConceptProgress::class, 'concept_id');
    }

    public function scopeActive($query)    { return $query->where('is_active', true); }
    public function scopeOrdered($query)   { return $query->orderBy('sort_order'); }
    public function scopeMandatory($query) { return $query->where('is_mandatory', true); }

    /**
     * Completion criteria defaults (for checking if concept is "done")
     */
    public function getCompletionCriteriaDefaulted(): array
    {
        return array_merge([
            'video_required'    => true,
            'quiz_required'     => $this->has_micro_quiz,
            'min_quiz_score'    => 60,
            'reading_required'  => false,
            'assignment_required' => false,
            'min_time_seconds'  => $this->min_time_seconds,
        ], $this->completion_criteria ?? []);
    }
}
