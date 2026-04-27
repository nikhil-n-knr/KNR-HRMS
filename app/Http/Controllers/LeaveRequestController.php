<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\Documents\DocumentRegistryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class LeaveRequestController extends Controller
{
    protected $documentService;
    protected $leaveService;

    public function __construct(DocumentRegistryService $documentService, \App\Services\Leave\LeaveService $leaveService)
    {
        $this->documentService = $documentService;
        $this->leaveService = $leaveService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
        ]);

        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        // Check if document is mandatory
        if ($leaveType->requires_document && !$request->hasFile('document')) {
            return redirect()->back()->withErrors(['document' => 'A supporting document is required for this leave type.']);
        }

        $attachmentPath = null;

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            
            // Use DocumentRegistryService
            $doc = $this->documentService->file($employee, $file, [
                'category' => 'Leave',
                'type' => 'Supporting Document',
                'source' => 'LeaveRequest',
                'title' => "Leave Request - " . $leaveType->name . " - " . now()->format('Y-m-d'),
                'meta' => [
                    'leave_type' => $leaveType->name,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date
                ]
            ]);
            
            // Store the db path or the actual relative path? 
            // The schema has 'attachment_path'. Usually we store the path for direct download.
            // EmployeeDocument has 'file_path'.
            $attachmentPath = $doc->file_path;
        }

        // Calculate days (excluding weekends and holidays via LeaveService)
        $days = $this->leaveService->calculateNetDays($request->start_date, $request->end_date, $employee->id);

        if ($days <= 0) {
            return redirect()->back()->withErrors(['start_date' => 'The selected range does not contain any working days.']);
        }

        $leaveRequest = LeaveRequest::create([
            'uuid' => Str::uuid(),
            'employee_id' => $employee->id,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $days,
            'reason' => $validated['reason'],
            'attachment_path' => $attachmentPath,
            'status' => 'pending',
        ]);

        // Trigger Workflow
        try {
            $workflowService = app(\App\Services\WorkflowService::class);
            $instance = $workflowService->initializeWorkflow('leave_request', $leaveRequest->id, $user);

            if (!$instance) {
                // Auto-approve if no workflow configured
                $leaveRequest->update(['status' => 'approved']);
                return redirect()->back()->with('success', 'Leave request approved automatically (No workflow configured).');
            }
        } catch (\Exception $e) {
            \Log::error('Leave Workflow Error: ' . $e->getMessage());
            // Don't fail the request, but maybe warn admin? 
            // For user, request is submitted but might be stuck.
        }

        return redirect()->back()->with('success', 'Leave request submitted for approval.');
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        // Cancel logic
        if ($leaveRequest->employee_id !== auth()->user()->employee->id) {
            abort(403);
        }

        if ($leaveRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Cannot cancel processed requests.');
        }

        $leaveRequest->delete(); // Soft delete

        return redirect()->back()->with('success', 'Request cancelled.');
    }
}
