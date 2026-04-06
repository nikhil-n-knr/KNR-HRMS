<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LmsCourse;

class LmsCoursePolicy
{
    /**
     * Determine if user can view any courses
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('lms.view');
    }
    
    /**
     * Determine if user can view the course
     */
    public function view(User $user, LmsCourse $course): bool
    {
        // HR/Admin can view all
        if ($user->hasRole(['Admin', 'HR'])) {
            return true;
        }
        
        // Employees can view if assigned
        if ($user->employee) {
            return $course->assignments()
                ->where('employee_id', $user->employee_id)
                ->exists();
        }
        
        return false;
    }
    
    /**
     * Determine if user can create courses
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('lms.create');
    }
    
    /**
     * Determine if user can update the course
     */
    public function update(User $user, LmsCourse $course): bool
    {
        return $user->hasPermission('lms.edit');
    }
    
    /**
     * Determine if user can delete the course
     */
    public function delete(User $user, LmsCourse $course): bool
    {
        return $user->hasPermission('lms.delete');
    }
}
