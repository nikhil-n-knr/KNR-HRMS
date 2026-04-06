<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskChecklist;
use Illuminate\Http\Request;

class TaskChecklistController extends Controller
{
    protected $logger;

    public function __construct(\App\Services\Infrastructure\LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:255',
        ]);

        $checklist = $task->checklists()->create([
            'content' => $validated['content'],
            'position' => $task->checklists()->count() + 1
        ]);
        
        $this->logger->log('Task', 'Checklist Add', "Added checklist item '{$validated['content']}'", ['task_id' => $task->id]);

        return response()->json($checklist);
    }

    public function update(Request $request, TaskChecklist $checklist)
    {
        $validated = $request->validate([
            'content' => 'nullable|string|max:255',
            'is_completed' => 'nullable|boolean',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $checklist->update($validated);

        return response()->json($checklist);
    }

    public function destroy(TaskChecklist $checklist)
    {
        $checklist->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function toggle(TaskChecklist $checklist)
    {
        $checklist->update(['is_completed' => !$checklist->is_completed]);
        return response()->json($checklist);
    }

    public function cloneFromTask(Request $request, Task $task)
    {
        $validated = $request->validate([
            'source_task_id' => 'required|exists:project_tasks,id'
        ]);

        $sourceTask = Task::with('checklists')->findOrFail($validated['source_task_id']);
        
        $newItems = [];
        foreach ($sourceTask->checklists as $item) {
            $newItems[] = $task->checklists()->create([
                'content' => $item->content,
                'is_completed' => false,
                'position' => $item->position
            ]);
        }

        $this->logger->log('Task', 'Checklist Clone', "Cloned " . count($newItems) . " checklist items from task #{$sourceTask->id}", ['task_id' => $task->id, 'source_task_id' => $sourceTask->id]);

        return response()->json($task->load('checklists')->checklists);
    }

    public function importFile(Request $request, Task $task)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx'
        ]);

        $file = $request->file('file');
        $items = [];

        if ($file->getClientOriginalExtension() === 'csv' || $file->getClientOriginalExtension() === 'txt') {
            $handle = fopen($file->getPathname(), 'r');
            while (($data = fgetcsv($handle)) !== false) {
                if (isset($data[0]) && trim($data[0])) {
                    $items[] = trim($data[0]);
                }
            }
            fclose($handle);
        } else {
             return response()->json(['message' => 'Currently only .csv and .txt are supported for direct parsing without office-libs.'], 422);
        }

        $created = [];
        foreach ($items as $content) {
            $created[] = $task->checklists()->create([
                'content' => $content,
                'position' => $task->checklists()->count() + 1
            ]);
        }

        $this->logger->log('Task', 'Checklist Import', "Imported " . count($created) . " items from file", ['task_id' => $task->id]);

        return response()->json($task->load('checklists')->checklists);
    }
}
