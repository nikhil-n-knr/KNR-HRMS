<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Project;
use App\Models\Task;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use App\Services\Infrastructure\LoggerService;
use App\Services\ProjectManagement\DependencyService;
use App\Notifications\Project\TaskMovedNotification;
use App\Models\ProjectStage;
use App\Models\WorkAssignment;
use Illuminate\Support\Facades\Notification;

class KanbanController extends Controller
{
    use \App\Traits\ProjectGovernanceTrait;
    protected $logger;
    protected $dependencyService;

    public function __construct(LoggerService $logger, DependencyService $dependencyService)
    {
        $this->logger = $logger;
        $this->dependencyService = $dependencyService;
    }

    public function index(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        // Ensure Default Stages Logic
        if ($project->stages()->count() === 0) {
            $defaults = [
                ['name' => 'To Do', 'type' => 'todo', 'color' => '#64748b'],
                ['name' => 'In Progress', 'type' => 'doing', 'color' => '#3b82f6'],
                ['name' => 'In Review', 'type' => 'review', 'color' => '#f59e0b'],
                ['name' => 'Done', 'type' => 'done', 'color' => '#10b981'],
            ];
            foreach ($defaults as $i => $d) {
                $project->stages()->create(array_merge($d, ['slug' => \Illuminate\Support\Str::slug($d['name']), 'order' => $i]));
            }
        }

        // Ensure Default Priorities Logic (omitted for brevity if unchanged, but keeping context safe)
        if ($project->priorities()->count() === 0) {
            $defaults = [
                ['name' => 'Low', 'color' => '#64748b'],
                ['name' => 'Medium', 'color' => '#3b82f6'],
                ['name' => 'High', 'color' => '#f97316'],
                ['name' => 'Critical', 'color' => '#ef4444']
            ];
            foreach ($defaults as $i => $d) {
                $project->priorities()->create(array_merge($d, ['order' => $i]));
            }
        }

        // Determine Sprint Context
        $sprintId = $request->query('sprint');
        $activeSprints = $project->sprints()->where('status', 'active')->get(); // Fetch All Active
        $activeSprint = $activeSprints->first(); // Default fallback
        
        $currentSprint = null;
        if ($sprintId === 'backlog') {
            $currentSprint = 'backlog';
        } elseif ($sprintId) {
            $currentSprint = $project->sprints()->find($sprintId);
        } else {
            $currentSprint = $activeSprint;
        }

        // Fetch tasks
        $query = $project->tasks()
            ->with(['assignees:id,first_name,last_name,avatar', 'reporter:id,name', 'stage', 'sprint', 'module.parent']) // Fixed columns: first_name, last_name
            ->withCount(['comments', 'checklists', 'checklists as completed_checklists_count' => function ($query) {
                $query->where('is_completed', true);
            }])
            ->orderBy('order')
            ->orderBy('id');

        if ($currentSprint === 'backlog') {
            // Explicitly showing backlog view - show only backlog tasks
            $query->where('is_backlog', true);
        } elseif ($currentSprint) {
            // Specific sprint selected - show sprint tasks that are NOT in backlog
            $query->where('sprint_id', $currentSprint->id)->where('is_backlog', false);
        } else {
            // Default board: show ALL tasks that are NOT marked as backlog
            $query->where('is_backlog', false);
        }

        $tasks = $query->get()->map(function ($task) {
            $task->assignees->each(function ($assignee) {
                $assignee->name = $assignee->first_name . ' ' . $assignee->last_name;
            });
            return $task;
        });

        // Load project relationships needed for the board
        $project->load([
            'stages.assignees', 
            'sprints',
            'modules' => fn($q) => $q->whereNull('parent_id')->with('childrenRecursive')
        ]);

        return Inertia::render('Project/Board', [
            'project' => $project,
            'tasks' => $tasks,
            'currentSprint' => $currentSprint,
            'activeSprints' => $activeSprints,
            'employees' => \App\Models\User::select('id', 'name')->with('employee:id,user_id,avatar')->whereHas('employee')->get()->map(function($u) {
                return ['id' => $u->id, 'name' => $u->name, 'avatar' => $u->employee ? $u->employee->avatar : null];
            }),
            'priorities' => $project->priorities()->orderBy('order')->get(),
            'modules' => $project->modules, // Pass already loaded relationship
            'taskTemplates' => $project->taskTemplates()->get(),
        ]);
    }

    public function store(Request $request, Project $project)
    {
        // Fix string 'null' issues from frontend selects
        $sprintId = $request->sprint_id;
        if (empty($sprintId) || $sprintId === 'null' || $sprintId === 'undefined' || strtolower((string)$sprintId) === 'backlog') {
            $sprintId = null;
        }
        
        $moduleId = $request->module_id;
        if (empty($moduleId) || $moduleId === 'null' || $moduleId === 'undefined') {
            $moduleId = null;
        }

        $request->merge([
            'sprint_id' => $sprintId,
            'module_id' => $moduleId,
        ]);

        if (empty($request->stage_id) || $request->stage_id === 'null') {
            $firstStage = $project->stages()->orderBy('order')->first();
            if ($firstStage) {
                $request->merge(['stage_id' => $firstStage->id]);
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stage_id' => 'required|exists:project_stages,id',
            'sprint_id' => 'nullable|exists:sprints,id',
            'priority' => 'nullable|string|max:100',
            'is_backlog' => 'nullable|boolean',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id',
            'scrum_points' => 'nullable|integer|min:0',
            'blocked_by_task_id' => 'nullable|exists:project_tasks,id',
            'git_branch_url' => 'nullable|url',
            'git_pr_url' => 'nullable|url',
            'qa_notes' => 'nullable|string',
            'module_id' => 'nullable|exists:project_modules,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ]);
        
        $validated['created_by'] = auth()->id();
        
        // Auto-assign order
        $maxOrder = $project->tasks()->where('stage_id', $validated['stage_id'])->max('order') ?? 0;
        $validated['order'] = $maxOrder + 1;
        
        // Check stage default assignee
        $stage = \App\Models\ProjectStage::find($validated['stage_id']);
        $validated['status'] = $this->mapStageToStatus($stage->type);

        // Calculate Estimated Hours if dates provided
        if (!empty($validated['start_date']) && !empty($validated['due_date'])) {
            $days = \Carbon\Carbon::parse($validated['start_date'])->diffInDays(\Carbon\Carbon::parse($validated['due_date'])) + 1;
            $validated['estimated_hours'] = $days * 8;
        }

        $task = $project->tasks()->create($validated);
        
        // Handle Assignees (Manual + Default)
        $assigneeIds = $validated['assignees'] ?? [];
        
        // Auto-assign default if empty
        if (empty($assigneeIds) && $stage->default_assignee_id) {
             // ensure default_assignee_id is a User ID (it should be, but let's assume standard)
             $assigneeIds[] = $stage->default_assignee_id; 
        }

        if (!empty($assigneeIds)) {
             // 1. Sync Legacy Employee Relation (Only for those who ARE employees)
             $employees = \App\Models\Employee::whereIn('user_id', $assigneeIds)->get();
             $task->assignees()->sync($employees->pluck('id'));

             // 2. Create WorkAssignments (Planner Compatibility) - Handle All Users
             foreach ($assigneeIds as $uid) {
                // Determine if User is Employee
                $emp = $employees->firstWhere('user_id', $uid);
                
                WorkAssignment::create([
                    'task_id' => $task->id,
                    'assignee_id' => $emp ? $emp->id : $uid,
                    'assignee_type' => $emp ? \App\Models\Employee::class : \App\Models\User::class,
                    'project_id' => $task->project_id,
                    'start_date' => $task->start_date,
                    'end_date' => $task->due_date
                ]);
             }
        }

        // System Log
        $this->logger->log('Kanban', 'Create', "Task '{$task->title}' created", [
            'project_id' => $project->id,
            'task_id' => $task->id
        ]);

        // Activity Log
        $task->activities()->create([
            'user_id' => auth()->id(),
            'type' => 'created',
            'details' => ['message' => 'Task created']
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Task created successfully', 'task' => $task->load('stage', 'sprint')]);
        }
        return redirect()->back()->with('success', 'Task created successfully.')->setStatusCode(303);
    }

    public function update(Request $request, Project $project, Task $task)
    {
        // Fix string 'null' issues from frontend selects
        $sprintId = $request->sprint_id;
        if (empty($sprintId) || $sprintId === 'null' || $sprintId === 'undefined' || strtolower((string)$sprintId) === 'backlog') {
            $sprintId = null;
        }
        
        $moduleId = $request->module_id;
        if (empty($moduleId) || $moduleId === 'null' || $moduleId === 'undefined') {
            $moduleId = null;
        }

        $request->merge([
            'sprint_id' => $sprintId,
            'module_id' => $moduleId,
        ]);

        if (empty($request->stage_id) || $request->stage_id === 'null') {
            $request->merge(['stage_id' => $task->stage_id]); // Fallback to current stage if auto-assign chosen
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'stage_id' => 'sometimes|required|exists:project_stages,id',
            'sprint_id' => 'nullable|exists:sprints,id',
            'priority' => 'nullable|string|max:100',
            'is_backlog' => 'nullable|boolean',
            'is_locked' => 'nullable|boolean',
            'total_efforts' => 'nullable|numeric|min:0',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id', 
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'scrum_points' => 'nullable|integer|min:0',
            'order' => 'nullable|integer',
            'blocked_by_task_id' => 'nullable|exists:project_tasks,id',
            'git_branch_url' => 'nullable|url',
            'git_pr_url' => 'nullable|url',
            'qa_notes' => 'nullable|string',
            'module_id' => 'nullable|exists:project_modules,id',
            'version' => 'nullable|integer'
        ]);

        if (isset($validated['stage_id'])) {
             $stage = \App\Models\ProjectStage::find($validated['stage_id']);
             $validated['status'] = $this->mapStageToStatus($stage->type);
        }

        // Circular Dependency Check
        if (!empty($validated['blocked_by_task_id'])) {
            $this->dependencyService->validateDependency($task->id, $validated['blocked_by_task_id']);
        }

        $task->fill($validated);

        // Track changes for notifications
        $changes = [];
        $monitoredFields = ['start_date', 'due_date', 'total_efforts'];
        foreach ($monitoredFields as $field) {
            if ($task->isDirty($field)) {
                $orig = $task->getOriginal($field);
                $curr = $task->$field;
                $changes[$field] = [
                    'old' => $orig instanceof \Carbon\Carbon ? $orig->format('Y-m-d') : $orig,
                    'new' => $curr instanceof \Carbon\Carbon ? $curr->format('Y-m-d') : $curr,
                ];
            }
        }

        // Check Lock & Notify (Trait Method)
        $this->checkLockAndNotify($project, $task, $changes);

        $task->save();
        
        if (isset($validated['assignees'])) {
            $assigneeIds = $validated['assignees'];
            
            // 1. Sync Legacy
            $employees = \App\Models\Employee::whereIn('user_id', $assigneeIds)->get();
            $task->assignees()->sync($employees->pluck('id'));
            
            // 2. Sync WorkAssignments (Delete & Recreate)
            $task->assignments()->delete();
            foreach ($assigneeIds as $uid) {
                $emp = $employees->firstWhere('user_id', $uid);

                WorkAssignment::create([
                    'task_id' => $task->id,
                    'assignee_id' => $emp ? $emp->id : $uid,
                    'assignee_type' => $emp ? \App\Models\Employee::class : \App\Models\User::class,
                    'project_id' => $task->project_id,
                    'start_date' => $task->start_date,
                    'end_date' => $task->due_date
                ]);
            }
        }
        
        // System Log
        $this->logger->log('Kanban', 'Update', "Task '{$task->title}' updated", [
            'project_id' => $project->id,
            'task_id' => $task->id,
            'changes' => array_keys($validated)
        ]);

        // Activity Log
        if (!empty($validated)) {
             $task->activities()->create([
                'user_id' => auth()->id(),
                'type' => 'update',
                'details' => ['fields' => array_keys($validated)]
            ]);
        }

        if ($request->wantsJson()) {
            $task->load(['stage', 'sprint', 'assignees']);
            $task->assignees->each(fn($a) => $a->name = trim(($a->first_name ?? '') . ' ' . ($a->last_name ?? '')));
            return response()->json(['message' => 'Task updated successfully', 'task' => $task]);
        }
        return redirect()->back()->with('success', 'Task updated successfully.')->setStatusCode(303);
    }

    public function destroy(Request $request, Project $project, Task $task)
    {
        $id = $task->id;
        $title = $task->title;
        $task->delete();
        
        $this->logger->log('Kanban', 'Delete', "Task '{$title}' deleted", [
            'project_id' => $project->id,
            'task_id' => $id
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Task deleted successfully']);
        }
        return redirect()->back()->with('success', 'Task deleted successfully.')->setStatusCode(303);
    }
    
    public function reorder(Request $request, Project $project)
    {
        $request->validate([
            'stage_id' => 'required|exists:project_stages,id',
            'taskIds' => 'required|array',
            'taskIds.*' => 'exists:project_tasks,id'
        ]);

        $stageId = $request->stage_id;
        $ids = $request->taskIds;

        $stage = \App\Models\ProjectStage::find($stageId);
        $status = $this->mapStageToStatus($stage->type);

        DB::transaction(function() use ($ids, $stageId, $status) {
            foreach ($ids as $index => $id) {
                Task::where('id', $id)->update([
                    'stage_id' => $stageId,
                    'status' => $status,
                    'order' => $index
                ]);
            }
        });

        return redirect()->back()->setStatusCode(303);
    }

    public function move(Request $request, Project $project, Task $task)
    {
        $validated = $request->validate([
            'stage_id' => 'required|exists:project_stages,id',
            'order' => 'nullable|integer'
        ]);

        $stage = \App\Models\ProjectStage::find($validated['stage_id']);
        $validated['status'] = $this->mapStageToStatus($stage->type);

        // Auto-assign Logic (From TaskController)
        if ($stage->default_assignee_id && !$task->assignees->contains($stage->default_assignee_id)) {
            $task->assignees()->attach($stage->default_assignee_id);
             $this->logger->log('Kanban', 'AutoAssign', "Task '{$task->title}' auto-assigned to user {$stage->default_assignee_id}", [
                'project_id' => $project->id, 
                'task_id' => $task->id
            ]);
        }
        
        // Handle Order if not provided
        if (!isset($validated['order'])) {
            $maxOrder = $project->tasks()->where('stage_id', $validated['stage_id'])->max('order') ?? 0;
            $validated['order'] = $maxOrder + 1;
        }

        $oldStageId = $task->stage_id;
        $oldStage = ProjectStage::find($oldStageId);

        $task->update($validated);
        
        // Log Move
        $activity = $task->activities()->create([
            'user_id' => auth()->id(),
            'type' => 'moved',
            'details' => [
                'from_stage_id' => $oldStageId, 
                'from_stage_name' => $oldStage?->name ?? 'Unknown',
                'to_stage_id' => $stage->id,
                'to_stage_name' => $stage->name
            ]
        ]);
        
        // Notify Stage Owners
        $stage->load('assignees');
        $ownersToNotify = $stage->assignees->filter(function($employee) {
            return $employee->pivot->notify_on_entry;
        })->map(function($employee) {
            return $employee->user;
        })->filter();

        if ($ownersToNotify->isNotEmpty()) {
            Notification::send($ownersToNotify, new TaskMovedNotification(
                $task, 
                $oldStage ?? $stage, // Fallback to current if old not found
                $stage, 
                auth()->user()
            ));
        }

        return redirect()->back()->setStatusCode(303);
    }

    public function activityLog(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $query = \App\Models\TaskActivity::whereHas('task', fn($q) => $q->where('project_id', $project->id))
            ->with(['user', 'task'])
            ->latest();

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('task', fn($t) => $t->where('title', 'like', "%{$search}%"))
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        // Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $activities = $query->paginate(50);

        if ($request->wantsJson()) {
            return response()->json($activities);
        }

        return $activities;
    }

    public function exportActivityLog(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $query = \App\Models\TaskActivity::whereHas('task', fn($q) => $q->where('project_id', $project->id))
            ->with(['user', 'task'])
            ->latest();

        // Apply filters same as activityLog
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('task', fn($t) => $t->where('title', 'like', "%{$search}%"))
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('start_date')) $query->whereDate('created_at', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->whereDate('created_at', '<=', $request->end_date);

        $activities = $query->get();

        $filename = "project_{$project->id}_activity_" . now()->format('Y-m-d') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($activities) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'User', 'Task ID', 'Task Title', 'Action', 'Details']);

            foreach ($activities as $act) {
                $detailsText = "";
                if ($act->type == 'moved') {
                    $detailsText = ($act->details['from_stage_name'] ?? 'Unknown') . " -> " . ($act->details['to_stage_name'] ?? 'Unknown');
                } else {
                    $detailsText = json_encode($act->details);
                }

                fputcsv($file, [
                    $act->created_at->format('Y-m-d H:i:s'),
                    $act->user->name ?? 'System',
                    $act->task->id ?? 'N/A',
                    $act->task->title ?? 'N/A',
                    strtoupper($act->type),
                    $detailsText
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show(Project $project, Task $task)
    {
        $task->load([
            'assignees', 
            'reporter', 
            'stage', 
            'sprint', 
            'module', 
            'comments.author', 
            'checklists',
            'activities.user',
            'dependencies',
            'dependents',
            'pullRequests.user'
        ]);

        $task->assignees->each(function ($assignee) {
            $assignee->name = trim(($assignee->first_name ?? '') . ' ' . ($assignee->last_name ?? ''));
        });
        
        return response()->json($task);
    }

    /**
     * Mark task as backlog (hide from board).
     */
    public function moveToBacklog(Request $request, Project $project, Task $task)
    {
        $task->update(['is_backlog' => true]);

        $task->activities()->create([
            'user_id' => auth()->id(),
            'type'    => 'moved_to_backlog',
            'details' => ['action' => 'Moved to Backlog']
        ]);

        $this->logger->log('Kanban', 'Backlog', "Task '{$task->title}' moved to backlog", [
            'project_id' => $project->id,
            'task_id'    => $task->id,
        ]);

        return response()->json(['message' => 'Task moved to backlog', 'is_backlog' => true]);
    }

    /**
     * Restore task from backlog back to board.
     */
    public function restoreFromBacklog(Request $request, Project $project, Task $task)
    {
        $task->update(['is_backlog' => false]);

        $task->activities()->create([
            'user_id' => auth()->id(),
            'type'    => 'restored_from_backlog',
            'details' => ['action' => 'Restored to Board']
        ]);

        $this->logger->log('Kanban', 'Backlog', "Task '{$task->title}' restored from backlog", [
            'project_id' => $project->id,
            'task_id'    => $task->id,
        ]);

        return response()->json(['message' => 'Task restored to board', 'is_backlog' => false]);
    }

    private function mapStageToStatus($type) {
        return match($type) {
            'todo', 'backlog' => 'To Do',
            'doing' => 'In Progress',
            'review' => 'In Review',
            'done' => 'Done',
            default => 'To Do'
        };
    }
}
