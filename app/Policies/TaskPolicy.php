<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $this->isAssigned($user, $task);
    }

    public function updateOwnStatus(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    public function moveNextStage(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    public function comment(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    public function toggleChecklist(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    public function submitDrift(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    public function denyStructuralEdits(User $user, Task $task): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'Admin', 'Manager']);
    }

    private function isAssigned(User $user, Task $task): bool
    {
        $employeeId = $this->resolveEmployeeId($user);

        if ($task->assignments()
            ->where('assignee_type', User::class)
            ->where('assignee_id', $user->id)
            ->exists()) {
            return true;
        }

        if ($employeeId) {
            if ($task->assignees()->where('employees.id', $employeeId)->exists()) {
                return true;
            }

            if ($task->assignments()
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
