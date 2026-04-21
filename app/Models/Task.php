<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\OptimisticLocking; // Validation Trait

class Task extends Model
{
    use HasFactory, SoftDeletes, OptimisticLocking;

    protected $table = 'project_tasks';

    protected static function booted()
    {
        static::updating(function ($task) {
            // Auto-capture baseline when locked for the first time
            if ($task->isDirty('is_locked') && $task->is_locked && is_null($task->baseline_start_date)) {
                $task->baseline_start_date = $task->start_date;
                $task->baseline_due_date = $task->due_date;
                // If total_efforts is not yet set (drift recording hasn't happened), 
                // use estimated_hours as the baseline point.
                $task->baseline_efforts = $task->total_efforts > 0 ? $task->total_efforts : $task->estimated_hours;
            }
        });
    }

    protected $fillable = [
        'project_id',
        'module_id',
        'sprint_id', // New
        'stage_id',  // New
        'title',
        'description',
        'status',          
        'priority',        
        'complexity',      
        'start_date',
        'estimated_hours',
        'actual_hours',
        'billable',
        'blocked_by_task_id',
        'created_by',
        'git_branch_url', // New
        'git_pr_url',     // New
        'qa_notes',       // New
        'deployed_to',    // New
        
        'is_backlog',
        'is_billable',
        'invoice_id',
        'billed_at',
        'version',
        'due_date',
        'scrum_points',
        'is_locked',
        'total_efforts',
        'baseline_efforts'
    ];

    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    public function stage()
    {
        return $this->belongsTo(ProjectStage::class);
    }

    protected $casts = [
        'billable' => 'boolean',
        'is_backlog' => 'boolean',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'start_date' => 'date',
        'due_date' => 'date',
        'baseline_start_date' => 'date',
        'baseline_due_date' => 'date',
        'is_locked' => 'boolean',
        'total_efforts' => 'decimal:2',
        'baseline_efforts' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function module()
    {
        return $this->belongsTo(ProjectModule::class, 'module_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function blocker()
    {
        return $this->belongsTo(Task::class, 'blocked_by_task_id');
    }

    public function assignees()
    {
        return $this->belongsToMany(\App\Models\Employee::class, 'task_assignees', 'task_id', 'employee_id')
                    ->withTimestamps();
    }

    /**
     * Polymorphic Assignments
     */
    public function assignments()
    {
        return $this->hasMany(WorkAssignment::class);
    }

    // Helper for Kanban (Singular Primary Assignee) logic moved to Controller

    public function reporter()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // --- Task Hub Relationships ---

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }

    public function checklists()
    {
        return $this->hasMany(TaskChecklist::class)->orderBy('position');
    }

    public function activities()
    {
        return $this->hasMany(TaskActivity::class)->latest();
    }

    public function dependencies()
    {
        return $this->belongsTo(Task::class, 'blocked_by_task_id');
    }

    public function dependents()
    {
        return $this->hasMany(Task::class, 'blocked_by_task_id');
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    public function bugTicket()
    {
        return $this->hasOne(BugTicket::class, 'task_id');
    }

    public function pullRequests()
    {
        return $this->hasMany(TaskPullRequest::class, 'task_id');
    }

    public function extensions()
    {
        return $this->hasMany(ProjectExtension::class, 'task_id');
    }
}
