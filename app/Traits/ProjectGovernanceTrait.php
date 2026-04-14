<?php

namespace App\Traits;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\Project\PlanOverwrittenNotification;
use Illuminate\Support\Facades\Notification;
use App\Services\Infrastructure\LoggerService;

trait ProjectGovernanceTrait
{
    /**
     * Check if project/task is locked and verify permissions.
     * Trigger notifications if an Admin bypasses a lock.
     */
    protected function checkLockAndNotify(Project $project, Task $task = null, $requestedChanges = [])
    {
        // 1. If not locked at either level, proceed freely
        if (!$project->is_locked && (!$task || !$task->is_locked)) {
            return;
        }

        $user = auth()->user();
        
        // 2. Multi-Level RBAC: Admins/Super-Admins can bypass WITH notification.
        // Others are strictly blocked.
        if (!$user->hasRole(['Admin', 'Super Admin'])) {
            throw new \Exception("The schedule for this " . ($task ? 'task' : 'project') . " is locked. Only Administrators can overwrite the plan.");
        }

        // 3. Trigger immediate alert if changes actually occurred
        if (!empty($requestedChanges)) {
            $this->notifyStakeholders($project, $task, $user, $requestedChanges);
        }
    }

    /**
     * Notify all relevant stakeholders about a planning override.
     */
    protected function notifyStakeholders(Project $project, Task $task, User $overwriter, $changes)
    {
        // 1. Log the event (using standard Logger if available)
        $logger = app(LoggerService::class);
        $logger->log('project_management', 'plan_overwrite_alert', "Plan overwritten for " . ($task ? $task->title : $project->name), [
            'by' => $overwriter->name,
            'changes' => $changes
        ], 'warning');

        // 2. Resolve Recipients
        // Default: All Admins & Super Admins
        $admins = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Admin', 'Super Admin']);
        })->get();

        // Configured: Specific users in project settings
        $extraIds = $project->plan_lock_recipients;
        $extraUsers = collect();
        if (!empty($extraIds) && is_array($extraIds)) {
            $extraUsers = User::whereIn('id', $extraIds)->get();
        }

        $recipients = $admins->merge($extraUsers)->unique('id');

        // 3. Send Notifications
        Notification::send($recipients, new PlanOverwrittenNotification($project, $task, $overwriter, $changes));
    }
}
