<?php

namespace App\Http\Controllers\Employee\Work;

use App\Http\Controllers\Controller;
use App\Models\ProjectExtension;
use App\Models\ProjectStage;
use App\Models\Task;
use App\Models\TaskChecklist;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EmployeeWorkTaskController extends Controller
{
    private ?array $checklistColumns = null;

    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $this->authorize('updateOwnStatus', $task);

        $validated = $request->validate([
            'stage_id' => 'required|exists:project_stages,id',
        ]);

        $stage = ProjectStage::query()
            ->where('id', $validated['stage_id'])
            ->where('project_id', $task->project_id)
            ->firstOrFail();

        $task->stage_id = $stage->id;
        $task->status = $stage->type;
        $task->save();

        $task->activities()->create([
            'user_id' => auth()->id(),
            'type' => 'move',
            'details' => [
                'to_stage' => $stage->name,
                'source' => 'employee_workbench',
            ],
        ]);

        return response()->json([
            'message' => 'Task status updated successfully.',
            'task_id' => $task->id,
            'stage' => [
                'id' => $stage->id,
                'name' => $stage->name,
                'type' => $stage->type,
            ],
        ]);
    }

    public function moveNext(Task $task): JsonResponse
    {
        $this->authorize('moveNextStage', $task);

        $nextStage = ProjectStage::query()
            ->where('project_id', $task->project_id)
            ->where('order', '>', $task->stage?->order ?? -1)
            ->orderBy('order')
            ->first();

        if (!$nextStage) {
            return response()->json(['message' => 'No next stage is available for this task.'], 422);
        }

        $task->stage_id = $nextStage->id;
        $task->status = $nextStage->type;
        $task->save();

        $task->activities()->create([
            'user_id' => auth()->id(),
            'type' => 'move',
            'details' => [
                'to_stage' => $nextStage->name,
                'source' => 'employee_workbench',
            ],
        ]);

        return response()->json([
            'message' => 'Task moved to next stage.',
            'task_id' => $task->id,
            'stage' => [
                'id' => $nextStage->id,
                'name' => $nextStage->name,
                'type' => $nextStage->type,
            ],
        ]);
    }

    public function toggleChecklist(Request $request, Task $task, TaskChecklist $checklist): JsonResponse
    {
        $this->authorize('toggleChecklist', $task);

        if ((bool) $task->is_locked) {
            return response()->json([
                'message' => 'Task is locked. Checklist updates are disabled.',
            ], 423);
        }

        if ((int) $checklist->task_id !== (int) $task->id) {
            abort(404);
        }

        if (!empty($checklist->assigned_to) && (int) $checklist->assigned_to !== (int) auth()->id()) {
            abort(403, 'This checklist item is assigned to another user.');
        }

        $validated = $request->validate([
            'is_completed' => 'nullable|boolean',
            'actual_minutes' => 'nullable|integer|min:0|max:2880',
            'work_date' => 'nullable|date',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
        ]);

        $isCompleted = array_key_exists('is_completed', $validated)
            ? (bool) $validated['is_completed']
            : (bool) $checklist->is_completed;

        $updates = [
            'is_completed' => $isCompleted,
        ];

        if (array_key_exists('actual_minutes', $validated) && $this->checklistColumnExists('actual_minutes')) {
            $updates['actual_minutes'] = $validated['actual_minutes'];
        }

        if (array_key_exists('work_date', $validated) && $this->checklistColumnExists('work_date')) {
            $updates['work_date'] = $validated['work_date'];
        }

        if ($isCompleted && $this->checklistColumnExists('work_date') && empty($updates['work_date']) && empty($checklist->work_date)) {
            $updates['work_date'] = now()->toDateString();
        }

        if ($this->checklistColumnExists('started_at')) {
            if (!empty($validated['started_at'])) {
                $updates['started_at'] = Carbon::parse($validated['started_at']);
            } elseif ($isCompleted && empty($checklist->started_at)) {
                $updates['started_at'] = now();
            }
        }

        if ($this->checklistColumnExists('completed_at')) {
            $updates['completed_at'] = $isCompleted
                ? (!empty($validated['completed_at']) ? Carbon::parse($validated['completed_at']) : now())
                : null;
        }

        if ($this->checklistColumnExists('completed_by')) {
            $updates['completed_by'] = $isCompleted ? auth()->id() : null;
        }

        $checklist->fill($updates);
        $checklist->save();

        return response()->json([
            'message' => 'Checklist item updated.',
            'checklist' => [
                'id' => $checklist->id,
                'task_id' => $checklist->task_id,
                'content' => $checklist->content,
                'is_completed' => (bool) $checklist->is_completed,
                'planned_minutes' => $checklist->planned_minutes,
                'actual_minutes' => $checklist->actual_minutes,
                'work_date' => $checklist->work_date,
                'started_at' => $checklist->started_at,
                'completed_at' => $checklist->completed_at,
            ],
        ]);
    }

    public function listChecklist(Task $task, Request $request): JsonResponse
    {
        $this->authorize('toggleChecklist', $task);

        $validated = $request->validate([
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:10|max:100',
            'q' => 'nullable|string|max:255',
        ]);

        $perPage = (int) ($validated['per_page'] ?? 50);

        $query = $task->checklists()
            ->select($this->safeChecklistSelectColumns())
            ->orderBy('position')
            ->orderBy('id');

        if (!empty($validated['q'])) {
            $query->where('content', 'like', '%' . trim($validated['q']) . '%');
        }

        $items = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'task_id' => $task->id,
            'items' => $items,
            'summary' => [
                'total' => (int) $task->checklists()->count(),
                'completed' => (int) $task->checklists()->where('is_completed', true)->count(),
                'planned_minutes_total' => $this->checklistColumnExists('planned_minutes')
                    ? (int) ($task->checklists()->sum('planned_minutes') ?? 0)
                    : 0,
                'actual_minutes_total' => $this->checklistColumnExists('actual_minutes')
                    ? (int) ($task->checklists()->sum('actual_minutes') ?? 0)
                    : 0,
            ],
        ]);
    }

    public function storeChecklist(Request $request, Task $task): JsonResponse
    {
        $this->authorize('toggleChecklist', $task);

        $validated = $request->validate([
            'content' => 'required|string|max:500',
            'planned_minutes' => 'nullable|integer|min:0|max:2880',
        ]);

        $payload = [
            'content' => trim($validated['content']),
            'position' => ((int) $task->checklists()->max('position')) + 1,
            'is_completed' => false,
        ];

        if ($this->checklistColumnExists('planned_minutes')) {
            $payload['planned_minutes'] = $validated['planned_minutes'] ?? null;
        }

        $item = $task->checklists()->create($payload);

        return response()->json([
            'message' => 'Checklist item added.',
            'item' => $item,
        ], 201);
    }

    public function storeChecklistBulk(Request $request, Task $task): JsonResponse
    {
        $this->authorize('toggleChecklist', $task);

        $validated = $request->validate([
            'items' => 'required|array|min:1|max:500',
            'items.*.content' => 'required|string|max:500',
            'items.*.planned_minutes' => 'nullable|integer|min:0|max:2880',
        ]);

        $position = ((int) $task->checklists()->max('position')) + 1;
        $created = [];

        foreach ($validated['items'] as $item) {
            $payload = [
                'content' => trim((string) $item['content']),
                'position' => $position++,
                'is_completed' => false,
            ];

            if ($this->checklistColumnExists('planned_minutes')) {
                $payload['planned_minutes'] = $item['planned_minutes'] ?? null;
            }

            $created[] = $task->checklists()->create($payload);
        }

        return response()->json([
            'message' => count($created) . ' checklist items added.',
            'count' => count($created),
        ], 201);
    }

    public function storeComment(Request $request, Task $task): JsonResponse
    {
        $this->authorize('comment', $task);

        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $comment = $task->comments()->create([
            'body' => $validated['body'],
            'user_id' => auth()->id(),
            'attachments' => [],
        ]);

        return response()->json([
            'message' => 'Comment added successfully.',
            'comment' => $comment->load('author'),
        ]);
    }

    public function submitDrift(Request $request, Task $task): JsonResponse
    {
        $this->authorize('submitDrift', $task);

        $validated = $request->validate([
            'category' => 'required|in:priority_conflict,scope_change,complexity_drag',
            'days_added' => 'required|integer|min:0',
            'hours_added' => 'required|numeric|min:0',
            'reason' => 'required|string|min:5',
            'notes' => 'nullable|string',
            'extended_end_date' => 'nullable|date',
        ]);

        $daysAdded = (int) $validated['days_added'];
        $hoursAdded = (float) $validated['hours_added'];

        if ($daysAdded === 0 && $hoursAdded === 0.0) {
            return response()->json([
                'message' => 'Drift must include additional days or hours.',
            ], 422);
        }

        $typeMap = [
            'priority_conflict' => 'time',
            'scope_change' => 'both',
            'complexity_drag' => 'both',
        ];

        $extension = ProjectExtension::query()->create([
            'project_id' => $task->project_id,
            'task_id' => $task->id,
            'category' => $validated['category'],
            'type' => $typeMap[$validated['category']] ?? 'both',
            'days_added' => $daysAdded,
            'hours_added' => $hoursAdded,
            'reason' => $validated['reason'],
            'notes' => $validated['notes'] ?? null,
            'original_start_date' => $task->start_date,
            'original_end_date' => $task->due_date,
            'extended_end_date' => $validated['extended_end_date'] ?? $task->due_date,
            'extension_meta' => [
                'source' => 'employee_workbench',
                'entity' => 'task',
                'entity_id' => $task->id,
                'approval_status' => 'pending',
            ],
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Drift submitted for review.',
            'extension' => $extension,
        ]);
    }

    private function checklistColumnExists(string $column): bool
    {
        if ($this->checklistColumns === null) {
            $this->checklistColumns = Schema::getColumnListing('task_checklists');
        }

        return in_array($column, $this->checklistColumns, true);
    }

    private function safeChecklistSelectColumns(): array
    {
        $columns = ['id', 'task_id', 'content', 'is_completed', 'position', 'created_at', 'updated_at'];
        $optional = ['planned_minutes', 'actual_minutes', 'work_date', 'started_at', 'completed_at', 'completed_by', 'assigned_to'];

        foreach ($optional as $column) {
            if ($this->checklistColumnExists($column)) {
                $columns[] = $column;
            }
        }

        return $columns;
    }
}
