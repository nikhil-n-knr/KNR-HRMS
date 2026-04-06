<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\PayrollHold;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayrollHoldController extends Controller
{
    public function index(Request $request)
    {
        $query = PayrollHold::with('employee.user')->latest();
        
        if($request->status) {
            $query->where('status', $request->status);
        }

        return Inertia::render('HR/Payroll/Holds/Index', [
            'holds' => $query->paginate(15),
            'employees' => Employee::with('user')->get()->map(function($e) {
                return ['id' => $e->id, 'name' => $e->user->name . ' (' . $e->employee_code . ')'];
            })
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'reason' => 'required|string|max:255',
            'type' => 'required|in:Full,Partial',
            'amount' => 'nullable|numeric|required_if:type,Partial',
            'hold_until' => 'nullable|date'
        ]);

        PayrollHold::create([
            'employee_id' => $request->employee_id,
            'reason' => $request->reason,
            'type' => $request->type,
            'amount' => $request->amount,
            'hold_until' => $request->hold_until,
            'status' => 'Active',
            'created_by' => auth()->id()
        ]);

        return back()->with('success', 'Salary Hold Applied');
    }

    public function update(Request $request, PayrollHold $hold)
    {
        // Used for Release action mainly
        if ($request->action === 'release') {
            $hold->update(['status' => 'Released']);
            return back()->with('success', 'Salary Hold Released');
        }

        return back()->with('error', 'Invalid action');
    }

    public function destroy(PayrollHold $hold)
    {
        $hold->delete();
        return back()->with('success', 'Hold Record Deleted');
    }
}
