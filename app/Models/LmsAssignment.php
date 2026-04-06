<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsAssignment extends Model
{
    protected $fillable = [
        'course_id', 'employee_id', 'assigned_on', 'due_date',
        'valid_until', 'status', 'assigned_by', 'completed_at'
    ];
    
    protected $casts = [
        'assigned_on' => 'date',
        'due_date' => 'date',
        'valid_until' => 'date',
        'completed_at' => 'datetime',
    ];
    
    public function course()
    {
        return $this->belongsTo(LmsCourse::class, 'course_id');
    }
    
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
    
    public function attempts()
    {
        return $this->hasMany(LmsAttempt::class, 'assignment_id');
    }
    
    public function isOverdue(): bool
    {
        return $this->status === 'pending' && now()->gt($this->due_date);
    }
    
    public function isExpired(): bool
    {
        return $this->valid_until && now()->gt($this->valid_until);
    }
}
