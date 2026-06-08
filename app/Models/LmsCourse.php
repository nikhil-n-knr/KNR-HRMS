<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LmsCourse extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'title', 'slug', 'description', 'category_id', 'institution_id', 'program_id', 
        'semester', 'level', 'language', 'promo_video_url', 'thumbnail_url', 'thumbnail_path',
        'mode', 'is_public', 'is_published', 'allow_self_enrollment', 'price',
        'tags', 'cover_image', 'banner_image', 'prerequisites', 'what_youll_learn',
        'course_requirements', 'target_audience', 'total_concepts', 'total_modules',
        'total_duration_minutes', 'enrolled_count', 'avg_rating', 'total_ratings',
        'completion_count', 'certificate_rule_id', 'certificate_template_id',
        'erp_course_code', 'erp_metadata', 'validity_days', 'target_audience_type',
        'target_audience_config', 'deadline_days_from_joining', 'passing_score', 
        'max_attempts', 'timer_minutes', 'shuffle_questions', 'shuffle_options',
        'question_pool_size', 'auto_generate_certificate', 'certificate_template_path',
        'disable_seeking', 'min_time_per_section', 'prevent_copy_paste',
        'track_tab_switches', 'max_tab_switches', 'action_on_fail',
        'cooloff_hours', 'is_active', 'created_by'
    ];
    
    protected $casts = [
        'target_audience_config'    => 'array',
        'tags'                      => 'array',
        'prerequisites'             => 'array',
        'erp_metadata'              => 'array',
        'shuffle_questions'         => 'boolean',
        'shuffle_options'           => 'boolean',
        'auto_generate_certificate' => 'boolean',
        'is_public'                 => 'boolean',
        'is_published'              => 'boolean',
        'allow_self_enrollment'     => 'boolean',
        'disable_seeking'           => 'boolean',
        'prevent_copy_paste'        => 'boolean',
        'track_tab_switches'        => 'boolean',
        'is_active'                 => 'boolean',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });
    }
    
    // ─────────────────────────────────────────────────────────────────────
    // ADVANCED LMS RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────────────

    public function institution()
    {
        return $this->belongsTo(LMS\LmsInstitution::class, 'institution_id');
    }

    public function program()
    {
        return $this->belongsTo(LMS\LmsProgram::class, 'program_id');
    }

    public function category()
    {
        return $this->belongsTo(LMS\LmsCategory::class, 'category_id');
    }

    public function modules()
    {
        return $this->hasMany(LMS\LmsModule::class, 'course_id')->ordered();
    }

    public function chapters()
    {
        return $this->hasMany(LMS\LmsChapter::class, 'course_id')->ordered();
    }

    public function concepts()
    {
        return $this->hasMany(LMS\LmsConcept::class, 'course_id')->ordered();
    }

    public function activities()
    {
        return $this->hasMany(LMS\LmsActivity::class, 'course_id')->ordered();
    }

    public function enrollments()
    {
        return $this->hasMany(LMS\LmsEnrollment::class, 'course_id');
    }

    public function courseProgress()
    {
        return $this->hasMany(LMS\LmsCourseProgress::class, 'course_id');
    }

    public function certificateRule()
    {
        return $this->belongsTo(LMS\LmsCertificateRule::class, 'certificate_rule_id');
    }

    public function certificateTemplate()
    {
        return $this->belongsTo(LMS\LmsCertificateTemplate::class, 'certificate_template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─────────────────────────────────────────────────────────────────────
    // LEGACY & HELPER METHODS
    // ─────────────────────────────────────────────────────────────────────

    public function contents()
    {
        return $this->hasMany(LmsCourseContent::class, 'course_id')->orderBy('order');
    }
    
    public function questions()
    {
        return $this->hasMany(LmsQuestion::class, 'course_id');
    }
    
    public function assignments()
    {
        return $this->hasMany(LmsAssignment::class, 'course_id');
    }
    
    public function attempts()
    {
        return $this->hasMany(LmsAttempt::class, 'course_id');
    }
    
    public function certificatesV1()
    {
        return $this->hasMany(LmsCertificate::class, 'course_id');
    }
    
    public function certificates()
    {
        return $this->hasMany(LmsCertificate::class, 'course_id');
    }
    
    public function getTargetEmployees()
    {
        $query = \App\Models\Employee::where('status', 'active');
        
        switch ($this->target_audience_type) {
            case 'department':
                $deptIds = $this->target_audience_config['department_ids'] ?? [];
                $query->whereIn('department_id', $deptIds);
                break;
                
            case 'gender':
                $gender = $this->target_audience_config['gender'] ?? null;
                if ($gender) {
                    $query->where('gender', $gender);
                }
                break;
                
            case 'role':
                $roleIds = $this->target_audience_config['role_ids'] ?? [];
                $query->whereHas('user', function($q) use ($roleIds) {
                    $q->whereIn('role_id', $roleIds);
                });
                break;
                
            case 'location':
                $locationIds = $this->target_audience_config['location_ids'] ?? [];
                $query->whereIn('location_id', $locationIds);
                break;
        }
        
        return $query->get();
    }
    
    public function canEmployeeAttempt($employee): array
    {
        $assignment = $this->assignments()
            ->where('employee_id', $employee->id)
            ->latest()
            ->first();
            
        if (!$assignment) {
            return ['can_attempt' => false, 'reason' => 'Not assigned'];
        }
        
        if ($assignment->status === 'completed' && !$this->isExpired($assignment)) {
            return ['can_attempt' => false, 'reason' => 'Already completed'];
        }
        
        $attemptsCount = $this->attempts()
            ->where('assignment_id', $assignment->id)
            ->count();
            
        if ($attemptsCount >= $this->max_attempts) {
            return ['can_attempt' => false, 'reason' => 'Maximum attempts reached'];
        }
        
        // Check cooloff period
        $lastAttempt = $this->attempts()
            ->where('assignment_id', $assignment->id)
            ->where('is_passed', false)
            ->latest()
            ->first();
            
        if ($lastAttempt && $this->action_on_fail === 'cooloff') {
            $cooloffEnds = $lastAttempt->created_at->addHours($this->cooloff_hours);
            if (now()->lt($cooloffEnds)) {
                return [
                    'can_attempt' => false,
                    'reason' => 'Cooloff period active',
                    'available_at' => $cooloffEnds
                ];
            }
        }
        
        return ['can_attempt' => true];
    }
    
    private function isExpired($assignment): bool
    {
        if (!$assignment->valid_until) {
            return false;
        }
        return now()->gt($assignment->valid_until);
    }
}
