<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsModuleProgress extends Model
{
    protected $table = 'lms_module_progress';

    protected $fillable = [
        'user_id', 'enrollment_id', 'module_id', 'course_id',
        'completion_pct', 'concepts_total', 'concepts_completed',
        'total_time_seconds', 'avg_quiz_score',
        'is_completed', 'certificate_issued', 'completed_at',
    ];

    protected $casts = [
        'is_completed'      => 'boolean',
        'certificate_issued'=> 'boolean',
        'completed_at'      => 'datetime',
    ];

    public function module()     { return $this->belongsTo(LmsModule::class); }
    public function enrollment() { return $this->belongsTo(LmsEnrollment::class); }
}
