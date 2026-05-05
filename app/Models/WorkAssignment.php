<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'task_id',
        'assignee_id',
        'assignee_type',
        'allocated_hours',
        'daily_allocations',
        'start_date',
        'end_date',
        'force_allocation'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'allocated_hours' => 'decimal:2',
        'daily_allocations' => 'array',
        'force_allocation' => 'boolean'
    ];

    /**
     * Get the parent assignee model (User, Team, Department).
     */
    public function assignee()
    {
        return $this->morphTo();
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
