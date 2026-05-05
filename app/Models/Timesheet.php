<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'project_id', // Linked Project
        'project_name', // Legacy/Fallback
        'task_description',
        'task_type',
        'is_billable',
        'hours_spent',
        'status',
        'violation_flags',
        'task_id',
        'task_title',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'hours_spent' => 'decimal:2',
        'is_billable' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
