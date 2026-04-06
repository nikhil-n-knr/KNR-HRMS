<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeExit;
use App\Models\FnFItem;
use App\Services\Payroll\PayrollProcessor;
use App\Services\Exit\ClearanceService;
use App\Services\Payroll\LeaveEncashmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

class SettlementController extends Controller
{
    protected $clearanceService;
    protected $encashmentService;

    public function __construct(
        PayrollProcessor $payrollProcessor,
        \App\Services\Exit\ClearanceService $clearanceService,
        \App\Services\Payroll\LeaveEncashmentService $encashmentService
    ) {
        $this->payrollProcessor = $payrollProcessor;
        $this->clearanceService = $clearanceService;
        $this->encashmentService = $encashmentService;
    }

    /**
     * Show Separation & Settlement Hub
     */
    public function create(Employee $employee)
    {
        $employee->load(['latestSalary.structure', 'department', 'user']);
        
        // 1. Ensure Clearance is initiated if record exists or status is resigned/terminated
        $exit = \App\Models\EmployeeExit::where('employee_id', $employee->id)->first();
        if ($exit) {
            $this->clearanceService->initiateClearance($employee);
        }

        // 2. Gather Data for Hub
        $matrix = $this->clearanceService->getMatrix($employee);
        
        // 3. Mock/Calculate Leaves (Placeholder for actual quota service)
        $totalLeaves = 15.5; // Example: Pull from LeaveService if available

        return Inertia::render('HR/Settlement/SettlementHub', [
            'employee' => $employee,
            'exit_record' => $exit,
            'clearance_matrix' => $matrix,
            'total_leaves' => $totalLeaves,
            'last_working_day' => $exit->last_working_day_approved ?? today()->format('Y-m-d'),
            'gratuity_eligible' => $employee->joining_date && Carbon::parse($employee->joining_date)->diffInYears(now()) >= 5
        ]);
    }

    /**
     * Process Settlement (Full F&F)
     */
    public function store(Request $request, Employee $employee)
    {
        // 1. Policy Enforcement: Check if fully cleared
        if (!$this->clearanceService->isFullyCleared($employee)) {
             return back()->withErrors(['error' => 'Employee must be cleared by all departments (IT/Finance/Admin) before final settlement.'])
                 ->setStatusCode(303);
        }

        $request->validate([
            'last_working_day' => 'required|date',
            'leave_encashment_days' => 'nullable|numeric|min:0',
            'notice_shortfall_days' => 'nullable|numeric|min:0',
            'gratuity_amount' => 'nullable|numeric|min:0',
            'recovery_amount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string'
        ]);

        // 2. Get or Create Exit Record
        $exit = \App\Models\EmployeeExit::firstOrCreate(
            ['employee_id' => $employee->id],
            [
                'resignation_date' => now()->subMonth(), 
                'last_working_day_approved' => $request->last_working_day,
                'status' => 'approved' 
            ]
        );
        
        $exit->update(['last_working_day_approved' => $request->last_working_day]);

        // 3. Mark Employee as Terminated
        $employee->update(['status' => 'terminated', 'exit_date' => $request->last_working_day]);

        // 4. Clear Old FnF Items
        $exit->fnfItems()->delete();

        // 5. Save New FnF Items (Credits)
        
        // A. Leave Encashment (Service Based)
        if ($request->leave_encashment_days > 0) {
            $calc = $this->encashmentService->calculate($employee, $request->leave_encashment_days);
            FnFItem::create([
                'exit_id' => $exit->id,
                'component_name' => "Leave Encashment (#{$request->leave_encashment_days} Days)",
                'type' => 'earning',
                'amount' => $calc['amount'],
                'is_taxable' => true
            ]);
        }

        // B. Gratuity (Statutory)
        if ($request->gratuity_amount > 0) {
             FnFItem::create([
                'exit_id' => $exit->id,
                'component_name' => 'Gratuity',
                'type' => 'earning',
                'amount' => $request->gratuity_amount,
                'is_taxable' => false
            ]);
        }

        // 6. Save Recoveries (Deductions)

        // A. Automatic Clearance Recovery
        $clearanceRecovery = $this->clearanceService->getTotalRecovery($employee);
        if ($clearanceRecovery > 0) {
            FnFItem::create([
                'exit_id' => $exit->id,
                'component_name' => 'Departmental Clearance Recovery',
                'type' => 'deduction',
                'amount' => $clearanceRecovery
            ]);
        }

        // B. Notice Shortfall
        if ($request->notice_shortfall_days > 0) {
             $monthlyGross = ($employee->latestSalary->annual_ctc ?? 0) / 12;
             $shortfallAmount = round(($monthlyGross / 30) * $request->notice_shortfall_days);
             
             FnFItem::create([
                'exit_id' => $exit->id,
                'component_name' => "Notice Shortfall (#{$request->notice_shortfall_days} Days)",
                'type' => 'deduction',
                'amount' => $shortfallAmount
            ]);
        }
        
        // 7. Generate Final Payroll
        $lwd = Carbon::parse($request->last_working_day);
        $payroll = $this->payrollProcessor->generatePayroll(
            $lwd->month, 
            $lwd->year, 
            auth()->id(), 
            $employee->id
        );

        // 8. Inject FnF Items into Payslip
        $payslip = $payroll->payslips()->first();
        if ($payslip) {
            $breakdown = $payslip->salary_breakdown;
            $items = $exit->fnfItems;
            
            foreach($items as $item) {
                if ($item->type === 'earning') {
                    $breakdown['breakup'][$item->component_name] = ['type' => 'earning', 'amount' => $item->amount];
                    $payslip->gross_pay += $item->amount;
                    $payslip->net_pay += $item->amount;
                } else {
                    $breakdown['breakup'][$item->component_name] = ['type' => 'deduction', 'amount' => $item->amount];
                    $payslip->deductions += $item->amount;
                    $payslip->net_pay -= $item->amount;
                }
            }
            $payslip->salary_breakdown = $breakdown;
            $payslip->save();
        }

        return redirect()->route('hr.payroll.show', $payroll->id)->with('success', 'Full & Final Settlement Processed')
            ->setStatusCode(303);
    }
}
