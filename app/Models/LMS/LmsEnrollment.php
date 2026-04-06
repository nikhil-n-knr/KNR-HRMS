<?php

namespace App\Models\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsEnrollment extends Model
{
    protected $table = 'lms_enrollments';

    protected $fillable = [
        'user_id', 'course_id', 'program_id', 'institution_id',
        'source', 'status', 'semester', 'enrolled_at', 'expires_at',
        'completed_at', 'enrolled_by', 'metadata',
    ];

    protected $casts = [
        'metadata'    => 'array',
        'enrolled_at' => 'datetime',
        'expires_at'  => 'datetime',
        'completed_at'=> 'datetime',
    ];

    public function user()        { return $this->belongsTo(\App\Models\User::class); }
    public function course()      { return $this->belongsTo(\App\Models\LmsCourse::class); }
    public function program()     { return $this->belongsTo(LmsProgram::class); }
    public function institution() { return $this->belongsTo(LmsInstitution::class); }
    
    public function courseProgress()  { return $this->hasOne(LmsCourseProgress::class, 'enrollment_id'); }
    public function moduleProgress()  { return $this->hasMany(LmsModuleProgress::class, 'enrollment_id'); }
    public function conceptProgress() { return $this->hasMany(LmsConceptProgress::class, 'enrollment_id'); }
    public function certificatesV2()  { return $this->hasMany(LmsCertificateV2::class, 'course_id', 'course_id')->where('user_id', $this->user_id); }

    public function scopeActive($query)    { return $query->where('status', 'active'); }
    public function scopeCompleted($query) { return $query->where('status', 'completed'); }
}
