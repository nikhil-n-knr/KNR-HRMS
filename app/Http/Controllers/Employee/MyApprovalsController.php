<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\WorkflowInstance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class MyApprovalsController extends Controller
{
    /**
     * Display the employee's own approval requests and incoming items to action.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. My Requests (Initiated by me)
        $initiatedQuery = WorkflowInstance::with([
            'workflow:id,name,entity_type,approved_status,rejected_status',
            'initiator:id,name',
            'currentStage:id,name,approver_type',
            'approvals' => fn($q) => $q->with('approver:id,name')->orderBy('created_at'),
        ])->where('initiator_id', $user->id);

        // 2. Incoming Actions (Items pending my approval)
        $incomingQuery = \App\Models\WorkflowApproval::with([
            'workflowInstance.workflow',
            'workflowInstance.initiator.employee',
            'workflowInstance.approvals.approver:id,name',
            'stage'
        ])
        ->where('approver_id', $user->id)
        ->where('status', 'pending');

        // Apply shared filters (Status, Entity Type, Dates)
        if ($request->filled('status') && $request->status !== 'all') {
            $initiatedQuery->where('status', $request->status);
        }
        if ($request->filled('entity_type') && $request->entity_type !== 'all') {
            $initiatedQuery->where('entity_type', $request->entity_type);
            $incomingQuery->whereHas('workflowInstance', fn($q) => $q->where('entity_type', $request->entity_type));
        }
        if ($request->filled('date_from')) {
            $initiatedQuery->whereDate('created_at', '>=', $request->date_from);
            $incomingQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $initiatedQuery->whereDate('created_at', '<=', $request->date_to);
            $incomingQuery->whereDate('created_at', '<=', $request->date_to);
        }

        // Fetch and Map Initiated
        $instances = $initiatedQuery->latest()->get()->map(function ($instance) {
            $instance->entity_summary = $this->resolveEntitySummary($instance);
            $instance->entity_details = $this->resolveEntityDetails($instance);
            $instance->raised_by = $instance->initiator?->name ?? 'System';
            $instance->approved_by = collect($instance->approvals ?? [])
                ->filter(fn($a) => in_array($a->status, ['approved', 'skipped'], true))
                ->map(fn($a) => $a->approver?->name ?: 'Pool Approver')
                ->unique()
                ->values()
                ->all();
            $instance->pending_with = collect($instance->approvals ?? [])
                ->filter(fn($a) => $a->status === 'pending')
                ->map(fn($a) => $a->approver?->name ?: 'Pool Approver')
                ->unique()
                ->values()
                ->all();
            return $instance;
        });

        // Fetch and Map Incoming
        $incoming = $incomingQuery->latest()->get()->map(function($approval) {
            $instance = $approval->workflowInstance;
            $allApprovals = collect($instance->approvals ?? []);

            return [
                'id' => $approval->id,
                'instance_id' => $instance->id,
                'entity_type' => $instance->entity_type,
                'entity_id' => $instance->entity_id,
                'workflow_name' => $instance->workflow?->name ?? 'Approval Request',
                'stage_name' => $approval->stage?->name ?? 'Review',
                'initiator' => $instance->initiator?->name ?? 'System',
                'raised_by' => $instance->initiator?->name ?? 'System',
                'summary' => $this->resolveEntitySummary($instance),
                'details' => $this->resolveEntityDetails($instance),
                'approved_by' => $allApprovals
                    ->filter(fn($a) => in_array($a->status, ['approved', 'skipped'], true))
                    ->map(fn($a) => $a->approver?->name ?: 'Pool Approver')
                    ->unique()
                    ->values()
                    ->all(),
                'pending_with' => $allApprovals
                    ->filter(fn($a) => $a->status === 'pending')
                    ->map(fn($a) => $a->approver?->name ?: 'Pool Approver')
                    ->unique()
                    ->values()
                    ->all(),
                'created_at' => $approval->created_at,
                'started_at' => $instance->started_at,
                'status' => $approval->status,
            ];
        });

        // --- Search (post-enrich) ---
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $instances = $instances->filter(fn($i) => str_contains(strtolower($i->entity_summary ?? ''), $search))->values();
            $incoming = $incoming->filter(fn($i) => str_contains(strtolower($i->summary ?? ''), $search))->values();
        }

        // --- Analytics Stats ---
        $allInitiated = WorkflowInstance::where('initiator_id', $user->id)->get();
        $stats = [
            'total'             => $allInitiated->count(),
            'total_pending'     => $allInitiated->where('status', 'pending')->count(),
            'total_approved'    => $allInitiated->where('status', 'approved')->count(),
            'total_rejected'    => $allInitiated->where('status', 'rejected')->count(),
            'incoming_pending'  => \App\Models\WorkflowApproval::where('approver_id', $user->id)->where('status', 'pending')->count(),
            'avg_turnaround_days' => $this->calcAvgTurnaround($allInitiated),
        ];

        // Module specific counts (for My Requests)
        $counts = $allInitiated->groupBy('entity_type')->map->count();
        $moduleTypes = [
            ['value' => 'all', 'label' => 'all', 'count' => $allInitiated->count()],
            ['value' => 'leave_request', 'label' => 'leaves', 'count' => $counts['leave_request'] ?? 0],
            ['value' => 'attendance_regularization', 'label' => 'regularizations', 'count' => $counts['attendance_regularization'] ?? 0],
            ['value' => 'shift_swap', 'label' => 'swaps', 'count' => $counts['shift_swap'] ?? 0],
            ['value' => 'floating_holiday_request', 'label' => 'Holidays', 'count' => $counts['floating_holiday_request'] ?? 0],
            ['value' => 'timesheet', 'label' => 'timesheets', 'count' => $counts['timesheet'] ?? 0],
            ['value' => 'overtime_request', 'label' => 'overtime', 'count' => $counts['overtime_request'] ?? 0],
            ['value' => 'wfh_request', 'label' => 'wfh', 'count' => $counts['wfh_request'] ?? 0],
            ['value' => 'expense', 'label' => 'expenses', 'count' => $counts['expense'] ?? 0],
            ['value' => 'payroll', 'label' => 'payrolls', 'count' => $counts['payroll'] ?? 0],
        ];

        $data = [
            'instances'   => $instances,
            'incoming'    => $incoming,
            'stats'       => $stats,
            'moduleTypes' => $moduleTypes,
            'filters'     => $request->only(['status', 'entity_type', 'date_from', 'date_to', 'search']),
        ];

        return $request->wantsJson() ? response()->json($data) : Inertia::render('Employee/MyApprovals/Index', $data);
    }

    /**
     * Process an approval action (Approve/Reject).
     */
    public function action(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:workflow_approvals,id',
            'action' => 'required|in:approve,reject',
            'remarks' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $service = app(\App\Services\WorkflowService::class);
        $logger = app(\App\Services\Infrastructure\LoggerService::class);

        try {
            $approval = \App\Models\WorkflowApproval::where('id', $request->id)
                ->where('approver_id', $user->id)
                ->where('status', 'pending')
                ->firstOrFail();

            if ($request->action === 'approve') {
                $service->approve($approval, $user, $request->remarks);
            } else {
                $service->reject($approval, $user, $request->remarks ?? 'Rejected');
            }

            $instance = $approval->workflowInstance;
            $logger->log('approval', $request->action, "{$instance->entity_type} #{$instance->entity_id} processed by {$user->name}", ['id' => $instance->entity_id]);

            return back()->with('success', 'Request ' . ($request->action === 'approve' ? 'approved' : 'rejected') . ' successfully.')->setStatusCode(303);
        } catch (\Exception $e) {
            \Log::error("Unified Approval Action Failed: " . $e->getMessage());
            return back()->with('error', 'Action failed: ' . $e->getMessage())->setStatusCode(303);
        }
    }

    /**
     * Process bulk approval actions.
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:workflow_approvals,id',
            'action' => 'required|in:approve,reject',
            'remarks' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $service = app(\App\Services\WorkflowService::class);
        $logger = app(\App\Services\Infrastructure\LoggerService::class);

        $approvals = \App\Models\WorkflowApproval::with('workflowInstance')
            ->whereIn('id', $validated['ids'])
            ->where('approver_id', $user->id)
            ->where('status', 'pending')
            ->get();

        if ($approvals->isEmpty()) {
            return back()->with('error', 'No valid pending approvals found for bulk action.')->setStatusCode(303);
        }

        $successCount = 0;
        $errors = [];

        foreach ($approvals as $approval) {
            try {
                if ($validated['action'] === 'approve') {
                    $service->approve($approval, $user, $validated['remarks'] ?? null);
                } else {
                    $service->reject($approval, $user, $validated['remarks'] ?? 'Rejected');
                }

                $instance = $approval->workflowInstance;
                $logger->log(
                    'approval',
                    'bulk_' . $validated['action'],
                    "{$instance->entity_type} #{$instance->entity_id} processed by {$user->name}",
                    ['id' => $instance->entity_id]
                );

                $successCount++;
            } catch (\Throwable $e) {
                $errors[] = "#{$approval->id}: {$e->getMessage()}";
                \Log::error("Bulk approval action failed for approval {$approval->id}: {$e->getMessage()}");
            }
        }

        if ($successCount === 0) {
            return back()->with('error', 'Bulk action failed. ' . implode(' | ', array_slice($errors, 0, 3)))->setStatusCode(303);
        }

        $message = "{$successCount} request(s) " . ($validated['action'] === 'approve' ? 'approved' : 'rejected') . ' successfully.';
        if (!empty($errors)) {
            $message .= ' Some items failed.';
        }

        return back()->with('success', $message)->setStatusCode(303);
    }

    /**
     * Resolve a human-readable summary string for each entity type.
     */
    protected function resolveEntitySummary(WorkflowInstance $instance): string
    {
        try {
            $entity = null;
            switch ($instance->entity_type) {
                case 'leave_request':
                    $entity = \App\Models\LeaveRequest::with('leaveType:id,name')->find($instance->entity_id);
                    if (!$entity) return 'Leave Request';
                    return "Leave: {$entity->total_days} day(s) — " . ($entity->leaveType?->name ?? 'N/A') . " ({$entity->start_date} to {$entity->end_date})";

                case 'attendance_regularization':
                    $entity = \App\Models\AttendanceRegularization::find($instance->entity_id);
                    if (!$entity) return 'Regularization Request';
                    return "Regularization: {$entity->date} (" . ($entity->regularized_in_time ?? '??') . "–" . ($entity->regularized_out_time ?? '??') . ")";

                case 'shift_swap':
                    $entity = \App\Models\ShiftSwap::with('recipient:id,first_name,last_name')->find($instance->entity_id);
                    if (!$entity) return 'Shift Swap';
                    $name = ($entity->recipient?->first_name ?? 'Unknown') . ' ' . ($entity->recipient?->last_name ?? 'User');
                    return "Shift Swap with {$name} on {$entity->date}";

                case 'expense':
                    $entity = \App\Models\Expense::with('category:id,name')->find($instance->entity_id);
                    if (!$entity) return 'Expense Claim';
                    return "Expense: ₹" . number_format($entity->amount ?? 0, 2) . " — {$entity->title} (" . ($entity->category?->name ?? 'N/A') . ")";

                case 'timesheet':
                    $entity = \App\Models\Timesheet::with(['project:id,name', 'task:id,title'])->find($instance->entity_id);
                    if (!$entity) return 'Timesheet';

                    $date = $entity->date?->format('d M Y') ?? 'N/A';
                    $hours = number_format((float) ($entity->hours_spent ?? 0), 2);
                    $projectName = $entity->project?->name ?? $entity->project_name ?? 'N/A';
                    $taskName = $entity->task?->title ?? $entity->task_title ?? $entity->task_description ?? 'N/A';

                    return "Timesheet: {$date} — {$hours}h ({$projectName} / {$taskName})";
                
                case 'overtime':
                case 'overtime_request':
                    $entity = \App\Models\OvertimeRequest::find($instance->entity_id);
                    if (!$entity) return 'Overtime Request';
                    $date = $entity->date instanceof \Carbon\Carbon ? $entity->date->format('d M Y') : $entity->date;
                    return "Overtime: " . (($entity->minutes ?? 0) / 60) . " hrs on " . ($date ?? 'N/A');

                case 'wfh':
                case 'wfh_request':
                    $entity = \App\Models\WfhRequest::find($instance->entity_id);
                    if (!$entity) return 'WFH Request';
                    $date = $entity->date instanceof \Carbon\Carbon ? $entity->date->format('d M Y') : $entity->date;
                    return "WFH Request for " . ($date ?? 'N/A');

                case 'floating_holiday':
                case 'floating_holiday_request':
                    $entity = \App\Models\FloatingHolidayRequest::with('holiday')->find($instance->entity_id);
                    if (!$entity) return 'Holiday Request';
                    return "Holiday: " . ($entity->holiday?->name ?? 'Floating Holiday');

                case 'payroll':
                    $entity = \App\Models\Payroll::find($instance->entity_id);
                    if (!$entity) return 'Payroll Processing';
                    return "Payroll: " . ($entity->month ?? '?') . "/" . ($entity->year ?? '?') . " — Batch: " . ($entity->batch_name ?? 'N/A');

                default:
                    return ucfirst(str_replace('_', ' ', $instance->entity_type));
            }
        } catch (\Throwable $e) {
            \Log::warning("Summary resolution failed for {$instance->entity_type} #{$instance->entity_id}: " . $e->getMessage());
            return ucfirst(str_replace('_', ' ', $instance->entity_type));
        }
    }

    /**
     * Resolve structured details to render clear cards in the approvals list.
     */
    protected function resolveEntityDetails(WorkflowInstance $instance): array
    {
        try {
            switch ($instance->entity_type) {
                case 'timesheet':
                    $entity = \App\Models\Timesheet::with(['project:id,name', 'task:id,title'])->find($instance->entity_id);
                    if (!$entity) return [];

                    $projectName = $entity->project?->name ?? $entity->project_name ?? 'N/A';
                    $taskName = $entity->task?->title ?? $entity->task_title ?? $entity->task_description ?? 'N/A';
                    $hours = number_format((float) ($entity->hours_spent ?? 0), 2) . 'h';
                    $date = $entity->date?->format('d M Y') ?? 'N/A';

                    return [
                        ['label' => 'Date', 'value' => $date],
                        ['label' => 'Project', 'value' => $projectName],
                        ['label' => 'Task', 'value' => $taskName],
                        ['label' => 'Hours', 'value' => $hours],
                    ];

                default:
                    return [];
            }
        } catch (\Throwable $e) {
            \Log::warning("Details resolution failed for {$instance->entity_type} #{$instance->entity_id}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate average turnaround time in days for completed instances.
     */
    protected function calcAvgTurnaround($instances): ?float
    {
        $completed = $instances->whereNotNull('completed_at')->where('status', '!=', 'pending');
        if ($completed->isEmpty()) return null;

        $avg = $completed->avg(fn($i) =>
            Carbon::parse($i->started_at)->diffInHours(Carbon::parse($i->completed_at)) / 24
        );

        return round($avg, 1);
    }
}
