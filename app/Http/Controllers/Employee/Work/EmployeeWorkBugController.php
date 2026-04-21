<?php

namespace App\Http\Controllers\Employee\Work;

use App\Http\Controllers\Controller;
use App\Models\BugTicket;
use App\Models\ProjectExtension;
use App\Models\WorkflowStage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeWorkBugController extends Controller
{
    public function updateStatus(Request $request, BugTicket $bug): JsonResponse
    {
        $this->authorize('updateOwnStatus', $bug);

        $validated = $request->validate([
            'stage_id' => 'required|exists:workflow_stages,id',
        ]);

        $stage = WorkflowStage::query()
            ->where('id', $validated['stage_id'])
            ->firstOrFail();

        $bug->workflow_stage_id = $stage->id;
        if (!$bug->started_at && stripos($stage->name, 'progress') !== false) {
            $bug->started_at = now();
        }
        if ($stage->is_final && !$bug->resolved_at) {
            $bug->resolved_at = now();
        }
        $bug->save();

        return response()->json([
            'message' => 'Bug status updated successfully.',
            'bug_id' => $bug->id,
            'stage' => [
                'id' => $stage->id,
                'name' => $stage->name,
            ],
        ]);
    }

    public function storeComment(Request $request, BugTicket $bug): JsonResponse
    {
        $this->authorize('comment', $bug);

        $validated = $request->validate([
            'body' => 'required|string',
            'is_public' => 'nullable|boolean',
        ]);

        $comment = $bug->comments()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
            'is_public' => $validated['is_public'] ?? true,
            'attachments' => [],
        ]);

        return response()->json([
            'message' => 'Comment added successfully.',
            'comment' => $comment->load('author'),
        ]);
    }

    public function submitDrift(Request $request, BugTicket $bug): JsonResponse
    {
        $this->authorize('submitDrift', $bug);

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
            'project_id' => $bug->project_id,
            'task_id' => $bug->task_id,
            'category' => $validated['category'],
            'type' => $typeMap[$validated['category']] ?? 'both',
            'days_added' => $daysAdded,
            'hours_added' => $hoursAdded,
            'reason' => $validated['reason'],
            'notes' => $validated['notes'] ?? null,
            'original_start_date' => optional($bug->started_at)->toDateString(),
            'original_end_date' => optional($bug->resolved_at)->toDateString(),
            'extended_end_date' => $validated['extended_end_date'] ?? optional($bug->resolved_at)->toDateString(),
            'extension_meta' => [
                'source' => 'employee_workbench',
                'entity' => 'bug',
                'entity_id' => $bug->id,
                'approval_status' => 'pending',
            ],
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Drift submitted for review.',
            'extension' => $extension,
        ]);
    }
}
