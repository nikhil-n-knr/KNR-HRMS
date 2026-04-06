<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\VariablePayout;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VariablePayoutController extends Controller
{
    public function index(Request $request)
    {
        $query = VariablePayout::with(['employee.user', 'creator'])->latest();
        
        if ($request->status) {
            $query->where('status', $request->status);
        }

        return Inertia::render('HR/Payroll/Variable/Index', [
            'payouts' => $query->paginate(15),
            'employees' => Employee::with('user')->get()->map(function($e) {
                return ['id' => $e->id, 'name' => $e->user->name . ' (' . $e->employee_code . ')'];
            })
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|string', // "Performance Bonus", "Commission"
            'pay_month' => 'required|integer|min:1|max:12',
            'pay_year' => 'required|integer|min:2024',
            'remarks' => 'nullable|string'
        ]);

        VariablePayout::create([
            'employee_id' => $request->employee_id,
            'amount' => $request->amount,
            'type' => $request->type,
            'pay_month' => $request->pay_month,
            'pay_year' => $request->pay_year,
            'remarks' => $request->remarks,
            'status' => 'Pending',
            'created_by' => auth()->id()
        ]);

        return back()->with('success', 'Variable Pay Drafted');
    }

    public function update(Request $request, VariablePayout $variable)
    {
         // Approve / Reject
         $request->validate(['action' => 'required|in:approve,reject']);
         
         if ($request->action === 'approve') {
             $variable->update([
                 'status' => 'Approved', 
                 'approved_by' => auth()->id()
             ]);
             return back()->with('success', 'Payout Approved');
         } else {
             $variable->update([
                 'status' => 'Rejected', 
                 'approved_by' => auth()->id()
             ]);
             return back()->with('success', 'Payout Rejected');
         }
    }
    
    public function destroy(VariablePayout $variable) {
        if ($variable->status !== 'Pending') {
            return back()->with('error', 'Cannot delete processed payout');
        }
        $variable->delete();
        return back()->with('success', 'Deleted');
    }
}
