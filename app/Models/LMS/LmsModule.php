<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LmsModule extends Model
{
    use SoftDeletes;

    protected $table = 'lms_modules';

    protected $fillable = [
        'course_id', 'title', 'description', 'thumbnail_path',
        'sort_order', 'is_mandatory', 'unlock_condition',
        'estimated_duration_minutes', 'has_module_certificate', 'is_active',
    ];

    protected $casts = [
        'is_mandatory'           => 'boolean',
        'unlock_condition'       => 'array',
        'has_module_certificate' => 'boolean',
        'is_active'              => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(\App\Models\LmsCourse::class, 'course_id');
    }

    public function chapters()
    {
        return $this->hasMany(LmsChapter::class, 'module_id')->orderBy('sort_order');
    }

    public function concepts()
    {
        return $this->hasMany(LmsConcept::class, 'module_id');
    }

    public function activities()
    {
        return $this->hasMany(LmsActivity::class, 'module_id');
    }

    public function liveSessions()
    {
        return $this->hasMany(LmsLiveSession::class, 'module_id');
    }

    public function moduleProgress()
    {
        return $this->hasMany(LmsModuleProgress::class, 'module_id');
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeOrdered($query) { return $query->orderBy('sort_order'); }

    /**
     * Count total mandatory concepts needed for completion
     */
    public function getMandatoryConceptsCountAttribute(): int
    {
        return $this->concepts()->where('is_mandatory', true)->count();
    }
}
