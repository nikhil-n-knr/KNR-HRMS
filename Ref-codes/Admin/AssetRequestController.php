<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetRequest;
use App\Models\AssetCategory;
use App\Models\Asset;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class AssetRequestController extends Controller
{
    public function store(Request $request, \App\Services\WorkflowService $workflowService)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'item' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'priority' => 'required|in:Low,Normal,High',
            'reason' => 'required|string|min:5',
        ]);

        $category = AssetCategory::firstOrCreate(
            ['name' => trim($validated['category'])],
            ['is_electronic' => false]
        );

        $reason = trim($validated['reason']);

        $assetRequest = AssetRequest::create([
            'user_id' => $validated['user_id'],
            'item'    => $validated['item'],
            'category_id' => $category->id,
            'priority' => $validated['priority'],
            'reason' => $reason,
            'status' => 'Pending',
        ]);

        try {
            $workflowService->initializeWorkflow('asset_request', $assetRequest->id, auth()->user());
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Request created successfully.');
    }

    public function update(Request $request, AssetRequest $assetRequest)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'item' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'priority' => 'required|in:Low,Normal,High',
            'reason' => 'required|string|min:5',
        ]);

        $category = AssetCategory::firstOrCreate(
            ['name' => trim($validated['category'])],
            ['is_electronic' => false]
        );

        $assetRequest->update([
            'user_id' => $validated['user_id'],
            'item' => $validated['item'],
            'category_id' => $category->id,
            'priority' => $validated['priority'],
            'reason' => $validated['reason'],
        ]);

        return back()->with('success', 'Request updated successfully.');
    }

    public function index()
    {
        $requests = AssetRequest::with(['user', 'category', 'approver'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/AssetRequests/Index', [
            'requests' => $requests
        ]);
    }

    public function approve(Request $request, AssetRequest $assetRequest)
    {
        $isApproved = $assetRequest->update([
            'status' => 'Approved',
            'rejection_reason' => null,
        ]);

        if (!$isApproved) {
            return back()->with('error', 'Failed to approve the request.');
        }

        return back()->with('success', 'Request Approved.');
    }

    public function reject(Request $request, AssetRequest $assetRequest)
    {
        $isRejected = $assetRequest->update([
            'status' => 'Rejected',
            'rejection_reason' => $request->input('rejection_reason', 'No reason provided'),
        ]);

        if(!$isRejected){
            return back()->with('error', 'Failed to reject the request.');
        }

        return back()->with('success', 'Request Rejected.');
    }

    public function fulfill(Request $request, AssetRequest $assetRequest)
    {
        $isFulfilled = $assetRequest->update([
            'status' => 'Fulfilled',
            'rejection_reason' => null,
        ]);

        if(!$isFulfilled){
            return back()->with('error', 'Failed to fulfill the request.');
        }

        return back()->with('success', 'Request Fulfilled.');
    }

    public function destroy(AssetRequest $assetRequest)
    {
        if ($assetRequest->status === 'Fulfilled') {
            Log::warning("Attempt to delete fulfilled asset request ID: {$assetRequest->id}");
            return back()->with('error', 'Cannot delete a fulfilled request.');
        }

        $isDeleted = $assetRequest->delete();

        if (!$isDeleted) {
            return back()->with('error', 'Failed to delete the request.');
        }

        return back()->with('success', 'Request deleted successfully.');
    }

    public function markReturned(Request $request, AssetRequest $assetRequest)
    {
        if ($assetRequest->status !== 'Fulfilled') {
            return back()->with('error', 'Only fulfilled requests can be marked as returned.');
        }

        $isReturned = $assetRequest->update([
            'status' => 'Returned',
        ]);

        if (!$isReturned) {
            return back()->with('error', 'Failed to mark the request as returned.');
        }

        return back()->with('success', 'Request marked as returned.');
    }
}
