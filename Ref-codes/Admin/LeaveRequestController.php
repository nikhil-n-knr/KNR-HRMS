<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Services\Infrastructure\LoggerService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\WorkflowService;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    use ApiResponser;

    protected $logger;
    protected $leaveService;

    public function __construct(LoggerService $logger, \App\Services\Leave\LeaveService $leaveService)
    {
        $this->logger = $logger;
        $this->leaveService = $leaveService;
    }

    // List "My Leaves"
    public function dashboard(Request $request)
    {
        return \Inertia\Inertia::render('Admin/Leave/Hub', [
            'tab' => $request->get('tab', 'dashboard')
        ]);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->employee) {
            return $this->error('No employee record linked to this account.', 404);
        }

        $query = LeaveRequest::with(['leaveType', 'approver'])
            ->where('employee_id', $user->employee->id);

        if ($request->has('status')) {
             $query->where('status', $request->status);
        }

        $leaves = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 10);

        // Also fetch balances for the dashboard
        $balances = LeaveBalance::where('employee_id', $user->employee->id)
            ->where('year', date('Y'))
            ->with('leaveType')
            ->get();

        // If simple paging response:
        // return $this->success($leaves);
        
        // But we want balances too. ApiResponser typically expects one main data object.
        // We will return leaves as main data, and attach balances as extra.
        // Or better: separate endpoint for balances? 
        // For efficiency, let's merge or return a custom structure if frontend handles it.
        // But BaseDataTable expects standard paging structure.
        // Strategy: Return standard paging for the Table. Dashboard can make a separate call for headers OR we embed it in 'meta' or 'additional'.
        // Let's stick to standard Paging for the list, and endpoint for balances.
        // Or, assume the dashboard calls `index` for the table.
        // We'll create a separate method `dashboard` if needed, but for now let's just do `index` for list.
        
        return $this->success($leaves, 'Leave requests retrieved');
    }

    public function myLeaves(Request $request)
    {
        $user = auth()->user();
        if (!$user->employee) {
            return \Inertia\Inertia::render('Employee/LeaveManagement/Index', [
                'balances' => [],
                'leaves' => ['data' => []],
            'leaveTypes' => LeaveType::where('is_active', true)->get(),
                'error' => 'Your account is not linked to an Employee Profile. Please contact HR to link your record.'
            ]);
        }

        // Balances
        $balances = LeaveBalance::where('employee_id', $user->employee->id)
            ->where('year', date('Y'))
            ->with('leaveType')
            ->get();

        // Leaves History
        $query = LeaveRequest::with(['leaveType', 'approver'])
            ->where('employee_id', $user->employee->id);

        $this->applyFilters($query, $request);

        $leaves = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return \Inertia\Inertia::render('Employee/LeaveManagement/Index', [
            'balances' => $balances,
            'leaves' => $leaves,
            'leaveTypes' => LeaveType::where('is_active', true)->get(),
            'filters' => $request->only(['status', 'leave_type_id', 'start_date', 'end_date'])
        ]);
    }

    public function balances(Request $request)
    {
        $user = auth()->user();
        if (!$user->employee) return $this->error('No employee profile.', 404);

        $balances = LeaveBalance::where('employee_id', $user->employee->id)
            ->where('year', date('Y'))
            ->with('leaveType')
            ->get();
            
        // If no balances exist yet, we might want to initialize them?
        // Phase 1 assumption: Admin creates them or we auto-create on fly?
        // Let's return what we have.
        
        return $this->success($balances, 'Balances retrieved');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->employee) {
            return $this->error('No employee record linked.', 403);
        }

        $leaveType = LeaveType::find($request->leave_type_id);

        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'attachment' => [
                Rule::requiredIf(fn() => $leaveType && $leaveType->requires_document),
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120' // 5MB
            ]
        ]);
        
        $attachmentPath = null;
        $fileMetadata = []; // Store metadata for EmployeeDocument

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileMetadata = [
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'original_name' => $file->getClientOriginalName()
            ];
            $attachmentPath = $file->store('leave-attachments', 'public');
        }

        $daysRequested = $this->leaveService->calculateNetDays($validated['start_date'], $validated['end_date'], $user->employee->id);

        if ($daysRequested <= 0) {
             if (request()->header('X-Inertia') || request()->ajax() === false) {
                 throw \Illuminate\Validation\ValidationException::withMessages([
                     'start_date' => "The selected range does not contain any working days."
                 ]);
             }
             return $this->error("The selected range does not contain any working days.", 422);
        }

        // 1. Check Balance
        $balance = LeaveBalance::where('employee_id', $user->employee->id)
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('year', date('Y'))
            ->first();

        if (!$balance) {
            // Auto-allocate default balance if not exists (Lazy Allocation)
            $balance = LeaveBalance::create([
                'employee_id' => $user->employee->id,
                'leave_type_id' => $validated['leave_type_id'],
                'year' => date('Y'),
                'total_days' => $leaveType->days_allowed_per_year,
                'used_days' => 0
            ]);
        }

        $available = $balance->total_days - $balance->used_days;
        
        if ($daysRequested > $available) {
             if (request()->header('X-Inertia') || request()->ajax() === false) {
                 throw \Illuminate\Validation\ValidationException::withMessages([
                     'end_date' => "Insufficient balance. Requested: {$daysRequested}, Available: {$available}"
                 ]);
             }
             return $this->error("Insufficient balance. Requested: {$daysRequested}, Available: {$available}", 422);
        }

        return \Illuminate\Support\Facades\DB::transaction(function() use ($user, $validated, $daysRequested, $balance, $attachmentPath, $fileMetadata) {
            // 2. Deduct Balance (Hold)
            $balance->increment('used_days', $daysRequested);

            // 2.1 Check for Timesheet Conflict (Reverse Link)
            $existingWork = \App\Models\Timesheet::where('employee_id', $user->employee->id)
                ->whereBetween('date', [$validated['start_date'], $validated['end_date']])
                ->exists();

            if ($existingWork) {
                // Determine if we should fail or allow?
                // User said "restrict them". Usually if you worked, you shouldn't apply for full day leave without cancelling work.
                // We will Block it to enforce data integrity.
                if (request()->header('X-Inertia') || request()->ajax() === false) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'start_date' => "Cannot apply for leave: Timesheet entries exist for these dates. Please delete timesheets first."
                    ]);
                }
                throw new \Exception("Cannot apply for leave: Timesheet entries exist for these dates. Please delete timesheets first.");
            }

            // 3. Create Request
            $leaveRequest = LeaveRequest::create([
                'uuid' => Str::uuid(),
                'employee_id' => $user->employee->id,
                'leave_type_id' => $validated['leave_type_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'total_days' => $daysRequested,
                'reason' => $validated['reason'],
                'attachment_path' => $attachmentPath,
                'status' => 'pending'
            ]);

            // Trigger Workflow
            try {
                $workflowService = app(WorkflowService::class);
                $instance = $workflowService->initializeWorkflow('leave_request', $leaveRequest->id, $user);

                if (!$instance) {
                    // Auto-approve if no workflow configured
                    $leaveRequest->update(['status' => 'approved']);
                }
            } catch (\Throwable $e) {
                \Log::error('Leave Workflow Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            }

            // 4. Create Centralized Document Entry
            if ($attachmentPath) {
                EmployeeDocument::create([
                    'employee_id' => $user->employee->id,
                    'title' => 'Leave Attachment (' . $validated['start_date'] . ')',
                    'category' => 'Leave',
                    'document_type' => 'Support Document',
                    'file_path' => $attachmentPath,
                    'file_type' => $fileMetadata['mime'] ?? 'application/octet-stream',
                    'file_size' => $fileMetadata['size'] ?? 0,
                    'is_system_generated' => false,
                    'source_module' => 'LeaveManagement',
                    'source_reference' => $leaveRequest->id,
                    'uploaded_by' => $user->id
                ]);
            }

            $this->logger->log('leave_management', 'create', "Leave requested by {$user->name}: {$daysRequested} days.");

            if (request()->header('X-Inertia')) {
                return redirect()->back()->with('success', 'Leave request submitted successfully');
            }

            return $this->success($leaveRequest, 'Leave request submitted successfully', 201);
        });
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $user = auth()->user();

        // 1. Check Status & Ownership
        if ($leaveRequest->status !== 'pending') {
            return $this->error('Only pending requests can be modified.', 422);
        }
        if ($leaveRequest->employee_id !== $user->employee->id) {
             return $this->error('Unauthorized', 403);
        }

        // 2. Validate
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function() use ($leaveRequest, $validated, $request, $user) {
             $newDays = $this->leaveService->calculateNetDays($validated['start_date'], $validated['end_date'], $user->employee->id);

              if ($newDays <= 0) {
                   throw \Illuminate\Validation\ValidationException::withMessages([
                       'start_date' => "The selected range does not contain any working days."
                   ]);
              }

             // Handle Attachment
             if ($request->hasFile('attachment')) {
                 $leaveRequest->attachment_path = $request->file('attachment')->store('leave-attachments', 'public');
             }

             // Adjust Balances
             // A. Refund Old
             $oldBalance = LeaveBalance::where('employee_id', $user->employee->id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('year', date('Y'))
                ->first();
             if ($oldBalance) {
                 $oldBalance->decrement('used_days', $leaveRequest->total_days);
             }

             // B. Charge New
             $newBalance = LeaveBalance::where('employee_id', $user->employee->id)
                ->where('leave_type_id', $validated['leave_type_id'])
                ->where('year', date('Y'))
                ->first();

             // Auto-create if missing (Edge case if switching type)
             if (!$newBalance) {
                  $type = LeaveType::find($validated['leave_type_id']);
                  $newBalance = LeaveBalance::create([
                        'employee_id' => $user->employee->id,
                        'leave_type_id' => $validated['leave_type_id'],
                        'year' => date('Y'),
                        'total_days' => $type->days_allowed_per_year,
                        'used_days' => 0
                  ]);
             }

             $available = $newBalance->total_days - $newBalance->used_days;
              if ($newDays > $available) {
                   throw \Illuminate\Validation\ValidationException::withMessages([
                       'end_date' => "Insufficient balance. Requested: {$newDays}, Available: {$available}"
                   ]);
              }

             $newBalance->increment('used_days', $newDays);

             // Update Request
             $leaveRequest->update([
                 'leave_type_id' => $validated['leave_type_id'],
                 'start_date' => $validated['start_date'],
                 'end_date' => $validated['end_date'],
                 'total_days' => $newDays,
                 'reason' => $validated['reason']
             ]);

             $this->logger->log('leave_management', 'update', "Leave request updated: {$leaveRequest->uuid}");

             if (request()->header('X-Inertia')) {
                return redirect()->back()->with('success', 'Leave request updated successfully');
             }
             return $this->success($leaveRequest, 'Updated');
        });
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        // Cancel/Withdraw logic
        $isFuture = \Carbon\Carbon::parse($leaveRequest->start_date)->isFuture();

        if ($leaveRequest->status === 'approved') {
            // Check 6-hour withdrawal window
            // If hours remaining until start date is < 6, block.
            $startTime = \Carbon\Carbon::parse($leaveRequest->start_date)->startOfDay();
            $hoursRemaining = now()->diffInHours($startTime, false); // Returns hours from now TO start (positive if future)

            if ($hoursRemaining < 6) {
                 if (request()->header('X-Inertia') || request()->ajax() === false) {
                     return back()->with('error', 'Cannot withdraw: Request is within 6 hours of start time (or passed).');
                 }
                 return $this->error('Cannot withdraw: Request is within 6 hours of start time (or passed).', 422);
            }
        } elseif ($leaveRequest->status !== 'pending') {
             if (request()->header('X-Inertia') || request()->ajax() === false) {
                 return back()->with('error', 'Cannot withdraw a rejected or cancelled request.');
             }
             return $this->error('Cannot withdraw a rejected or cancelled request.', 422);
        }

        // Verify ownership
        if ($leaveRequest->employee_id !== auth()->user()->employee?->id) {
             if (request()->header('X-Inertia') || request()->ajax() === false) {
                 abort(403, 'Unauthorized');
             }
             return $this->error('Unauthorized', 403);
        }

        return \Illuminate\Support\Facades\DB::transaction(function() use ($leaveRequest) {
            // Refund Balance
            $balance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('year', date('Y'))
                ->first();
            
            if ($balance) {
                $balance->decrement('used_days', $leaveRequest->total_days);
            }

            $leaveRequest->forceDelete(); // Completely remove from DB
            
            $this->logger->log('leave_management', 'cancel', "Leave request cancelled and removed: {$leaveRequest->uuid}");

            if (request()->header('X-Inertia')) {
                return redirect()->back()->with('success', 'Leave request cancelled and removed.');
            }

            return $this->success(null, 'Leave request cancelled and removed.');
        });
    }

    // --- Manager Workflow ---

    public function approvals(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->roles->contains('name', 'Super Admin') || $user->roles->contains('name', 'Admin');

        $query = LeaveRequest::with(['employee.department', 'leaveType']);
        
        // Stats Query
        $statsQuery = LeaveRequest::query();
        if (!$isAdmin) {
             $reportingEmployeeIds = Employee::where('reporting_to', $user->id)->pluck('id');
             $query->whereIn('employee_id', $reportingEmployeeIds);
             $statsQuery->whereIn('employee_id', $reportingEmployeeIds);
        }

        // Calculate Stats
        $stats = [
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'approved_today' => (clone $statsQuery)->where('status', 'approved')->whereDate('approved_at', Carbon::today())->count(),
            'on_leave_today' => (clone $statsQuery)->where('status', 'approved')
                                ->whereDate('start_date', '<=', Carbon::today())
                                ->whereDate('end_date', '>=', Carbon::today())
                                ->count(),
            'total_today' => (clone $statsQuery)->whereDate('created_at', Carbon::today())->count()
        ];

        $this->applyFilters($query, $request);

        if (!$request->has('status') && !$request->has('all')) {
            $query->where('status', 'pending');
        }

        $requests = $query->orderBy('created_at', 'asc')
            ->paginate($request->per_page ?? 10)
            ->withQueryString();
        
        return response()->json([
            'status' => 'Success',
            'message' => 'Approval queue retrieved',
            'data' => $requests,
            'stats' => $stats
        ]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:leave_requests,id',
            'action' => 'required|in:approved,rejected',
            'comment' => 'nullable|string'
        ]);

        $user = auth()->user();
        $count = 0;

        \DB::transaction(function () use ($request, $user, &$count) {
             $requests = LeaveRequest::with('employee')->whereIn('id', $request->ids)->where('status', 'pending')->get();
             
             foreach ($requests as $req) {
                 $isAdmin = $user->roles->contains('name', 'Super Admin') || $user->roles->contains('name', 'Admin');
                 if (!$isAdmin && $req->employee->reporting_to !== $user->id) continue;

                 if ($request->action === 'rejected') {
                     $balance = LeaveBalance::where('employee_id', $req->employee_id)
                        ->where('leave_type_id', $req->leave_type_id)
                        ->where('year', date('Y'))
                        ->first();
                    if ($balance) $balance->decrement('used_days', $req->total_days);
                 }

                 $req->update([
                     'status' => $request->action,
                     'approver_id' => $user->id,
                     'approver_comment' => $request->comment,
                     'approved_at' => now()
                 ]);
                 $count++;
             }
        });
        
        return response()->json(['message' => "{$count} requests processed."]);
    }

    public function action(Request $request, LeaveRequest $leaveRequest)
    {
        // 1. Authorization: Must be the manager
        $user = auth()->user();
        if ($leaveRequest->employee->reporting_to !== $user->id) {
             $isAdmin = $user->roles->contains('name', 'Super Admin') || $user->roles->contains('name', 'Admin');
             if (!$isAdmin) { 
                 return $this->error('Unauthorized to approve this request.', 403);
             }
        }

        if ($leaveRequest->status !== 'pending') {
            return $this->error('Request is already processed.', 422);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'comment' => 'nullable|string|max:500'
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function() use ($leaveRequest, $validated, $user) {
            
            if ($validated['status'] === 'rejected') {
                // Refund usage
                $balance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
                    ->where('leave_type_id', $leaveRequest->leave_type_id)
                    ->where('year', date('Y'))
                    ->first();
                
                if ($balance) {
                    $balance->decrement('used_days', $leaveRequest->total_days);
                }
            }
            // If Approved, do nothing (usage was held on apply)

            $leaveRequest->update([
                'status' => $validated['status'],
                'approver_id' => $user->id,
                'approver_comment' => $validated['comment'] ?? null,
                'approved_at' => now()
            ]);

            $this->logger->log('leave_management', 'approve', "Leave request {$validated['status']} by {$user->name}: {$leaveRequest->uuid}");

            return $this->success($leaveRequest, "Leave request {$validated['status']}");
        });
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $query = LeaveRequest::with(['leaveType', 'employee', 'approver']);
        
        if ($request->mode === 'approvals') {
             // Manager/Admin Scope
             $isAdmin = $user->roles->contains('name', 'Super Admin') || $user->roles->contains('name', 'Admin');
             if (!$isAdmin) {
                 $reportingEmployeeIds = Employee::where('reporting_to', $user->id)->pluck('id');
                 $query->whereIn('employee_id', $reportingEmployeeIds);
             }
        } else {
             // My Leaves Scope
              if (!$user->employee) {
                  return back()->with('error', 'Your account is not linked to an Employee Profile. Please contact HR.');
              }
             $query->where('employee_id', $user->employee->id);
        }
        
        $this->applyFilters($query, $request);
        
        $filename = 'leaves_' . date('Y-m-d_H-i') . '.csv';
        
        return response()->streamDownload(function() use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Employee', 'Type', 'Start Date', 'End Date', 'Days', 'Status', 'Reason', 'Applied On']);
            
            $query->chunk(200, function($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->uuid,
                        $row->employee->first_name . ' ' . $row->employee->last_name,
                        $row->leaveType->name ?? 'N/A',
                        $row->start_date,
                        $row->end_date,
                        $row->total_days,
                        ucfirst($row->status),
                        $row->reason,
                        $row->created_at->format('Y-m-d H:i')
                    ]);
                }
            });
            fclose($handle);
        }, $filename);
    }

    private function applyFilters($query, $request) {
        if ($request->filled('leave_type_id')) $query->where('leave_type_id', $request->leave_type_id);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('start_date')) $query->whereDate('start_date', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->whereDate('end_date', '<=', $request->end_date);
    }
}
