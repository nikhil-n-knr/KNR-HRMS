<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'client_id',
        'name',
        'code',
        'description',
        'status',              // planning, active, etc.
        'visibility',          // public, team_locked, stealth, freelancer_mode
        'start_date',
        'deadline',
        'gamification_settings', // JSON
        'billing_type',
        'hourly_rate',
        'currency'
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'gamification_settings' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function modules()
    {
        return $this->hasMany(ProjectModule::class);
    }

    public function stages()
    {
        return $this->hasMany(ProjectStage::class)->orderBy('order');
    }

    public function sprints()
    {
        return $this->hasMany(Sprint::class)->orderBy('end_date');
    }

    public function priorities()
    {
        return $this->hasMany(ProjectPriority::class)->orderBy('order');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function taskTemplates()
    {
        return $this->hasMany(TaskTemplate::class);
    }

    /**
     * Project-Level Assignments (Team allocation to project)
     */
    public function assignments()
    {
        return $this->hasMany(WorkAssignment::class);
    }

    public function members()
    {
        // Get Users assigned to the project via WorkAssignment
        // assuming assignments map to Employees, which map to Users... 
        // Or simpler: assignments table likely has user_id or employee_id.
        // Let's assume we want users.
        // WorkAssignment: assignee_id (Employee/User), assignee_type.
        // This is polymorphic. Getting "members" as Users is tricky in one go.
        // Simplification: We will manual load in Controller or define a specific relation if possible.
        // Let's define a helper for now or just user 'assignments.assignee'.
        // Actually, for filters, we want a list of Users.
        // Let's stick to assignments for now and process in controller or add a getter.
        return $this->assignments()->with('assignee');
    }

    /**
     * Scope to filter projects based on User Visibility Rules.
     * 
     * Rule 1: 'public' -> Visible to all.
     * Rule 2: 'team_locked' -> Visible only if user/team assigned OR user is Admin.
     * Rule 3: 'stealth' -> Same as team_locked but hidden from general lists (handled here same way).
     * Rule 4: 'freelancer_mode' -> Should restrict heavily, but for Project List, if they have task, they see project.
     */
    public function repositories()
    {
        return $this->hasMany(\App\Models\GitRepository::class);
    }

    public function scopeVisibleTo(Builder $query, User $user, bool $forceMyView = false)
    {
        if (!$forceMyView && $user->hasRole(['Super Admin', 'Admin'])) {
            return $query; // God mode
        }

        return $query->where(function ($q) use ($user) {
            // 1. All Public Projects
            $q->where('visibility', 'public')
            
            // 2. OR Projects where User is explicitly assigned (Project Level)
              ->orWhereHas('assignments', function ($assign) use ($user) {
                  $assign->where('assignee_type', User::class)
                         ->where('assignee_id', $user->id);
              })
            
            // 3. OR Projects where User's Employee Profile is assigned (Project Level)
              ->orWhereHas('assignments', function ($assign) use ($user) {
                  $assign->where('assignee_type', \App\Models\Employee::class)
                         ->where('assignee_id', $user->employee_id);
              })

            // 4. OR Projects where User (as Employee) has assigned TASKS
              ->orWhereHas('tasks', function ($taskQuery) use ($user) {
                  $taskQuery->whereHas('assignees', function ($assigneeQuery) use ($user) {
                        $assigneeQuery->where('employees.id', $user->employee_id);
                  });
              });
        });
    }
}

