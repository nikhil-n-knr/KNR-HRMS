<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LmsActivity extends Model
{
    use SoftDeletes;

    protected $table = 'lms_activities';

    protected $fillable = [
        'concept_id', 'chapter_id', 'module_id', 'course_id',
        'type', 'title', 'description', 'sort_order',
        'is_mandatory', 'is_graded', 'config', 'unlock_condition',
        'activityable_type', 'activityable_id', 'is_active',
    ];

    protected $casts = [
        'is_mandatory'    => 'boolean',
        'is_graded'       => 'boolean',
        'config'          => 'array',
        'unlock_condition'=> 'array',
        'is_active'       => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function concept()  { return $this->belongsTo(LmsConcept::class, 'concept_id'); }
    public function chapter()  { return $this->belongsTo(LmsChapter::class, 'chapter_id'); }
    public function module()   { return $this->belongsTo(LmsModule::class, 'module_id'); }
    public function course()   { return $this->belongsTo(\App\Models\LmsCourse::class, 'course_id'); }

    // Polymorphic: the actual activity definition
    public function activityable()
    {
        return $this->morphTo();
    }

    // Typed shortcuts
    public function videoLesson()
    {
        return $this->hasOne(LmsVideoLesson::class, 'activity_id');
    }

    public function readingMaterial()
    {
        return $this->hasOne(LmsReadingMaterial::class, 'activity_id');
    }

    public function quizConfig()
    {
        return $this->hasOne(LmsQuizConfig::class, 'activity_id');
    }

    public function assignment()
    {
        return $this->hasOne(LmsAssignmentV2::class, 'activity_id');
    }

    public function liveSession()
    {
        return $this->hasOne(LmsLiveSession::class, 'activity_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive($query)    { return $query->where('is_active', true); }
    public function scopeOrdered($query)   { return $query->orderBy('sort_order'); }
    public function scopeOfType($query, string $type) { return $query->where('type', $type); }
    public function scopeMandatory($query) { return $query->where('is_mandatory', true); }
}
