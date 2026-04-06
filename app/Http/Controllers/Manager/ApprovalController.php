<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\LeaveRequest;
use App\Models\AttendanceRegularization;
use App\Models\ShiftSwap;
use App\Models\FloatingHolidayRequest;
use App\Models\Timesheet;
use App\Notifications\RequestProcessed;
use App\Services\Infrastructure\LoggerService;
use Carbon\Carbon;

class ApprovalController extends Controller
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Display the Manager Approval Dashboard.
     * Aggregates all pending requests.
     */
    /**
     * Display the Manager Approval Dashboard.
     * Fetches requests waiting for the current user's approval.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'Pending');
        $month = $request->input('month');
        $year = $request->input('year');
        $search = $request->input('search');

        // Fetch Approval IDs for current user
        // If status is Pending, we check WorkflowApproval table
        // If status is Approved/Rejected, we check history (approver_id = user)
        $userId = auth()->id();
        
        $isSuperAdmin = auth()->user()->roles->contains('name', 'Super Admin');

        $query = \App\Models\WorkflowApproval::query();
        
        if (!$isSuperAdmin) {
            $query->where('approver_id', $userId);
        }

        $approvals = $query->when($status === 'Pending', fn($q) => $q->where('status', 'pending'))
            ->when($status !== 'Pending', fn($q) => $q->where('status', '!=', 'pending')) // History
            ->with(['workflowInstance'])
            ->get()
            ->groupBy(fn($a) => $a->workflowInstance->entity_type);

        // Helper to get IDs
        $getIds = fn($type) => $approvals->get($type, collect())->pluck('workflowInstance.entity_id')->toArray();

        // 1. Leaves
        $leaves = LeaveRequest::with(['employee', 'leaveType'])
            ->whereIn('id', $getIds('leave_request'))
            ->when($search, fn($q) => $q->whereHas('employee', fn($sub) => 
                $sub->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")
            ))
            ->orderBy('start_date', 'asc')
            ->get();

        // 2. Regularizations
        $regularizations = AttendanceRegularization::with(['employee'])
            ->whereIn('id', $getIds('attendance_regularization'))
            ->when($search, fn($q) => $q->whereHas('employee', fn($sub) => 
                $sub->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")
            ))
            ->orderBy('date', 'asc')
            ->get();

        // 3. Shift Swaps
        $swaps = ShiftSwap::with(['requester', 'recipient', 'shiftFrom', 'shiftTo'])
            ->whereIn('id', $getIds('shift_swap'))
            ->when($search, fn($q) => $q->whereHas('requester', fn($sub) => 
                $sub->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")
            ))
            ->get();

        // 4. Floating Holidays
        $floatingHolidays = FloatingHolidayRequest::with(['user.employee', 'holiday'])
            ->whereIn('id', $getIds('floating_holiday'))
            ->get();

        // 5. Timesheets
        $timesheets = Timesheet::with(['employee'])
            ->whereIn('id', $getIds('timesheet'))
            ->get();
            
        // 6. Overtime
        $overtime = \App\Models\OvertimeRequest::with(['employee'])
            ->whereIn('id', $getIds('overtime'))
            ->get();
            
        // 7. WFH
        $wfh = \App\Models\WfhRequest::with(['employee'])
            ->whereIn('id', $getIds('wfh'))
            ->get();

        // 8. Expenses
        $expenses = \App\Models\Expense::with(['employee', 'category', 'project', 'currentStage'])
            ->whereIn('id', $getIds('expense'))
            ->get();

        // 9. Payrolls
        $payrolls = \App\Models\Payroll::with(['processor', 'currentStage'])
            ->whereIn('id', $getIds('payroll'))
            ->get();

        // 10. Aggregated Pending for "All" Tab
        $allPendingQuery = \App\Models\WorkflowApproval::where('status', 'pending');
        
        if (!$isSuperAdmin) {
            $allPendingQuery->where('approver_id', $userId);
        }

        $allPending = $allPendingQuery->with(['workflowInstance.workflow', 'stage'])
            ->get()
            ->map(function($approval) {
                $instance = $approval->workflowInstance;
                $entity = $this->resolveEntity($instance->entity_type, $instance->entity_id);
                
                return [
                    'id' => $approval->id,
                    'type' => $instance->entity_type,
                    'entity_id' => $instance->entity_id,
                    'workflow' => $instance->workflow->name,
                    'stage' => $approval->stage->name,
                    'requester' => ($entity?->employee?->full_name) ?: ($entity?->user?->name ?: 'System'),
                    'summary' => $this->getEntitySummary($instance->entity_type, $entity),
                    'requested_at' => $approval->created_at->diffForHumans(),
                ];
            });

        if ($request->wantsJson()) {
            return response()->json([
                'leaves' => $leaves,
                'regularizations' => $regularizations,
                'swaps' => $swaps,
                'floatingHolidays' => $floatingHolidays,
                'timesheets' => $timesheets,
                'overtime' => $overtime,
                'wfh' => $wfh,
                'expenses' => $expenses,
                'payrolls' => $payrolls,
                'activeTab' => $activeTab ?? 'leaves'
            ]);
        }
        
        return Inertia::render('Manager/Approvals/ApprovalDashboard', [
            'leaves' => $leaves,
            'regularizations' => $regularizations,
            'swaps' => $swaps,
            'floatingHolidays' => $floatingHolidays,
            'timesheets' => $timesheets,
            'overtime' => $overtime,
            'wfh' => $wfh,
            'expenses' => $expenses,
            'payrolls' => $payrolls,
            'all_pending' => $allPending,
            'active_tab' => $status,
            'filters' => $request->only(['status', 'month', 'year', 'search'])
        ]);
    }


    /**
     * Handle actions (Approve/Reject) for various request types.
     */
    public function action(Request $request)
    {
        $request->validate([
            'approval_id' => 'nullable|integer|exists:workflow_approvals,id',
            'type' => 'required_without:approval_id|string',
            'id' => 'required_without:approval_id|integer',
            'action' => 'required|in:approve,reject',
            'remarks' => 'nullable|string|max:255'
        ]);

        $approver = auth()->user();
        $service = app(\App\Services\WorkflowService::class);

        try {
            if ($request->approval_id) {
                $approval = \App\Models\WorkflowApproval::where('id', $request->approval_id)
                    ->where('approver_id', $approver->id)
                    ->where('status', 'pending')
                    ->firstOrFail();
                $entityType = $approval->workflowInstance->entity_type;
                $entityId = $approval->workflowInstance->entity_id;
            } else {
                // Map frontend type to entity_type
                $typeMap = [
                    'leave' => 'leave_request',
                    'regularization' => 'attendance_regularization',
                    'swap' => 'shift_swap',
                    'floating_holiday' => 'floating_holiday',
                    'timesheet' => 'timesheet',
                    'overtime' => 'overtime',
                    'wfh' => 'wfh',
                    'expense' => 'expense',
                    'payroll' => 'payroll'
                ];

                $entityType = $typeMap[$request->type] ?? $request->type;
                $entityId = $request->id;

                // Find Approval
                $approval = \App\Models\WorkflowApproval::where('approver_id', $approver->id)
                    ->where('status', 'pending')
                    ->whereHas('workflowInstance', function($q) use ($entityType, $entityId) {
                        $q->where('entity_type', $entityType)
                          ->where('entity_id', $entityId);
                    })
                    ->first();
            }

            if ($approval) {
                if ($request->action === 'approve') {
                    $service->approve($approval, $approver, $request->remarks);
                } else {
                    $service->reject($approval, $approver, $request->remarks ?? 'Rejected');
                }
                
                $this->logger->log('approval', $request->action, "{$entityType} #{$entityId} processed by {$approver->name}");
            } else {
                return response()->json(['error' => 'Approval request not found or already processed.'], 404);
            }

        } catch (\Exception $e) {
            \Log::error("Approval Action Failed: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Request processed successfully.']);
        }

        return back()->with('success', 'Request processed successfully.')->setStatusCode(303);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'items' => 'required|array', // Array of {id, type, approval_id}
            'action' => 'required|in:approve,reject',
            'remarks' => 'nullable|string|max:255'
        ]);

        try {
            DB::transaction(function() use ($request) {
                foreach ($request->items as $item) {
                    $req = new Request([
                        'approval_id' => $item['approval_id'] ?? null,
                        'type' => $item['type'] ?? null,
                        'id' => $item['id'] ?? null,
                        'action' => $request->action,
                        'remarks' => $request->remarks
                    ]);
                    $this->action($req);
                }
            });
        } catch (\Exception $e) {
            \Log::error('Bulk Approval Failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Batch processing failed: ' . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Requests processed successfully.']);
        }

        return back()->with('success', 'Batch processed.')->setStatusCode(303);
    }

    private function resolveEntity($type, $id)
    {
        $map = [
            'leave_request' => \App\Models\LeaveRequest::class,
            'attendance_regularization' => \App\Models\AttendanceRegularization::class,
            'shift_swap' => \App\Models\ShiftSwap::class,
            'floating_holiday' => \App\Models\FloatingHolidayRequest::class,
            'timesheet' => \App\Models\Timesheet::class,
            'overtime' => \App\Models\OvertimeRequest::class,
            'wfh' => \App\Models\WfhRequest::class,
            'expense' => \App\Models\Expense::class,
            'payroll' => \App\Models\Payroll::class,
        ];

        $class = $map[$type] ?? null;
        return $class ? $class::find($id) : null;
    }

    private function getEntitySummary($type, $entity)
    {
        if (!$entity) return '-';

        return match($type) {
            'leave_request' => ($entity->leaveType?->name ?? 'Leave') . ": " . Carbon::parse($entity->start_date)->format('M d') . " (" . ($entity->total_days ?? 0) . " days)",
            'attendance_regularization' => "Clock mismatch on " . Carbon::parse($entity->date)->format('M d'),
            'shift_swap' => "Swap with " . ($entity->recipient?->name ?? 'Peer'),
            'expense' => "Amount: ₹" . number_format($entity->amount ?? 0, 2),
            'timesheet' => "Project: " . ($entity->project_name ?? 'N/A') . " (" . ($entity->hours_spent ?? 0) . " hrs)",
            default => "ID: #{$entity->id}",
        };
    }

    private function awardGamificationPoints($approver)
    {
        // Gamification points logic - placeholder for future implementation
        // Award points to approver for processing requests
    }

    // Private handler methods would go here in a real implementation
    // These methods were referenced in the action() method but not included in the file
}