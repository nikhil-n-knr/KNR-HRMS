<?php

namespace App\Http\Controllers\Exit;

use App\Http\Controllers\Controller;
use App\Models\ExitClearance;
use App\Services\Exit\ClearanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExitClearanceController extends Controller
{
    protected $service;

    public function __construct(ClearanceService $service)
    {
        $this->service = $service;
    }

    /**
     * Admin Dashboard: View All Pending Clearances
     */
    public function index(Request $request)
    {
        // Filter by Module (if logged in user is IT Head, show IT clearances)
        // For now, showing all for Admin. 
        // Real logic: $user->hasRole('IT_HEAD') -> where('module', 'it')
        
        $query = ExitClearance::with('employee:id,first_name,last_name,employee_code,department_id', 'employee.department')
            ->orderBy('status', 'asc') // Pending first
            ->orderBy('created_at', 'desc');

        if ($request->has('module')) {
            $query->where('module', $request->module);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Exit/Clearance/Index', [
            'clearances' => $query->paginate(15),
            'filters' => $request->only(['module', 'status'])
        ]);
    }

    /**
     * Employee View: My Clearance Status
     */
    public function myClearance(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) abort(404, 'Employee profile not found');

        $clearances = ExitClearance::where('employee_id', $employee->id)->get();
        $totalRecovery = $this->service->getTotalRecovery($employee);

        return Inertia::render('Exit/Clearance/MyClearance', [
            'clearances' => $clearances,
            'totalRecovery' => $totalRecovery
        ]);
    }
    
    /**
     * Action: Approve / Reject / Update Dues
     */
    public function update(Request $request, ExitClearance $clearance)
    {
        $request->validate([
            'status' => 'required|in:Pending,Cleared,Rejected,Hold',
            'due_amount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string'
        ]);

        // Security: Ensure User is allowed to update THIS module
        // if ($clearance->module !== 'it' && !auth()->user()->is_admin) abort(403);

        $this->service->updateStatus(
            $clearance,
            $request->status,
            $request->due_amount ?? 0,
            $request->remarks
        );

        return redirect()->back()->with('success', 'Clearance status updated.');
    }
    
    /**
     * Trigger Initiation manually (if not triggered by status change)
     */
    public function store(Request $request) {
        $request->validate(['employee_id' => 'required|exists:employees,id']);
        $employee = \App\Models\Employee::findOrFail($request->employee_id);
        
        $this->service->initiateClearance($employee);
        
        return redirect()->back()->with('success', 'Clearance process initiated.');
    }
}
