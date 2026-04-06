<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'minutes',
        'reason',
        'status',
        'approved_by',
        'rejection_reason',
        'project_id',
        'task_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    protected $casts = [
        'date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
