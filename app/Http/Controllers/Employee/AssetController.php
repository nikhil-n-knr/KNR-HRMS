<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetAssignment;
use Inertia\Inertia;

class AssetController extends Controller
{
    public function index()
    {
        $assignments = AssetAssignment::with(['asset.category'])
            ->where('user_id', auth()->id())
            ->whereNull('returned_at') // Only active assets
            ->latest()
            ->get();

        return Inertia::render('Employee/Assets/Index', [
            'assignments' => $assignments
        ]);
    }

    public function request(Request $request)
    {
        $categories = \App\Models\AssetCategory::select('id', 'name')->get();
        return Inertia::render('Employee/Assets/Request', [
            'categories' => $categories
        ]);
    }

    public function storeRequest(Request $request, \App\Services\Assets\AssetPolicyService $policyService, \App\Services\WorkflowService $workflowService)
    {
        $request->validate([
            'category_id' => 'required|exists:asset_categories,id',
            'reason' => 'required|string|min:10',
            'priority' => 'required|in:Low,Normal,High'
        ]);

        // Policy Check
        $policyCheck = $policyService->canRequest(auth()->user(), $request->category_id);
        if ($policyCheck !== true) {
            return back()->with('error', $policyCheck);
        }

        $assetRequest = \App\Models\AssetRequest::create([
            'user_id' => auth()->id(),
            'category_id' => $request->category_id,
            'reason' => $request->reason,
            'priority' => $request->priority,
            'status' => 'Pending'
        ]);

        // Initialize Workflow
        $workflowService->initializeWorkflow('asset_request', $assetRequest->id, auth()->user());

        return redirect()->route('employee.assets.index')->with('success', 'Asset Request Submitted for Approval');
    }

    public function show(\App\Models\Asset $asset)
    {
        $asset->load(['category', 'assignment.user']);
        
        // Mobile-friendly view
        return Inertia::render('Employee/Assets/Show', [
            'asset' => $asset,
            'isAssignedToMe' => $asset->assignment && $asset->assignment->user_id === auth()->id()
        ]);
    }

    public function reportIssue(Request $request, AssetAssignment $assignment)
    {
        // Logic to report issue will go here (create maintenance log or ticket)
        // For now, just a placeholder success
        return back()->with('success', 'Issue reported to IT Support.');
    }
}
