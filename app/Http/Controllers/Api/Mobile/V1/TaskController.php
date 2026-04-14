<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskChecklist;
use App\Services\Infrastructure\LoggerService;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;
        
        $tasks = Task::with(['project', 'stage'])
            ->whereHas('assignees', fn($q) => $q->where('employee_id', $employee->id ?? 0))
            ->whereIn('status', ['In Progress', 'To Do', 'Wait'])
            ->orderByRaw("FIELD(status, 'In Progress', 'To Do', 'Wait')")
            ->latest()
            ->paginate(15);

        return response()->json($tasks);
    }

    public function show(Task $task)
    {
        $employee = auth()->user()->employee;
        if (!$task->assignees()->where('employee_id', $employee->id ?? 0)->exists()) {
            abort(403, 'Unauthorized.');
        }

        $task->load(['project', 'checklists', 'stage', 'comments.user']);

        return response()->json($task);
    }

    public function updateProgress(Request $request, Task $task)
    {
        $request->validate([
            'progress' => 'required|integer|min:0|max:100',
            'status' => 'nullable|string'
        ]);

        $employee = auth()->user()->employee;
        if (!$task->assignees()->where('employee_id', $employee->id ?? 0)->exists()) {
            abort(403, 'Unauthorized.');
        }

        $oldProgress = $task->progress;
        $task->update([
            'progress' => $request->progress,
            'status' => $request->status ?? $task->status
        ]);

        \Log::context(['task_id' => $task->id, 'action' => 'mobile_task_progress_update']);
        LoggerService::info("Task Progress Updated to {$request->progress}%", [
            'old_progress' => $oldProgress,
            'new_progress' => $request->progress
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Progress updated.',
            'task' => $task
        ]);
    }

    public function toggleChecklist(Request $request, TaskChecklist $checklist)
    {
        $task = $checklist->task;
        $employee = auth()->user()->employee;
        if (!$task->assignees()->where('employee_id', $employee->id ?? 0)->exists()) {
            abort(403, 'Unauthorized.');
        }

        $checklist->update([
            'is_completed' => !$checklist->is_completed,
            'completed_at' => !$checklist->is_completed ? now() : null
        ]);

        $total = $task->checklists()->count();
        $completed = $task->checklists()->where('is_completed', true)->count();
        $newProgress = $total > 0 ? round(($completed / $total) * 100) : $task->progress;
        
        $task->update(['progress' => $newProgress]);

        return response()->json([
            'success' => true,
            'checklist' => $checklist,
            'new_progress' => $newProgress
        ]);
    }
}
