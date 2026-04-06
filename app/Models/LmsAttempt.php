<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsAttempt extends Model
{
    protected $fillable = [
        'assignment_id', 'employee_id', 'course_id', 'attempt_number',
        'started_at', 'submitted_at', 'time_spent_seconds',
        'score_obtained', 'max_score', 'percentage', 'is_passed',
        'answers_log', 'tab_switches_count', 'violations', 'status'
    ];
    
    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'answers_log' => 'array',
        'violations' => 'array',
        'is_passed' => 'boolean',
    ];
    
    public function course()
    {
        return $this->belongsTo(LmsCourse::class, 'course_id');
    }
    
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    
    public function assignment()
    {
        return $this->belongsTo(LmsAssignment::class);
    }
    
    public function certificate()
    {
        return $this->hasOne(LmsCertificate::class, 'attempt_id');
    }
    
    public function recordTabSwitch()
    {
        $this->increment('tab_switches_count');
        
        $violations = $this->violations ?? [];
        $violations[] = [
            'type' => 'tab_switch',
            'timestamp' => now()->toIso8601String()
        ];
        
        $this->update(['violations' => $violations]);
    }
    
    public function recordCopyAttempt()
    {
        $violations = $this->violations ?? [];
        $violations[] = [
            'type' => 'copy_attempt',
            'timestamp' => now()->toIso8601String()
        ];
        
        $this->update(['violations' => $violations]);
    }
}
