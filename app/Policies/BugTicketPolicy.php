<?php

namespace App\Policies;

use App\Models\BugTicket;
use App\Models\Employee;
use App\Models\User;

class BugTicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Admin', 'Manager']) || (bool) $user->employee_id;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BugTicket $bugTicket): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $this->isAssigned($user, $bugTicket);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BugTicket $bugTicket): bool
    {
        return $this->view($user, $bugTicket);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BugTicket $bugTicket): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BugTicket $bugTicket): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BugTicket $bugTicket): bool
    {
        return $this->isAdmin($user);
    }

    public function updateOwnStatus(User $user, BugTicket $bugTicket): bool
    {
        return $this->view($user, $bugTicket);
    }

    public function comment(User $user, BugTicket $bugTicket): bool
    {
        return $this->view($user, $bugTicket);
    }

    public function submitDrift(User $user, BugTicket $bugTicket): bool
    {
        return $this->view($user, $bugTicket);
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Admin', 'Manager']);
    }

    private function isAssigned(User $user, BugTicket $bugTicket): bool
    {
        $employeeId = $this->resolveEmployeeId($user);

        if ($bugTicket->assignee_type === User::class && (int) $bugTicket->assignee_id === (int) $user->id) {
            return true;
        }

        if ($bugTicket->assignees()
            ->where('assignee_type', User::class)
            ->where('assignee_id', $user->id)
            ->exists()) {
            return true;
        }

        if ($employeeId) {
            if ($bugTicket->assignee_type === Employee::class && (int) $bugTicket->assignee_id === (int) $employeeId) {
                return true;
            }

            if ($bugTicket->assignees()
                ->where('assignee_type', Employee::class)
                ->where('assignee_id', $employeeId)
                ->exists()) {
                return true;
            }
        }

        return false;
    }

    private function resolveEmployeeId(User $user): ?int
    {
        if ($user->employee) {
            return (int) $user->employee->id;
        }

        if ($user->employee_id) {
            $employee = Employee::query()->where('employee_code', $user->employee_id)->first();
            if ($employee) {
                return (int) $employee->id;
            }
        }

        return null;
    }
}
