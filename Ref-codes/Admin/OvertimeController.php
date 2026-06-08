<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use App\Models\Message; // For notifications
use App\Models\Employee;
use App\Traits\FilterableByAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\Infrastructure\LoggerService;

class OvertimeController extends Controller
{
    use FilterableByAccess, \App\Traits\HasAttendanceHubData;

    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Display a listing of overtime requests.
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $query = OvertimeRequest::query()->with(['employee.department', 'approver']);

        // 1. Apply Standard Employee Filters (Name, Dept, Loc)
        $query->whereHas('employee', function ($q) use ($request) {
            $this->scopeApplyStandardFilters($q, $request);
        });

        // 2. Date Filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        // 3. Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // 4. Sorting
        $query->orderByDesc('date');

        $requests = $query->paginate(15)->withQueryString();

        $data = $this->getHubBaseData('overtime', $tenantId);
        $data['requests'] = $requests;
        $data['filters'] = $request->only(['search', 'department_id', 'location_id', 'status', 'start_date', 'end_date']);

        return Inertia::render('Admin/Attendance/Hub', $data);
    }

    /**
     * Update the specified resource in storage.
     * Use this for Approve/Reject actions.
     */
    public function update(Request $request, $id)
    {
        $overtime = OvertimeRequest::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'rejection_reason' => 'nullable|string|required_if:status,Rejected',
        ]);

        $overtime->update([
            'status' => $validated['status'],
            'approved_by' => auth()->id(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        // Log Action
        $this->logger->log('attendance', 'overtime_update', "Overtime Request #{$id} was {$validated['status']} by " . auth()->user()->name, ['request_id' => $id, 'user_id' => auth()->id()]);

        return back()->with('success', "Overtime request marked as {$validated['status']}");
    }

    /**
     * Store a newly created Overtime Request.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $employeeId = $request->input('employee_id') ?? $user->employee_id; // Admin might set ID, Employee uses own
        
        // If Admin is creating for someone else
        if ($user->hasRole(['Admin', 'Super Admin']) && $request->filled('employee_id')) {
            $employeeId = $request->employee_id;
        } else {
             // Self Request
             if (!$user->employee) {
                 return back()->with('error', 'Your account is not linked to an Employee Profile. Please contact HR.');
             }
             $employeeId = $user->employee->id;
        }

        $validated = $request->validate([
             'date' => 'required|date',
             'hours' => 'required|numeric|min:0.5|max:12', // Max 12 hours OT
             'reason' => 'required|string|max:255',
             'employee_id' => 'nullable|exists:employees,id', // Helper for admin
             'project_id' => 'nullable|exists:projects,id',
             'task_id' => 'nullable|exists:project_tasks,id'
        ]);

        $ot = OvertimeRequest::create([
            'employee_id' => $employeeId,
            'date' => $validated['date'],
            'minutes' => $validated['hours'] * 60, // Convert to minutes
            'reason' => $validated['reason'],
            'status' => 'Pending',
            'project_id' => $validated['project_id'] ?? null,
            'task_id' => $validated['task_id'] ?? null,
        ]);

        // Trigger Workflow
        try {
            $workflowService = app(\App\Services\WorkflowService::class);
            $instance = $workflowService->initializeWorkflow('overtime', $ot->id, auth()->user());

            if (!$instance) {
                // Auto-approve if no workflow configured
                $ot->update(['status' => 'Approved']);
                $this->logger->log('attendance', 'overtime_auto_approve', "Overtime #{$ot->id} auto-approved (No workflow)", ['id' => $ot->id]);
                return back()->with('success', 'Overtime request submitted and auto-approved.');
            }
        } catch (\Exception $e) {
            \Log::error('Overtime Workflow Error: ' . $e->getMessage());
        }

        $this->logger->log('attendance', 'overtime_create', "Overtime requested for Emp #{$employeeId}", ['id' => $ot->id]);

        return back()->with('success', 'Overtime request submitted for approval.');
    }

    /**
     * Employee specific view for "My Requests"
     */
    public function myRequests(Request $request)
    {
        $user = auth()->user();
        if (!$user->employee) {
            return Inertia::render('Employee/Attendance/MyOvertimeRequests', [
                'requests' => ['data' => []],
                'error' => 'Your account is not linked to an Employee Profile. Please contact HR.'
            ]);
        }

        $requests = OvertimeRequest::where('employee_id', $user->employee->id)
            ->orderByDesc('date')
            ->paginate(10);

        return Inertia::render('Employee/Attendance/MyOvertimeRequests', [ // We will need to create this vue
            'requests' => $requests
        ]);
    }

    public function export(Request $request) 
    {
        // Simple CSV Export
        $query = OvertimeRequest::query()->with('employee.department');
        
        // Apply Filters (Reuse logic or keep simple)
        if ($request->filled('start_date')) $query->where('date', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->where('date', '<=', $request->end_date);
        if ($request->filled('status')) $query->where('status', $request->status);

        $data = $query->get();
        
        $csvHeader = ['ID', 'Employee', 'Date', 'Hours', 'Reason', 'Status', 'Approved By'];
        
        $callback = function() use ($data, $csvHeader) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $csvHeader);
            
            foreach ($data as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->employee ? $row->employee->first_name . ' ' . $row->employee->last_name : '-',
                    $row->date,
                    $row->hours,
                    $row->reason,
                    $row->status,
                    $row->approved_by
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=overtime_requests.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }
}
