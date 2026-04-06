<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LmsProgram extends Model
{
    use SoftDeletes;

    protected $table = 'lms_programs';

    protected $fillable = [
        'institution_id', 'name', 'code', 'description',
        'duration_semesters', 'degree_type', 'specialization',
        'syllabus_config', 'certificate_rules', 'is_active',
    ];

    protected $casts = [
        'syllabus_config'   => 'array',
        'certificate_rules' => 'array',
        'is_active'         => 'boolean',
    ];

    public function institution()
    {
        return $this->belongsTo(LmsInstitution::class, 'institution_id');
    }

    public function courses()
    {
        return $this->belongsToMany(\App\Models\LmsCourse::class, 'lms_course_programs', 'program_id', 'course_id')
            ->withPivot('semester', 'is_mandatory', 'sort_order')
            ->withTimestamps();
    }

    public function enrollments()
    {
        return $this->hasMany(LmsEnrollment::class, 'program_id');
    }
}
