<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        // 1. Super Admin / Admin
        if ($user->hasRole(['Super Admin', 'Admin'])) {
            return true;
        }

        // 2. Public Projects
        if ($project->visibility === 'public') {
            return true;
        }

        // 3. User is explicitly assigned (Project Level)
        // Check assignments relation (WorkAssignment) matching User ID
        if ($project->assignments()->where('assignee_type', User::class)->where('assignee_id', $user->id)->exists()) {
             return true;
        }
        
        // 4. User's Employee Profile is assigned (Project Level)
        if ($user->employee_id && $project->assignments()->where('assignee_type', \App\Models\Employee::class)->where('assignee_id', $user->employee_id)->exists()) {
             return true;
        }

        // 5. User has ANY task assigned in this project
        // Check tasks -> assignees (Employee)
        if ($user->employee_id && $project->tasks()->whereHas('assignees', function($q) use ($user) {
             $q->where('id', $user->employee_id);
        })->exists()) {
             return true;
        }
        
        // 6. User has task assigned via User model (Legacy/Freelancer)
        // Check tasks -> assignments (WorkAssignment)
        if ($project->tasks()->whereHas('assignments', function($q) use ($user) {
             $q->where('assignee_type', User::class)->where('assignee_id', $user->id);
        })->exists()) {
             return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Admin', 'Manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin'])) return true;
        // Project Manager logic could go here
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->hasRole(['Super Admin', 'Admin']);
    }
}
