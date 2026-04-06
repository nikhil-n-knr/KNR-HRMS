<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsCertificateRule extends Model
{
    protected $table = 'lms_certificate_rules';

    protected $fillable = [
        'name', 'description', 'type', 'course_id', 'program_id', 'template_id',
        'min_completion_pct', 'min_watch_seconds', 'min_quiz_avg_score',
        'min_live_sessions', 'min_live_attendance_pct',
        'capstone_required', 'topic_requirements', 'custom_rules', 'is_active',
    ];

    protected $casts = [
        'topic_requirements' => 'array',
        'custom_rules'       => 'array',
        'capstone_required'  => 'boolean',
        'is_active'          => 'boolean',
    ];

    public function course()    { return $this->belongsTo(\App\Models\LmsCourse::class); }
    public function program()   { return $this->belongsTo(LmsProgram::class); }
    public function template()  { return $this->belongsTo(LmsCertificateTemplate::class); }
    public function certificates() { return $this->hasMany(LmsCertificateV2::class, 'rule_id'); }
}
