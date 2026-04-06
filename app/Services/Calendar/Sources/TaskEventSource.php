<?php

namespace App\Services\Calendar\Sources;

use App\Services\Calendar\CalendarEvent;
use App\Services\Calendar\CalendarEventSource;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TaskEventSource implements CalendarEventSource
{
    public function getEvents(Carbon $start, Carbon $end, User $user): Collection
    {
        // Fetch tasks assigned to the user
        // OR tasks created by user (if desired)
        // For now: "My Workload" = Assigned to me
        
        $employeeId = $user->employee_id; // Check if user is linked to an Employee record
        
        if (!$employeeId) {
             // Try to find employee by user_id
             $employee = \App\Models\Employee::where('user_id', $user->id)->first();
             $employeeId = $employee?->id;
        }

        if (!$employeeId) {
            return collect([]); // No employee record, no tasks assigned via Employee model
        }

        $tasks = Task::query()
            ->whereHas('assignees', function($q) use ($employeeId) {
                $q->where('employee_id', $employeeId);
            })
            ->where(function($q) use ($start, $end) {
                // Task overlaps with the requested range
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('due_date', [$start, $end])
                  ->orWhere(function($sub) use ($start, $end) {
                      $sub->where('start_date', '<', $start)
                          ->where('due_date', '>', $end);
                  });
            })
            ->with('project')
            ->get();

        return $tasks->map(function (Task $task) {
            $startDate = $task->start_date ? Carbon::parse($task->start_date) : Carbon::now();
            $endDate = $task->due_date ? Carbon::parse($task->due_date) : $startDate->copy()->addDay();
            
            // Prioritize colors
            $color = match($task->priority) {
                'High', 'Critical' => 'red',
                'Medium' => 'blue',
                default => 'gray'
            };

            // If done, fade it
            if ($task->status === 'Done' || $task->status === 'Completed') {
                $color = 'green';
            }

            return new CalendarEvent(
                id: 'task-' . $task->id,
                title: "Task: {$task->title} ({$task->project->name})",
                start: $startDate,
                end: $endDate,
                type: 'task',
                color: $color,
                allDay: true, // Tasks are generally day-based
                metadata: [
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'link' => route('projects.tasks.show', ['project' => $task->project_id, 'task' => $task->id])
                ]
            );
        });
    }
}
