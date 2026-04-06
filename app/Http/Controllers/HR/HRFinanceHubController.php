<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\EmployeeSalary;
use App\Models\VariablePayout; // Assumptions
use Illuminate\Support\Facades\DB;

class HRFinanceHubController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'compensation');

        $data = ['tab' => $tab];

        // Conditional Data Loading
        if ($tab === 'compensation') {
            $data['structures'] = \App\Models\SalaryStructure::with('components')->get();
            $data['deferredPayments'] = \App\Models\FuturePayment::with('employee.user')->latest()->get();
            $data['employees'] = Employee::with('user')->get()->map(function($e) {
                return [
                    'id' => $e->id,
                    'first_name' => $e->user->name ?? $e->first_name,
                    'last_name' => '' // Handle if user only names
                ];
            });
        } elseif ($tab === 'variable') {
           // $data['pending_payouts'] = VariablePayout::where('status', 'Pending')->get();
            $data['employees'] = Employee::select('id', 'first_name', 'last_name')->get(); // For selection
        } elseif ($tab === 'payroll') {
            $data['payrolls'] = Payroll::latest()->take(5)->get(); // Recent runs
            if ($request->has('payroll_id')) {
                $data['selectedPayroll'] = Payroll::find($request->payroll_id);
                $data['payslips'] = \App\Models\Payslip::with('employee.user')
                                        ->where('payroll_id', $request->payroll_id)
                                        ->get();
            }
        } elseif ($tab === 'appraisals') {
            $data['employees'] = Employee::with(['latestSalary', 'department', 'user'])
                                    ->where('status', 'active')
                                    ->get()
                                    ->map(function($emp) {
                                        return [
                                            'id' => $emp->id,
                                            'name' => $emp->user->name ?? $emp->first_name,
                                            'code' => $emp->employee_code,
                                            'department' => $emp->department->name ?? '-',
                                            'current_ctc' => $emp->latestSalary->annual_ctc ?? 0,
                                            'current_effective_date' => $emp->latestSalary->effective_date ?? '-'
                                        ];
                                    });
        } elseif ($tab === 'exit') {
            // Exits in progress
            $data['exits'] = \App\Models\EmployeeExit::with('employee')->where('status', '!=', 'completed')->get();
        }

        return Inertia::render('HR/Finance/Hub', $data);
    }

    public function storeDeferred(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|string',
            'amount' => 'required|numeric',
            'due_date' => 'required|date'
        ]);

        \App\Models\FuturePayment::create($request->all());

        return back()->with('success', 'Future Payment Scheduled!');
    }

    public function storeAdhoc(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'type' => 'required|string',
            'calculation_type' => 'required|in:flat,percentage_basic',
            'amount' => 'nullable|numeric',
            'percentage' => 'nullable|numeric',
            'target_audience' => 'required|in:all,individual',
            'employee_ids' => 'required_if:target_audience,individual|array'
        ]);

        $employees = [];
        if ($request->target_audience === 'all') {
            $employees = Employee::where('status', 'active')->get();
        } else {
            $employees = Employee::whereIn('id', $request->employee_ids)->get();
        }

        $payouts = [];
        $now = now();
        
        foreach ($employees as $emp) {
            $amount = 0;
            if ($request->calculation_type === 'flat') {
                $amount = $request->amount;
            } else {
                // Percentage of Basic
                // Fetch latest salary or basic component
                // For MVP, assuming a standard basic calculation or fetching from EmployeeSalary
                $salary = EmployeeSalary::where('employee_id', $emp->id)->where('is_active', true)->first();
                $annualCtc = $salary ? $salary->annual_ctc : 0;
                $monthlyBasic = ($annualCtc / 12) * 0.4; // 40% of CTC as Basic approximation
                $amount = ($monthlyBasic * $request->percentage) / 100;
            }

            $payouts[] = [
                'employee_id' => $emp->id,
                'amount' => round($amount, 2),
                'type' => $request->type, // Bonus, Incentive
                'remarks' => $request->title,
                'pay_month' => $request->pay_month,
                'pay_year' => $request->pay_year,
                'status' => 'Pending',
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        \App\Models\VariablePayout::insert($payouts);

        return back()->with('success', count($payouts) . ' Payouts Added Successfully!');
    }

    public function updatePayslip(\App\Models\Payslip $payslip, Request $request)
    {
        if ($payslip->payroll->status !== 'Draft') {
            return back()->with('error', 'Cannot edit a locked payroll.');
        }

        $request->validate([
            'adjustment_amount' => 'required|numeric',
            'reason' => 'required|string',
            'type' => 'required|in:allowance,deduction'
        ]);

        if ($request->type === 'allowance') {
            $breakdown = $payslip->earnings_breakdown ?? [];
            $breakdown['Manual Adjustment'] = ($breakdown['Manual Adjustment'] ?? 0) + $request->adjustment_amount;
            $payslip->earnings_breakdown = $breakdown;
            $payslip->gross_earnings += $request->adjustment_amount;
            $payslip->net_pay += $request->adjustment_amount;
        } else {
             $breakdown = $payslip->deductions_breakdown ?? [];
             $breakdown['Manual Adjustment'] = ($breakdown['Manual Adjustment'] ?? 0) + $request->adjustment_amount;
             $payslip->deductions_breakdown = $breakdown;
             $payslip->gross_deductions += $request->adjustment_amount;
             $payslip->net_pay -= $request->adjustment_amount;
        }
        
        $payslip->save();

        return back()->with('success', 'Payslip Adjusted Successfully!');
    }

    public function storeAppraisals(Request $request, \App\Services\Payroll\SalaryService $salaryService)
    {
        $request->validate([
            'revisions' => 'required|array',
            'revisions.*.employee_id' => 'required|exists:employees,id',
            'revisions.*.new_ctc' => 'required|numeric|min:0',
            'revisions.*.effective_date' => 'required|date'
        ]);

        $count = 0;
        
        DB::transaction(function () use ($request, $salaryService) {
            foreach ($request->revisions as $rev) {
                $employee = Employee::with('latestSalary')->find($rev['employee_id']);
                
                // Get current structure ID to maintain it (Appraisal usually changes CTC, not Structure)
                // If no structure exists, we might need a fallback or fail. For now, skip or use existing.
                $structureId = $employee->latestSalary?->salary_structure_id;
                
                if (!$structureId) {
                    continue; 
                }

                // Gap Fix: Prevent backdating that deactivates a future/current salary
                // If the new effective date is OLDER than the current active salary, we skip (or should handle differently).
                if ($employee->latestSalary && $rev['effective_date'] < $employee->latestSalary->effective_date) {
                    // Skip to prevent regression (in a real app, we might insert it as inactive history)
                    continue; 
                }

                $salaryService->assignSalary(
                    $employee,
                    $structureId,
                    $rev['new_ctc'],
                    $rev['effective_date'],
                    'Annual Appraisal'
                );
                $count++;
            }
        });

        return back()->with('success', "Processed $count Salary Revisions!");
    }
        public function storeExit(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'resignation_date' => 'required|date',
            'last_working_day_proposed' => 'required|date',
            'reason_details' => 'nullable|string'
        ]);

        \App\Models\EmployeeExit::create([
            'employee_id' => $request->employee_id,
            'resignation_date' => $request->resignation_date,
            'last_working_day_proposed' => $request->last_working_day_proposed,
            'last_working_day_approved' => $request->last_working_day_proposed, // Auto-approve proposed for MVP
            'reason_details' => $request->reason_details,
            'status' => 'Resigned'
        ]);

        return back()->with('success', 'Resignation Initiated');
    }

    public function updateExitStage(Request $request, \App\Models\EmployeeExit $exit)
    {
        $request->validate(['status' => 'required|string']);
        $exit->update(['status' => $request->status]);
        return back()->with('success', 'Exit Stage Updated');
    }
}

