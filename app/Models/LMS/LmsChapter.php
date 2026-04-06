<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LmsChapter extends Model
{
    use SoftDeletes;

    protected $table = 'lms_chapters';

    protected $fillable = [
        'module_id', 'course_id', 'title', 'description',
        'sort_order', 'is_mandatory', 'unlock_condition',
        'estimated_duration_minutes', 'is_active',
    ];

    protected $casts = [
        'is_mandatory'     => 'boolean',
        'unlock_condition' => 'array',
        'is_active'        => 'boolean',
    ];

    public function module()   { return $this->belongsTo(LmsModule::class, 'module_id'); }
    public function course()   { return $this->belongsTo(\App\Models\LmsCourse::class, 'course_id'); }
    public function concepts() { return $this->hasMany(LmsConcept::class, 'chapter_id')->orderBy('sort_order'); }
    public function activities(){ return $this->hasMany(LmsActivity::class, 'chapter_id'); }

    public function scopeActive($query)  { return $query->where('is_active', true); }
    public function scopeOrdered($query) { return $query->orderBy('sort_order'); }
}
