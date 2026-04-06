<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetRequest;
use App\Models\Asset;
use Inertia\Inertia;

class AssetRequestController extends Controller
{
    public function index()
    {
        $requests = AssetRequest::with(['user', 'category', 'approver'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/AssetRequests/Index', [
            'requests' => $requests
        ]);
    }

    public function approve(Request $request, AssetRequest $assetRequest, \App\Services\WorkflowService $workflowService)
    {
        // Find pending approval for this user
        $approval = \App\Models\WorkflowApproval::where('approver_id', auth()->id())
            ->where('status', 'pending')
            ->whereHas('workflowInstance', function($q) use ($assetRequest) {
                $q->where('entity_type', 'asset_request')
                  ->where('entity_id', $assetRequest->id);
            })
            ->first();

        if (!$approval) {
            // Fallback for Admins who are not explicit approvers? 
            // For now, strict: return error.
            return back()->with('error', 'You do not have a pending approval for this request.');
        }

        try {
            $workflowService->approve($approval, auth()->user());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Request Approved.');
    }

    public function reject(Request $request, AssetRequest $assetRequest, \App\Services\WorkflowService $workflowService)
    {
        $request->validate([
            'rejection_reason' => 'required|string'
        ]);

        $approval = \App\Models\WorkflowApproval::where('approver_id', auth()->id())
            ->where('status', 'pending')
            ->whereHas('workflowInstance', function($q) use ($assetRequest) {
                $q->where('entity_type', 'asset_request')
                  ->where('entity_id', $assetRequest->id);
            })
            ->first();

        if (!$approval) {
            return back()->with('error', 'You do not have a pending approval for this request.');
        }

        try {
            $workflowService->reject($approval, auth()->user(), $request->rejection_reason);
        } catch (\Exception $e) {
             return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Request Rejected.');
    }
}
