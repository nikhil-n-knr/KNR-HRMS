<?php

namespace App\Http\Controllers\Admin\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\CRMWorkflow;
use App\Models\CRM\CRMWorkflowStage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CRMWorkflowController extends Controller
{
    /**
     * Display a listing of the CRM workflows.
     */
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        
        $workflows = CRMWorkflow::with('stages')
            ->where('tenant_id', $tenantId)
            ->get();

        $roles = Role::where('tenant_id', $tenantId)->get();
        $users = User::where('tenant_id', $tenantId)->get(['id', 'name']);

        return Inertia::render('CRM/Workflows/Index', [
            'workflows' => $workflows,
            'roles' => $roles,
            'users' => $users
        ]);
    }

    /**
     * Store a newly created workflow.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'entity_type' => 'required|string',
            'trigger_event' => 'nullable|string',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        
        $workflow = CRMWorkflow::create($validated);

        return redirect()->back()->with('success', 'CRM Workflow created successfully.');
    }

    /**
     * Update the specified workflow.
     */
    public function update(Request $request, CRMWorkflow $workflow)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'entity_type' => 'required|string',
            'trigger_event' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $workflow->update($validated);

        return redirect()->back()->with('success', 'CRM Workflow updated successfully.');
    }

    /**
     * Remove the specified workflow.
     */
    public function destroy(CRMWorkflow $workflow)
    {
        $workflow->delete();
        return redirect()->back()->with('success', 'CRM Workflow deleted.');
    }

    /**
     * Add a stage to the workflow.
     */
    public function addStage(Request $request, CRMWorkflow $workflow)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'action_type' => 'required|string',
            'config' => 'nullable|array'
        ]);

        $order = $workflow->stages()->count() + 1;
        $workflow->stages()->create(array_merge($validated, ['order' => $order]));

        return redirect()->back()->with('success', 'Stage added.');
    }

    /**
     * Update a stage.
     */
    public function updateStage(Request $request, CRMWorkflowStage $stage)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'action_type' => 'required|string',
            'config' => 'nullable|array'
        ]);

        $stage->update($validated);

        return redirect()->back()->with('success', 'Stage updated.');
    }

    /**
     * Remove a stage.
     */
    public function removeStage(CRMWorkflowStage $stage)
    {
        $stage->delete();
        return redirect()->back()->with('success', 'Stage removed.');
    }

    /**
     * Reorder stages.
     */
    public function reorderStages(Request $request, CRMWorkflow $workflow)
    {
        $request->validate(['stages' => 'required|array']);
        
        foreach ($request->stages as $index => $stageId) {
            CRMWorkflowStage::where('id', $stageId)->update(['order' => $index + 1]);
        }

        return redirect()->back()->with('success', 'Stages reordered.');
    }
}
