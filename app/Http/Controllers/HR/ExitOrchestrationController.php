<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeExit;
use App\Models\ExitClearance;
use App\Models\AssetAssignment;
use App\Models\Loan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExitOrchestrationController extends Controller
{
    /**
     * Display a consolidated view of an employee's exit progress.
     */
    public function show(Employee $employee)
    {
        $exit = EmployeeExit::where('employee_id', $employee->id)->first();
        if (!$exit) {
            return back()->with('error', 'No exit record found for this employee.');
        }

        // Aggregate Data
        $data = [
            'employee' => $employee->load('user', 'department'),
            'exit' => $exit,
            'assets' => AssetAssignment::where('employee_id', $employee->id)
                ->where('status', 'assigned')
                ->with('asset')
                ->get(),
            'loans' => Loan::where('employee_id', $employee->id)
                ->whereIn('status', ['Approved', 'Disbursed', 'Partially Paid'])
                ->with('product')
                ->get(),
            'clearances' => ExitClearance::where('employee_id', $employee->id)
                ->with('department', 'approver')
                ->get(),
            'payslips' => $employee->payslips()->latest()->take(3)->get(),
        ];

        return Inertia::render('HR/Exits/Orchestration', $data);
    }

    /**
     * Mark an asset as recovered or a clearance as completed.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:asset,clearance',
            'status' => 'required|string'
        ]);

        if ($request->type === 'clearance') {
            $clearance = ExitClearance::findOrFail($id);
            $clearance->update([
                'status' => $request->status,
                'cleared_by' => auth()->id(),
                'cleared_at' => now()
            ]);
        }

        return back()->with('success', 'Status updated successfully.');
    }
}
