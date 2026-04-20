<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'content',
        'is_completed',
        'position',
        'assigned_to',
        'planned_minutes',
        'actual_minutes',
        'work_date',
        'started_at',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'position' => 'integer',
        'planned_minutes' => 'integer',
        'actual_minutes' => 'integer',
        'work_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'completed_by' => 'integer',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
