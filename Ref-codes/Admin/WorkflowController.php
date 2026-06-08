<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use App\Models\WorkflowStage;
use App\Models\Role;
use App\Models\Team;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class WorkflowController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $workflows = Workflow::with(['stages', 'stages.role', 'stages.user', 'stages.team', 'stages.department'])->get();
        $roles = Role::where('tenant_id', $tenantId)->get();
        $users = User::where('tenant_id', $tenantId)->get(['id', 'name']);
        $teams = Team::get(['id', 'name']);
        $departments = Department::where('tenant_id', $tenantId)->get(['id', 'name']);
        
        return Inertia::render('Admin/Attendance/Hub', [
            'tab' => 'workflows',
            'workflows' => $workflows,
            'roles' => $roles,
            'users' => $users,
            'teams' => $teams,
            'departments' => $departments
        ]);
    }

    public function initDefaults()
    {
        // 1. Timesheet
        if (!Workflow::where('entity_type', 'timesheet')->exists()) {
            $this->createTimesheetWorkflow();
        }

        // 2. Attendance Regularization
        if (!Workflow::where('entity_type', 'attendance_regularization')->exists()) {
            $this->createAttendanceWorkflow();
        }

        // 3. Leave Request
        if (!Workflow::where('entity_type', 'leave_request')->exists()) {
            $this->createLeaveWorkflow();
        }

        // 4. Expenses
        if (!Workflow::where('entity_type', 'expense')->exists()) {
            $this->createExpenseWorkflow();
        }

        // 5. Shift Swap
        if (!Workflow::where('entity_type', 'shift_swap')->exists()) {
             $this->createSwapWorkflow();
        }
        
        return back()->with('success', 'Default workflows initialized successfully');
    }

    public function store(Request $request) 
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'entity_type' => 'required|string',
            'trigger_event' => 'nullable|string',
            'approved_status' => 'nullable|string',
            'rejected_status' => 'nullable|string',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        Workflow::create($validated);
        
        return back()->with('success', 'Workflow created');
    }
    
    public function update(Request $request, Workflow $workflow)
    {
        $workflow->update($request->only('name', 'description', 'is_active', 'entity_type', 'approved_status', 'rejected_status'));
        return back()->with('success', 'Workflow Updated');
    }

    public function addStage(Request $request, Workflow $workflow)
    {
        $request->validate([
            'name' => 'required|string',
            'approver_type' => 'required|in:manager,team_lead,role,specific_user,department_head,team,department',
            'role_id' => 'required_if:approver_type,role|nullable|exists:roles,id',
            'user_id' => 'required_if:approver_type,specific_user|nullable|exists:users,id',
            'team_id' => 'required_if:approver_type,team|nullable|exists:teams,id',
            'department_id' => 'required_if:approver_type,department|nullable|exists:departments,id',
            'additional_approvers' => 'nullable|array',
            'approval_strategy' => 'nullable|in:all_must_approve,any_can_approve',
            'allow_self_approval' => 'boolean'
        ]);

        // Auto calculate order
        $order = $workflow->stages()->max('stage_order') + 1;

        $workflow->stages()->create([
            'name' => $request->name,
            'stage_order' => $order,
            'approver_type' => $request->approver_type,
            'role_id' => $request->role_id,
            'user_id' => $request->user_id,
            'team_id' => $request->team_id,
            'department_id' => $request->department_id,
            'additional_approvers' => $request->additional_approvers,
            'is_parallel' => !empty($request->additional_approvers),
            'approval_strategy' => $request->approval_strategy ?? 'all_must_approve',
            'allow_self_approval' => $request->allow_self_approval ?? true,
        ]);

        return back()->with('success', 'Stage Added');
    }

    public function updateStage(Request $request, WorkflowStage $stage)
    {
        $request->validate([
            'name' => 'required|string',
            'approver_type' => 'required|in:manager,team_lead,role,specific_user,department_head,team,department',
            'role_id' => 'required_if:approver_type,role|nullable|exists:roles,id',
            'user_id' => 'required_if:approver_type,specific_user|nullable|exists:users,id',
            'team_id' => 'required_if:approver_type,team|nullable|exists:teams,id',
            'department_id' => 'required_if:approver_type,department|nullable|exists:departments,id',
            'additional_approvers' => 'nullable|array',
            'approval_strategy' => 'nullable|in:all_must_approve,any_can_approve',
            'allow_self_approval' => 'boolean'
        ]);

        $stage->update([
            'name' => $request->name,
            'approver_type' => $request->approver_type,
            'role_id' => $request->role_id,
            'user_id' => $request->user_id,
            'team_id' => $request->team_id,
            'department_id' => $request->department_id,
            'additional_approvers' => $request->additional_approvers,
            'is_parallel' => !empty($request->additional_approvers),
            'approval_strategy' => $request->approval_strategy ?? 'all_must_approve',
            'allow_self_approval' => $request->allow_self_approval ?? true,
        ]);

        return back()->with('success', 'Stage Updated');
    }
    
    public function removeStage(Request $request, WorkflowStage $stage)
    {
        $stage->delete();
        return back()->with('success', 'Stage Removed');
    }

    public function reorderStages(Request $request, Workflow $workflow)
    {
        $request->validate([
            'stages' => 'required|array',
            'stages.*.id' => 'required|exists:workflow_stages,id',
            'stages.*.stage_order' => 'required|integer'
        ]);

        foreach ($request->stages as $item) {
            $workflow->stages()->where('id', $item['id'])->update(['stage_order' => $item['stage_order']]);
        }

        return back()->with('success', 'Order Updated');
    }

    public function destroy(Workflow $workflow)
    {
        $workflow->delete();
        return back()->with('success', 'Workflow deleted');
    }

    public function clone(Request $request, Workflow $workflow)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'entity_type' => 'required|string'
        ]);

        DB::transaction(function() use ($workflow, $validated) {
            $newFlow = $workflow->replicate();
            $newFlow->name = $validated['name'];
            $newFlow->entity_type = $validated['entity_type'];
            $newFlow->is_active = false; // Default to inactive for safety
            $newFlow->tenant_id = auth()->user()->tenant_id;
            $newFlow->save();

            foreach ($workflow->stages as $stage) {
                $newStage = $stage->replicate();
                $newStage->workflow_id = $newFlow->id;
                $newStage->save();
            }
        });

        return back()->with('success', 'Workflow Protocol Cloned and Migrated');
    }

    // Helper methods for creating default workflows
    private function createTimesheetWorkflow()
    {
        $workflow = Workflow::create([
            'name' => 'Timesheet Approval',
            'description' => 'Standard timesheet approval flow',
            'entity_type' => 'timesheet',
            'trigger_event' => 'on_submit',
            'is_active' => true,
            'priority' => 1,
            'tenant_id' => auth()->user()->tenant_id
        ]);

        WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'name' => 'Manager Review',
            'stage_order' => 1,
            'approver_type' => 'manager',
            'can_reject' => true
        ]);

        WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'name' => 'HR Approval',
            'stage_order' => 2,
            'approver_type' => 'role',
            'role_id' => Role::where('name', 'HR')->first()?->id,
            'can_reject' => true
        ]);
    }

    private function createExpenseWorkflow()
    {
        $workflow = Workflow::create([
            'name' => 'Expense Reimbursement',
            'description' => 'Approval flow for employee expense claims',
            'entity_type' => 'expense',
            'trigger_event' => 'on_submit',
            'is_active' => true,
            'priority' => 1,
            'tenant_id' => auth()->user()->tenant_id
        ]);

        WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'name' => 'Department Head',
            'stage_order' => 1,
            'approver_type' => 'department_head',
            'can_reject' => true
        ]);

        WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'name' => 'Finance Audit',
            'stage_order' => 2,
            'approver_type' => 'role',
            'role_id' => Role::where('name', 'Finance')->orWhere('name', 'Admin')->first()?->id,
            'can_reject' => true
        ]);
    }

    private function createSwapWorkflow()
    {
        $workflow = Workflow::create([
            'name' => 'Shift Swap Protocol',
            'description' => 'Peer-to-Peer swap verification',
            'entity_type' => 'shift_swap',
            'trigger_event' => 'on_request',
            'is_active' => true,
            'priority' => 1,
            'tenant_id' => auth()->user()->tenant_id
        ]);

        WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'name' => 'Counterpart Consent',
            'stage_order' => 1,
            'approver_type' => 'specific_user', // Logic usually handles this dynamically, but we define the stage
            'can_reject' => true
        ]);

        WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'name' => 'Manager Approval',
            'stage_order' => 2,
            'approver_type' => 'manager',
            'can_reject' => true
        ]);
    }

    private function createAttendanceWorkflow()
    {
        $workflow = Workflow::create([
            'name' => 'Attendance Correction',
            'description' => 'Approval for manual attendance adjustments',
            'entity_type' => 'attendance_regularization',
            'is_active' => true,
            'priority' => 1,
            'tenant_id' => auth()->user()->tenant_id
        ]);

        WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'name' => 'Reporting Manager',
            'stage_order' => 1,
            'approver_type' => 'manager',
        ]);
    }

    private function createLeaveWorkflow()
    {
        $workflow = Workflow::create([
            'name' => 'Standard Leave Policy',
            'description' => 'Default leave request validation',
            'entity_type' => 'leave_request',
            'is_active' => true,
            'priority' => 1,
            'tenant_id' => auth()->user()->tenant_id
        ]);

        WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'name' => 'Immediate Supervisor',
            'stage_order' => 1,
            'approver_type' => 'manager',
        ]);

        WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'name' => 'HR Operations',
            'stage_order' => 2,
            'approver_type' => 'role',
            'role_id' => Role::where('name', 'HR')->first()?->id,
        ]);
    }
}
