<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WfhRequest;
use App\Traits\FilterableByAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\Infrastructure\LoggerService;

class WfhController extends Controller
{
    use FilterableByAccess, \App\Traits\HasAttendanceHubData;

    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Display a listing of WFH requests.
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $query = WfhRequest::query()->with(['employee.department', 'approver']);

        // 1. Standard Filters
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

        $query->orderByDesc('date');

        $requests = $query->paginate(15)->withQueryString();

        $data = $this->getHubBaseData('wfh', $tenantId);
        $data['requests'] = $requests;
        $data['filters'] = $request->only(['search', 'department_id', 'location_id', 'status', 'start_date', 'end_date']);

        return Inertia::render('Admin/Attendance/Hub', $data);
    }

    /**
     * Update the specified resource in storage (Approve/Reject).
     */
    public function update(Request $request, $id)
    {
        $wfh = WfhRequest::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'rejection_reason' => 'nullable|string|required_if:status,Rejected',
        ]);

        $wfh->update([
            'status' => $validated['status'],
            'approved_by' => auth()->id(),
            'approved_at' => now(), // WFH has approved_at column
        ]);

        // Log Action
        $this->logger->log('attendance', 'wfh_update', "WFH Request #{$id} was {$validated['status']} by " . auth()->user()->name, ['request_id' => $id, 'user_id' => auth()->id()]);

        return back()->with('success', "WFH request marked as {$validated['status']}");
    }

    /**
     * Store a newly created WFH Request.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $employeeId = $request->input('employee_id'); 
        
        // If Admin is creating for someone else
        if ($user->hasRole(['Admin', 'Super Admin']) && $employeeId) {
             // used provided ID
        } else {
             // Self Request
              if (!$user->employee) {
                  return back()->with('error', 'Your account is not linked to an Employee Profile. Please contact HR.');
              }
              $employeeId = $user->employee->id;
        }

        $validated = $request->validate([
             'start_date' => 'required|date',
             'end_date' => 'required|date|after_or_equal:start_date',
             'reason' => 'required|string|max:255',
             'employee_id' => 'nullable|exists:employees,id' 
        ]);

        // Fix: WFH model logic (does it store From/To or single Date? Usually range)
        // Controller used 'date' in Overtime, here let's assume valid fields.
        // Checking WFH migration... typically 'date' or 'from_date', 'to_date'.
        // Assuming 'date' if single day, or range. 
        // Let's assume singular 'date' for simple implementation if range isn't supported, 
        // OR better: Create 1 record per day if range is passed but DB is single day.
        // For now, let's assume 'date' column based on `index` method query `orderByDesc('date')`.
        // So we likely iterate.
        
        $start = \Carbon\Carbon::parse($validated['start_date']);
        $end = \Carbon\Carbon::parse($validated['end_date']);
        
        // Fetch Holidays in Range
        $holidays = \App\Models\Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('type', 'Fixed') 
            ->pluck('date')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->toArray();
            
        $createdCount = 0;
        
        while ($start->lte($end)) {
            $dateStr = $start->toDateString();
            
            // Skip Sundays
            if ($start->isSunday()) {
                $start->addDay();
                continue;
            }
            
            // Skip Fixed Holidays
            if (in_array($dateStr, $holidays)) {
                $start->addDay();
                continue;
            }
            
            // Check for existing request
            $exists = WfhRequest::where('employee_id', $employeeId)->where('date', $dateStr)->exists();
            if (!$exists) {
                $wfh = WfhRequest::create([
                    'employee_id' => $employeeId,
                    'date' => $dateStr, 
                    'reason' => $validated['reason'],
                    'status' => 'Pending'
                ]);
                $createdCount++;

                // Trigger Workflow
                try {
                    $workflowService = app(\App\Services\WorkflowService::class);
                    $instance = $workflowService->initializeWorkflow('wfh', $wfh->id, auth()->user());

                    if (!$instance) {
                        // Auto-approve if no workflow configured
                        $wfh->update(['status' => 'Approved']);
                    }
                } catch (\Exception $e) {
                    \Log::error('WFH Workflow Error: ' . $e->getMessage());
                }
            }
            
            $start->addDay();
        }

        $this->logger->log('attendance', 'wfh_create', "WFH requested for Emp #{$employeeId}", ['count' => $createdCount]);

        if ($createdCount === 0) {
            // Check if it was because duplicates? Or just holidays/sundays?
            return back()->with('warning', 'No new requests created. Days might be Sundays, Holidays, or already requested.');
        }

        return back()->with('success', "WFH request created for {$createdCount} working days.");
    }

    public function myRequests(Request $request)
    {
        $user = auth()->user();
        if (!$user->employee) {
            return Inertia::render('Employee/Attendance/MyWfhRequests', [
                'requests' => ['data' => []],
                'error' => 'Your account is not linked to an Employee Profile. Please contact HR.'
            ]);
        }

        $requests = WfhRequest::where('employee_id', $user->employee->id)
            ->orderByDesc('date')
            ->paginate(10);

        return Inertia::render('Employee/Attendance/MyWfhRequests', [
            'requests' => $requests
        ]);
    }

    public function export(Request $request) 
    {
        // CSV Export
        $query = WfhRequest::query()->with('employee.department');
        
        if ($request->filled('start_date')) $query->where('date', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->where('date', '<=', $request->end_date);
        if ($request->filled('status')) $query->where('status', $request->status);

        $data = $query->get();
        
        $csvHeader = ['ID', 'Employee', 'Date', 'Reason', 'Status', 'Approved By'];
        
        $callback = function() use ($data, $csvHeader) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $csvHeader);
            foreach ($data as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->employee ? $row->employee->first_name . ' ' . $row->employee->last_name : '-',
                    $row->date,
                    $row->reason,
                    $row->status,
                    $row->approved_by
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=wfh_requests.csv",
            "Pragma" => "no-cache"
        ]);
    }
}
