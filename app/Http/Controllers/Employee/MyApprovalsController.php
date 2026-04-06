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
     * Display the employee's own approval requests with analytics.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // --- Build base query ---
        $query = WorkflowInstance::with([
            'workflow:id,name,entity_type,approved_status,rejected_status',
            'currentStage:id,name,approver_type',
            'approvals' => fn($q) => $q->with('approver:id,name')->orderBy('created_at'),
        ])
        ->where('initiator_id', $user->id);

        // --- Filters ---
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('entity_type') && $request->entity_type !== 'all') {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $instances = $query->latest()->get();

        // --- Enrich each instance with entity details ---
        $instances = $instances->map(function ($instance) {
            $instance->entity_summary = $this->resolveEntitySummary($instance);
            return $instance;
        });

        // --- Search (post-enrich) ---
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $instances = $instances->filter(function ($i) use ($search) {
                return str_contains(strtolower($i->entity_summary ?? ''), $search)
                    || str_contains(strtolower($i->entity_type ?? ''), $search);
            })->values();
        }

        // --- Analytics Stats ---
        $allInstances = WorkflowInstance::where('initiator_id', $user->id)->get();

        $stats = [
            'total'             => $allInstances->count(),
            'total_pending'     => $allInstances->where('status', 'pending')->count(),
            'total_approved'    => $allInstances->where('status', 'approved')->count(),
            'total_rejected'    => $allInstances->where('status', 'rejected')->count(),
            'avg_turnaround_days' => $this->calcAvgTurnaround($allInstances),
        ];

        // --- Module specific counts ---
        $counts = $allInstances->groupBy('entity_type')->map->count();
        $moduleTypes = [
            ['value' => 'all',                        'label' => 'all',             'count' => $allInstances->count()],
            ['value' => 'leave_request',               'label' => 'leaves',          'count' => $counts['leave_request'] ?? 0],
            ['value' => 'attendance_regularization',   'label' => 'regularizations', 'count' => $counts['attendance_regularization'] ?? 0],
            ['value' => 'shift_swap',                  'label' => 'swaps',           'count' => $counts['shift_swap'] ?? 0],
            ['value' => 'floating_holiday_request',    'label' => 'Holidays',        'count' => $counts['floating_holiday_request'] ?? 0],
            ['value' => 'timesheet',                   'label' => 'timesheets',      'count' => $counts['timesheet'] ?? 0],
            ['value' => 'overtime_request',            'label' => 'overtime',        'count' => $counts['overtime_request'] ?? 0],
            ['value' => 'wfh_request',                 'label' => 'wfh',             'count' => $counts['wfh_request'] ?? 0],
            ['value' => 'expense',                     'label' => 'expenses',        'count' => $counts['expense'] ?? 0],
            ['value' => 'payroll',                     'label' => 'payrolls',        'count' => $counts['payroll'] ?? 0],
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'instances'   => $instances,
                'stats'       => $stats,
                'moduleTypes' => $moduleTypes,
                'filters'     => $request->only(['status', 'entity_type', 'date_from', 'date_to', 'search']),
            ]);
        }

        return Inertia::render('Employee/MyApprovals/Index', [
            'instances'   => $instances,
            'stats'       => $stats,
            'moduleTypes' => $moduleTypes,
            'filters'     => $request->only(['status', 'entity_type', 'date_from', 'date_to', 'search']),
        ]);
    }

    /**
     * Resolve a human-readable summary string for each entity type.
     */
    protected function resolveEntitySummary(WorkflowInstance $instance): string
    {
        try {
            switch ($instance->entity_type) {
                case 'leave_request':
                    $entity = \App\Models\LeaveRequest::with('leaveType:id,name')
                        ->find($instance->entity_id);
                    if (!$entity) return 'Leave Request';
                    return "Leave: {$entity->total_days} day(s) — {$entity->leaveType?->name} ({$entity->start_date} to {$entity->end_date})";

                case 'attendance_regularization':
                    $entity = \App\Models\AttendanceRegularization::find($instance->entity_id);
                    if (!$entity) return 'Regularization Request';
                    return "Regularization: {$entity->date} ({$entity->regularized_in_time}–{$entity->regularized_out_time})";

                case 'shift_swap':
                    $entity = \App\Models\ShiftSwap::with('recipient:id,first_name,last_name')
                        ->find($instance->entity_id);
                    if (!$entity) return 'Shift Swap';
                    $name = $entity->recipient?->first_name . ' ' . $entity->recipient?->last_name;
                    return "Shift Swap with {$name} on {$entity->date}";

                case 'expense':
                    $entity = \App\Models\Expense::with('category:id,name')
                        ->find($instance->entity_id);
                    if (!$entity) return 'Expense Claim';
                    return "Expense: ₹{$entity->amount} — {$entity->title} ({$entity->category?->name})";

                case 'timesheet':
                    $entity = \App\Models\Timesheet::find($instance->entity_id);
                    if (!$entity) return 'Timesheet';
                    return "Timesheet: Week of {$entity->week_start_date}";
                
                case 'overtime_request':
                    $entity = \App\Models\OvertimeRequest::find($instance->entity_id);
                    if (!$entity) return 'Overtime Request';
                    return "Overtime: {$entity->minutes} mins on {$entity->date->format('d M Y')}";

                case 'wfh_request':
                    $entity = \App\Models\WfhRequest::find($instance->entity_id);
                    if (!$entity) return 'WFH Request';
                    return "WFH Request for {$entity->date->format('d M Y')}";

                case 'floating_holiday_request':
                    $entity = \App\Models\FloatingHolidayRequest::with('holiday')->find($instance->entity_id);
                    if (!$entity) return 'Holiday Request';
                    return "Holiday: " . ($entity->holiday?->name ?? 'Floating Holiday');

                case 'payroll':
                    $entity = \App\Models\Payroll::find($instance->entity_id);
                    if (!$entity) return 'Payroll Processing';
                    return "Payroll: {$entity->month}/{$entity->year} — Batch: {$entity->batch_name}";

                default:
                    return ucfirst(str_replace('_', ' ', $instance->entity_type));
            }
        } catch (\Throwable $e) {
            return ucfirst(str_replace('_', ' ', $instance->entity_type));
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
