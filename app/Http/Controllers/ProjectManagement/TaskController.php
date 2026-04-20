<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stage_id' => 'required|exists:project_stages,id',
            'priority' => 'required|string',
            'module_id' => 'nullable|exists:project_modules,id',
            'sprint_id' => 'nullable|exists:sprints,id',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id',
            'scrum_points' => 'nullable|integer',
            'git_branch_url' => 'nullable|url',
            'git_pr_url' => 'nullable|url',
            'due_date' => 'nullable|date'
        ]);

        DB::transaction(function () use ($validated, $project, $request) {
            $task = $project->tasks()->create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'stage_id' => $validated['stage_id'],
                'priority' => $validated['priority'],
                'module_id' => $validated['module_id'] ?? null,
                'sprint_id' => $validated['sprint_id'] ?? null,
                'scrum_points' => $validated['scrum_points'] ?? 0,
                'git_branch_url' => $validated['git_branch_url'] ?? null,
                'git_pr_url' => $validated['git_pr_url'] ?? null,
                'due_date' => $validated['due_date'] ?? null,
                'created_by' => auth()->id()
            ]);

            if (!empty($validated['assignees'])) {
                $task->assignees()->sync($validated['assignees']);
            }

            $task->activities()->create([
                'user_id' => auth()->id(),
                'type' => 'create'
            ]);
        });

        return redirect()->back()->with('success', 'Task created.')->setStatusCode(303);
    }

    public function update(Request $request, Project $project, Task $task)
    {
        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'stage_id' => 'exists:project_stages,id',
            'priority' => 'string',
            'module_id' => 'nullable|exists:project_modules,id',
            'sprint_id' => 'nullable|exists:sprints,id',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id',
            'scrum_points' => 'nullable|integer',
            'git_branch_url' => 'nullable|url',
            'git_pr_url' => 'nullable|url',
            'due_date' => 'nullable|date',
            'total_efforts' => 'nullable|numeric',
            'is_locked' => 'nullable|boolean',
            // Extension Fields
            'extension' => 'nullable|array',
            'extension.hours_added' => 'numeric|min:0',
            'extension.days_added' => 'integer|min:0',
            'extension.reason' => 'string|required_with:extension',
            'extension.notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($validated, $task, $project) {
            $task->update(collect($validated)->except('extension')->toArray());

            if (isset($validated['assignees'])) {
                $task->assignees()->sync($validated['assignees']);
            }

            // Handle Extension if provided
            if (!empty($validated['extension'])) {
                $ext = $validated['extension'];
                $project->extensions()->create([
                    'task_id' => $task->id,
                    'type' => ($ext['hours_added'] > 0 && $ext['days_added'] > 0) ? 'both' : ($ext['hours_added'] > 0 ? 'effort' : 'time'),
                    'hours_added' => $ext['hours_added'] ?? 0,
                    'days_added' => $ext['days_added'] ?? 0,
                    'reason' => $ext['reason'],
                    'notes' => $ext['notes'] ?? null,
                    'created_by' => auth()->id()
                ]);
            }

            $task->activities()->create([
                'user_id' => auth()->id(),
                'type' => 'update'
            ]);
        });

        return redirect()->back()->with('success', 'Task updated.')->setStatusCode(303);
    }

    public function destroy(Project $project, Task $task)
    {
        $task->delete();
        return redirect()->back()->with('success', 'Task deleted.')->setStatusCode(303);
    }

    public function move(Request $request, Project $project, Task $task)
    {
        $request->validate(['stage_id' => 'required|exists:project_stages,id']);
        
        $oldStage = $task->stage;
        $task->update(['stage_id' => $request->stage_id]);
        $newStage = $task->fresh()->stage; // Reload to get new stage relation
        
        $task->activities()->create([
            'user_id' => auth()->id(),
            'type' => 'move',
            'details' => [
                'from' => $oldStage->name,
                'to' => $newStage->name
            ]
        ]);
        
        // Auto-Assign Rule
        if ($newStage->default_assignee_id) {
            // Check if already assigned
            if (!$task->assignees()->where('user_id', $newStage->default_assignee_id)->exists()) {
                $task->assignees()->attach($newStage->default_assignee_id);
                
                // Log this auto-assignment
                $task->activities()->create([
                    'user_id' => null, // System
                    'type' => 'system',
                    'details' => [
                        'message' => 'Auto-assigned to stage owner',
                        'assignee_id' => $newStage->default_assignee_id
                    ]
                ]);
            }
        }
        
        // Notify New Stage Owners
        // Users assigned to the new stage via 'project_stage_assignees'
        // We need to fetch User models from Employee IDs
        if ($newStage && $newStage->assignees->count() > 0) {
            $mover = auth()->user();
            
            // Get Users associated with these Employees
            // Assuming Employee model has 'user_id' or we notify via Employee -> User
            // Ideally Notification goes to User model.
            
            // Pluck user_ids from stage assignees (Employees)
            $userIds = $newStage->assignees->pluck('user_id')->filter()->unique();
            
            // Exclude current user to avoid self-notification
            $userIds = $userIds->reject(fn($id) => $id === $mover->id);
            
            if ($userIds->isNotEmpty()) {
                $users = \App\Models\User::whereIn('id', $userIds)->get();
                \Illuminate\Support\Facades\Notification::send($users, new \App\Notifications\Project\TaskMovedNotification($task, $oldStage, $newStage, $mover));
            }
        }
        
        return redirect()->back()->setStatusCode(303);
    }

    public function bulkUpdate(Request $request, Project $project)
    {
        $validated = $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:project_tasks,id',
            'sprint_id' => 'nullable|exists:sprints,id',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'exists:users,id'
        ]);

        DB::transaction(function () use ($validated, $project) {
            foreach ($validated['task_ids'] as $taskId) {
                $task = $project->tasks()->find($taskId);
                if (!$task) continue;

                // Move Sprint
                if (array_key_exists('sprint_id', $validated)) {
                    $task->update(['sprint_id' => $validated['sprint_id']]);
                    // Activity Log
                    $task->activities()->create([
                        'user_id' => auth()->id(),
                        'type' => 'update',
                        'details' => ['message' => 'Bulk moved to sprint']
                    ]);
                }

                // Bulk Assign
                if (isset($validated['assignee_ids'])) {
                    $task->assignees()->sync($validated['assignee_ids']);
                     $task->activities()->create([
                        'user_id' => auth()->id(),
                        'type' => 'update',
                        'details' => ['message' => 'Bulk assigned']
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', count($validated['task_ids']) . ' tasks updated.')->setStatusCode(303);
    }

    public function show(Request $request, Project $project, Task $task)
    {
        $task->load([
            'comments.author', 
            'checklists', 
            'activities.user', 
            'assignees', 
            'module', 
            'stage',
            'reporter'
        ]);
        
        // Fetch related PRs (Loose coupling via ID in title)
        $relatedPrs = \App\Models\GitPullRequest::where('title', 'LIKE', "%#{$task->id}%")
            ->orWhere('title', 'LIKE', "%Task-{$task->id}%")
            ->get();
            
        $task->related_pull_requests = $relatedPrs;
        
        return response()->json($task);
    }

    public function myTasks(Request $request)
    {
        $user = auth()->user();
        
        $query = Task::whereHas('assignees', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['project', 'stage', 'module', 'assignees']);

        // 1. Status Filter (Stage Names for Global View)
        if ($request->has('status') && !empty($request->status)) {
            $statuses = is_array($request->status) ? $request->status : explode(',', $request->status);
            $query->whereHas('stage', function($q) use ($statuses) {
                $q->whereIn('name', $statuses);
            });
        }

        // 2. Priority Filter
        if ($request->has('priority') && !empty($request->priority)) {
            $priorities = is_array($request->priority) ? $request->priority : explode(',', $request->priority);
            $query->whereIn('priority', $priorities);
        }

        // 3. Search
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $tasks = $query->orderBy('due_date', 'asc')
            ->paginate(20)
            ->withQueryString()
            ->through(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'project' => [
                        'id' => $task->project ? $task->project->id : null,
                        'name' => $task->project ? $task->project->name : 'Deleted Project',
                    ],
                    'stage' => [
                        'id' => $task->stage ? $task->stage->id : null,
                        'name' => $task->stage ? $task->stage->name : 'Unknown Stage',
                        'color' => $task->stage ? $task->stage->color : '#ccc',
                        'type' => $task->stage ? $task->stage->type : 'default'
                    ],
                    'priority' => $task->priority,
                    'due_date' => $task->due_date,
                    'scrum_points' => $task->scrum_points,
                    'assignees' => $task->assignees->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'avatar' => $a->profile_photo_url]),
                    'module' => $task->module ? ['id' => $task->module->id, 'name' => $task->module->name] : null
                ];
            });

        return Inertia::render('Project/Task/MyTasks', [
            'tasks' => $tasks
        ]);
    }
}
