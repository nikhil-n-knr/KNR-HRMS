<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Workflow;
use App\Models\WorkflowStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkflowArchitectController extends Controller
{
    public function index()
    {
        // Prioritize by BugTicket entity Type (Phase 11 Standard)
        $workflow = Workflow::with(['stages' => function($q) {
            $q->orderBy('stage_order');
        }])->where('entity_type', \App\Models\BugTicket::class)->first();

        if (!$workflow) {
            // Fallback by Name
            $workflow = Workflow::with(['stages' => function($q) {
                $q->orderBy('stage_order');
            }])->where('name', 'like', '%Bug Tracking%')->first();
        }

        if (!$workflow) {
            // Ultimate Fallback
            $workflow = Workflow::with(['stages' => function($q) {
                $q->orderBy('stage_order');
            }])->first();
        }

        $data = [
            'workflow' => $workflow,
            'teams' => Team::select('id', 'name')->get(),
            'roles' => \App\Models\Role::select('id', 'name')->get(),
            'users' => \App\Models\User::select('id', 'name')->get(),
        ];

        if (request()->wantsJson()) {
            return response()->json($data);
        }

        return \Inertia\Inertia::render('Project/Workflow/Architect', $data);
    }

    public function updateStage(Request $request, WorkflowStage $stage)
    {
        $request->validate([
            'name' => 'required|string',
            'assigned_team_id' => 'nullable|exists:teams,id',
            'role_id' => 'nullable|exists:roles,id',
            'auto_close_days' => 'nullable|integer|min:0',
            'requires_verification' => 'boolean',
            'is_client_visible' => 'boolean',
            'is_final' => 'boolean',
            'color' => 'nullable|string',
            'mentor_id' => 'nullable|exists:users,id',
            'approver_type' => 'nullable|string',
            'transition_rules' => 'nullable|array',
            'user_id' => 'nullable|exists:users,id',
            'team_id' => 'nullable|exists:teams,id',
            'department_id' => 'nullable|exists:departments,id'
        ]);

        $stage->update($request->all());

        return back()->with('success', 'Stage updated');
    }

    public function reorderStages(Request $request)
    {
        $request->validate([
            'stages' => 'required|array',
            'stages.*.id' => 'required|exists:workflow_stages,id',
            'stages.*.order' => 'required|integer',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->stages as $item) {
                WorkflowStage::where('id', $item['id'])->update(['stage_order' => $item['order']]);
            }
        });
        return response()->json(['message' => 'Stages reordered']);
    }

    public function storeStage(Request $request)
    {
        $request->validate([
            'workflow_id' => 'required|exists:workflows,id',
            'name' => 'required|string|max:255',
            'color' => 'nullable|string'
        ]);

        $lastOrder = WorkflowStage::where('workflow_id', $request->workflow_id)->max('stage_order') ?? 0;

        WorkflowStage::create([
            'workflow_id' => $request->workflow_id,
            'name' => $request->name,
            'color' => $request->color ?? '#6366f1',
            'stage_order' => $lastOrder + 1,
            'requires_verification' => (bool)$request->requires_verification,
            'is_client_visible' => (bool)$request->is_client_visible,
            'is_final' => (bool)$request->is_final,
            'mentor_id' => $request->mentor_id,
            'approver_type' => $request->approver_type,
            'transition_rules' => $request->transition_rules
        ]);

        return back()->with('success', 'Stage created');
    }

    public function deleteStage(WorkflowStage $stage)
    {
        $stage->delete();
        return back()->with('success', 'Stage deleted');
    }

    public function updatePeopleConfig(Request $request, WorkflowStage $stage)
    {
        $request->validate([
            'approver_type' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'role_id' => 'nullable|exists:roles,id',
            'team_id' => 'nullable|exists:teams,id',
            'stage_personnel' => 'nullable|array',
            'notify_client' => 'boolean',
            'auto_close_days' => 'nullable|integer|min:0',
            'requires_verification' => 'boolean',
            'mentor_id' => 'nullable|exists:users,id'
        ]);

        $stage->update([
            'approver_type' => $request->approver_type,
            'user_id' => $request->user_id,
            'role_id' => $request->role_id,
            'team_id' => $request->team_id,
            'stage_personnel' => $request->stage_personnel,
            'notify_client' => (bool)$request->notify_client,
            'auto_close_days' => $request->auto_close_days,
            'requires_verification' => (bool)$request->requires_verification,
            'mentor_id' => $request->mentor_id,
        ]);

        return response()->json(['message' => 'Personnel configuration updated']);
    }
}
