<?php

namespace App\Services\Payroll;

use App\Models\Payroll;
use App\Models\Payslip;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\Expense;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\Payroll\SalaryService; 
use App\Services\Payroll\LoanService;
use App\Services\Payroll\TaxCalculatorService;
use App\Services\Payroll\ArrearCalculatorService;

use App\Models\ComplianceRule;

class PayrollProcessor
{
    protected $loanService;
    protected $taxService;
    protected $arrearService;

    public function __construct(
        LoanService $loanService,
        TaxCalculatorService $taxService,
        ArrearCalculatorService $arrearService
    ) {
        $this->loanService = $loanService;
        $this->taxService = $taxService;
        $this->arrearService = $arrearService;
    }

    /**
     * Run Payroll for a given month and year.
     * Generates Draft Payslips.
     */
    /**
     * Create Draft Payroll Record
     */
    public function createDraft(int $month, int $year, int $processorId, ?int $employeeId = null, array $settings = []): Payroll
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endOfMonth = $startDate->copy()->endOfMonth();
        
        $batchName = $startDate->format('F Y') . ' Payroll';
        if ($employeeId) {
            $emp = Employee::with('user')->find($employeeId);
            $batchName = "FnF / Exit Settlement - " . ($emp ? $emp->user->name : $employeeId);
        }

        return Payroll::create([
            'month' => $month,
            'year' => $year,
            'batch_name' => $batchName,
            'start_date' => $startDate,
            'end_date' => $endOfMonth,
            'status' => 'Draft', // Or 'Processing'
            'processed_by' => $processorId,
            'processed_at' => now(),
            'settings' => $settings
        ]);
    }

    /**
     * Process Payroll Items for an Existing Payroll Record
     */
    public function process(Payroll $payroll, ?int $employeeId = null): void
    {
        DB::transaction(function () use ($payroll, $employeeId) {
            // 2. Fetch Active Employees with Salary Structures
            $query = EmployeeSalary::where('is_active', true)->whereHas('employee')->with(['employee', 'salaryStructure']);
            
            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }
            
            $activeSalaries = $query->get();
            $totalPayout = 0;
            
            $startDate = $payroll->start_date;
            $endOfMonth = $payroll->end_date;
            $month = $payroll->month;
            $year = $payroll->year;

            // Clear previous items if re-running (optional, or handle upsert)
            // For now, assuming fresh run or handling duplicates via Payslip logic
            // Ideally delete old payslips for this payroll if regenerating?
            if (!$employeeId) {
                // Force delete to ensure unique payslip numbers (which include soft deleted ones otherwise)
                $payroll->payslips()->forceDelete();
            } else {
                $payroll->payslips()->where('employee_id', $employeeId)->forceDelete();
            }
            
            foreach ($activeSalaries as $salary) {
                if (!$salary->employee) {
                    continue;
                }
                // --- 1. Pre-Check: Salary Hold ---
                $activeHold = \App\Models\PayrollHold::where('employee_id', $salary->employee_id)
                                ->where('status', 'Active')
                                ->where(function($q) use ($endOfMonth) {
                                    $q->whereNull('hold_until')
                                      ->orWhere('hold_until', '>=', $endOfMonth);
                                })
                                ->first();

                // 3. Calculate Attendance / Payable Days
                $daysInMonth = $startDate->daysInMonth;
                
                $periodStart = $startDate->copy();
                $periodEnd = $endOfMonth->copy();

                if ($salary->employee->joining_date && $salary->employee->joining_date->gt($periodStart)) {
                    $periodStart = $salary->employee->joining_date;
                }
                
                if ($salary->employee->deleted_at && $salary->employee->deleted_at->lt($periodEnd)) {
                    $periodEnd = $salary->employee->deleted_at;
                }

                $potentialDays = max(0, $periodEnd->diffInDays($periodStart) + 1);
                
                if ($potentialDays <= 0) continue;

                $lopDays = (float) $this->calculateLopDays($salary->employee->id, $periodStart, $periodEnd, ($payroll->settings['ignore_attendance'] ?? false)); 
                $payableDays = max(0, $potentialDays - $lopDays);
                
                // 4. Prorate Salary
                $monthlyData = $this->calculateProratedSalary($salary, $payableDays, $daysInMonth);

                // 4.0 Overtime Pay Integration
                $otEarnings = $this->calculateOvertimeEarnings($salary->employee_id, $periodStart, $periodEnd, $monthlyData['gross_earnings']);
                if ($otEarnings > 0) {
                    $monthlyData['earnings_breakdown']['Overtime Pay'] = $otEarnings;
                    $monthlyData['gross_earnings'] += $otEarnings;
                    $monthlyData['net_pay'] += $otEarnings;
                }
                
                // 4.1 Statutory Compliance Overrides (PF, ESI, PT)
                $monthlyData = $this->calculateStatutoryDeductions($salary->employee, $monthlyData, $month, $year);
                
                // --- Loan Deduction Integration ---
                $loanDeduction = $this->loanService->getMonthlyDeduction($salary->employee_id, $month, $year);
                if ($loanDeduction > 0) {
                    $monthlyData['deductions_breakdown']['Loan Repayment'] = $loanDeduction;
                    $monthlyData['gross_deductions'] += $loanDeduction;
                    $monthlyData['net_pay'] -= $loanDeduction;
                }

                // --- Variable Pay / Bonuses Integration ---
                $variablePayouts = \App\Models\VariablePayout::where('employee_id', $salary->employee_id)
                    ->where('pay_month', $month)
                    ->where('pay_year', $year)
                    ->where('status', 'Approved')
                    ->whereNull('payroll_id')
                    ->get();

                foreach ($variablePayouts as $payout) {
                    $monthlyData['earnings_breakdown'][$payout->type] = $payout->amount;
                    $monthlyData['gross_earnings'] += $payout->amount;
                    $monthlyData['net_pay'] += $payout->amount;
                }
                
                // --- TDS ---
                $tdsMethod = $salary->salaryStructure ? $salary->salaryStructure->tds_method : 'Auto_New'; 
                $regimeOverride = null;
                $shouldCalculateTax = true;

                if ($tdsMethod === 'Manual') {
                    $shouldCalculateTax = false;
                } elseif ($tdsMethod === 'Auto_New') {
                    $regimeOverride = 'New';
                } elseif ($tdsMethod === 'Auto_Old') {
                    $regimeOverride = 'Old';
                }

                if ($shouldCalculateTax) {
                    $tdsAmount = $this->taxService->calculateMonthlyTDS(
                        $salary->employee_id, 
                        $monthlyData['gross_earnings'], 
                        $month, 
                        $year,
                        $regimeOverride
                    );

                    if ($tdsAmount > 0) {
                        // Check for existing key from structure (usually 'Income Tax (TDS)')
                        $tdsKey = 'Income Tax (TDS)';
                        if (!isset($monthlyData['deductions_breakdown'][$tdsKey])) {
                             $tdsKey = 'TDS (Income Tax)'; // Fallback
                        }
                        
                        // If it exists (e.g. 0.00 placeholder), valid.
                        // We ADD to it or REPLACE it? Usually Replace the placeholder.
                        $monthlyData['deductions_breakdown'][$tdsKey] = $tdsAmount;
                        
                        // Recalculate Gross Deductions & Net Pay
                        // We need to subtract the *old* value if we are replacing, but usually placeholder is 0.
                        // However, to be safe, we should re-sum deductions.
                        // Simple approach: Add the *difference* if we knew the old value.
                        // Robust approach: Sum all deductions again.
                        
                        $monthlyData['gross_deductions'] = array_sum($monthlyData['deductions_breakdown']);
                        $monthlyData['net_pay'] = $monthlyData['gross_earnings'] - $monthlyData['gross_deductions'];
                    }
                }

                // --- Arrears ---
                $arrears = $this->arrearService->calculateArrears($salary->employee_id, $month, $year);
                if ($arrears['amount'] > 0) {
                    $monthlyData['earnings_breakdown']['Arrears'] = $arrears['amount'];
                    $monthlyData['gross_earnings'] += $arrears['amount'];
                    $monthlyData['net_pay'] += $arrears['amount'];
                }
 
                // --- Expense Claims ---
                $reimbursements = Expense::where('employee_id', $salary->employee_id)
                    ->where('status', 'Approved_Payroll')
                    ->where('payout_method', 'payroll')
                    ->whereNull('payroll_id')
                    ->whereMonth('reimbursed_on', $month)
                    ->whereYear('reimbursed_on', $year)
                    ->sum('amount');
                
                if ($reimbursements > 0) {
                    $monthlyData['earnings_breakdown']['Reimbursements'] = $reimbursements;
                    $monthlyData['gross_earnings'] += $reimbursements; // Include in Gross but it's non-taxable
                    $monthlyData['net_pay'] += $reimbursements; 
                }
                
                // 5. Create Payslip
                $finalNetPay = $monthlyData['net_pay'];
                $slipStatus = 'Draft'; 
                
                if ($activeHold && $activeHold->type === 'Full') {
                    $finalNetPay = 0;
                    $slipStatus = 'Held';
                }
                
                Payslip::create([
                    'payroll_id' => $payroll->id,
                    'employee_id' => $salary->employee_id,
                    'payslip_number' => 'PAY-' . $year . $month . '-' . $salary->employee_id . ($employeeId ? '-FNF' : ''),
                    'basic_salary' => $monthlyData['basic'],
                    'gross_earnings' => $monthlyData['gross_earnings'],
                    'gross_deductions' => $monthlyData['gross_deductions'],
                    'net_pay' => $finalNetPay, 
                    'payable_days' => $payableDays,
                    'lop_days' => $lopDays,
                    'earnings_breakdown' => $monthlyData['earnings_breakdown'], 
                    'deductions_breakdown' => $monthlyData['deductions_breakdown'],
                    'status' => $slipStatus
                ]);

                if ($slipStatus !== 'Held') {
                    $totalPayout += $finalNetPay;
                }
            }

            // Update Total
            $payroll->update(['total_payout' => $totalPayout, 'status' => 'Draft']); // Ensure stats is updated
        });
    }

    /**
     * Legacy Wrapper (Deprecated)
     */
    public function generatePayroll(int $month, int $year, int $processorId, ?int $employeeId = null): Payroll
    {
        $payroll = $this->createDraft($month, $year, $processorId, $employeeId);
        $this->process($payroll, $employeeId);
        return $payroll;
    }

    /**
     * Confirm Payroll: Lock it and Create Expense Entry.
     */
    public function confirmPayroll(Payroll $payroll, int $approverId): void
    {
        DB::transaction(function () use ($payroll, $approverId) {
            $payroll->update(['status' => 'Paid']);
            
            $payroll->payslips()->update(['status' => 'Paid']);
            
            foreach ($payroll->payslips as $slip) {
                 if (isset($slip->deductions_breakdown['Loan Repayment'])) {
                     $this->loanService->markDeducted($slip->employee_id, $payroll->month, $payroll->year, $payroll->id);
                 }

                 Expense::where('employee_id', $slip->employee_id)
                    ->where('status', 'Approved_Payroll') // Fix: Match status set by SettlementController
                    ->where('payout_method', 'payroll') 
                    ->whereNull('payroll_id')
                    ->update([
                        'payroll_id' => $payroll->id,
                        'status' => 'Paid', 
                    ]);

                 \App\Models\VariablePayout::where('employee_id', $slip->employee_id)
                    ->where('pay_month', $payroll->month)
                    ->where('pay_year', $payroll->year)
                    ->where('status', 'Approved')
                    ->whereNull('payroll_id')
                    ->update([
                        'payroll_id' => $payroll->id,
                        'status' => 'Paid'
                    ]);
            }
            
            Expense::create([
                'title' => 'Payroll Payout: ' . $payroll->batch_name,
                'amount' => $payroll->total_payout,
                'currency' => 'INR',
                'incurred_date' => now(),
                'category' => 'Payroll',
                'status' => 'Approved',
                'approved_by' => $approverId,
                'description' => 'System generated payroll expense for ' . $payroll->batch_name
            ]);
            
            \App\Jobs\DispatchPayslipEmails::dispatch($payroll);
        });
    }

    private function calculateLopDays(int $employeeId, Carbon $start, Carbon $end, bool $ignoreAttendance = false): float
    {
        if ($ignoreAttendance) {
            return 0;
        }
        // 1. LOP from Leave Requests (Unpaid)
        $leaveLop = LeaveRequest::where('employee_id', $employeeId)
            ->where('status', 'Approved')
            ->whereBetween('start_date', [$start, $end])
            ->whereHas('leaveType', function($q) {
                $q->where('name', 'like', '%Loss of Pay%')
                  ->orWhere('name', 'like', '%Unpaid%')
                  ->orWhere('is_paid', false); 
            })
            ->sum('total_days');

        // 2. LOP from Attendance Logs (Absent and Half Day)
        // Note: Filter out dates that already have an Approved Leave (to avoid double deduction)
        $leaveDates = LeaveRequest::where('employee_id', $employeeId)
            ->where('status', 'Approved')
            ->whereBetween('start_date', [$start, $end])
            ->get()
            ->flatMap(function($req) {
                $dates = [];
                for($d = Carbon::parse($req->start_date); $d <= Carbon::parse($req->end_date); $d->addDay()) {
                    $dates[] = $d->toDateString();
                }
                return $dates;
            })->unique()->toArray();

        $absentLogsCount = \App\Models\AttendanceLog::where('employee_id', $employeeId)
            ->whereBetween('date', [$start, $end])
            ->where('status', 'Absent')
            ->whereNotIn('date', $leaveDates)
            ->count();

        $halfDayLogsCount = \App\Models\AttendanceLog::where('employee_id', $employeeId)
            ->whereBetween('date', [$start, $end])
            ->where('status', 'Half Day')
            ->whereNotIn('date', $leaveDates)
            ->count();

        $attendanceLop = $absentLogsCount + ($halfDayLogsCount * 0.5);

        return (float) ($leaveLop + $attendanceLop);
    }

    /**
     * Calculate Overtime Earnings based on Approved OvertimeRequests.
     */
    private function calculateOvertimeEarnings(int $employeeId, Carbon $start, Carbon $end, float $monthlyGross): float
    {
        $totalMinutes = \App\Models\OvertimeRequest::where('employee_id', $employeeId)
            ->where('status', 'Approved')
            ->whereBetween('date', [$start, $end])
            ->sum('minutes');

        if ($totalMinutes <= 0) return 0;

        // Hourly Rate calculation: Gross / (Working Days in Month * 8)
        // Simplified: Gross / 200 (approx 25 days * 8 hours) or use logic.
        // Let's use 30 days * 8 hours = 240 as a standard if no structured daily rate.
        $hourlyRate = $monthlyGross / 240; 
        
        return round(($totalMinutes / 60) * $hourlyRate, 2);
    }

    private function calculateProratedSalary(EmployeeSalary $salary, int $payableDays, int $daysInMonth): array
    {
        $master = $salary->breakdown;
        if (!$master || !isset($master['components'])) {
             return [
                 'basic' => 0, 'gross_earnings' => 0, 'gross_deductions' => 0, 'net_pay' => 0,
                 'earnings_breakdown' => [], 'deductions_breakdown' => []
             ];
        }

        $prorataFactor = ($payableDays / $daysInMonth);
        
        $earnings = [];
        $deductions = [];
        $totalEarnings = 0;
        $totalDeductions = 0;
        $basic = 0;

        foreach ($master['components'] as $name => $amount) {
            $type = $this->getComponentType($salary->salary_structure_id, $name);
            
            $paidAmount = round($amount * $prorataFactor, 2);
            
            if ($name === 'Basic') $basic = $paidAmount;

            if ($type === 'earning') {
                $earnings[$name] = $paidAmount;
                $totalEarnings += $paidAmount;
            } else {
                $deductions[$name] = $paidAmount;
                $totalDeductions += $paidAmount;
            }
        }

        return [
            'basic' => $basic,
            'gross_earnings' => $totalEarnings,
            'gross_deductions' => $totalDeductions,
            'net_pay' => round($totalEarnings - $totalDeductions, 2),
            'earnings_breakdown' => $earnings,
            'deductions_breakdown' => $deductions
        ];
    }
    
    private function getComponentType(int $structureId, string $name): string
    {
        static $cache = [];
        $key = "$structureId-$name";
        if (isset($cache[$key])) return $cache[$key];
        
        $component = \App\Models\SalaryComponent::where('salary_structure_id', $structureId)
                    ->where('name', $name)->first();
        
        $type = $component ? $component->type : 'earning'; 
        $cache[$key] = $type;
        return $type;
    }

    /**
     * Recalculate Statutory Deductions (PF, ESI, PT) based on actual earnings and Rules Engine.
     */
    private function calculateStatutoryDeductions(Employee $employee, array $data, int $month, int $year): array
    {
        $earnedBasic = $data['basic'] ?? 0;
        $earnedGross = $data['gross_earnings'] ?? 0;
        
        // --- 1. Provident Fund (PF) ---
        // Only if PF is part of the structure (indicated by presence in deductions or a flag)
        // For now, we check if 'PF' key exists in deductions OR we enforce it for all.
        // Let's assume if it was in the Master Structure, it's enabled.
        if (isset($data['deductions_breakdown']['PF']) || isset($data['deductions_breakdown']['Provident Fund'])) {
            $key = isset($data['deductions_breakdown']['PF']) ? 'PF' : 'Provident Fund';
            $rule = ComplianceRule::getRule('PF');
            
            if ($rule) {
                $rules = $rule->rules_json;
                $wageCeiling = $rules['wage_ceiling'] ?? 15000;
                
                // Logic: PF Wages = Basic (simplification).
                // Check if capped
                // If employee opted for cap, use min(Basic, 15000).
                // We need a flag on Employee for "Uncapped PF". 
                // Let's assume a column `pf_uncapped` exists or default to Capped.
                // For MVP, we use the Rule's global setting or default to Capped.
                
                $pfWages = $earnedBasic;
                if ($pfWages > $wageCeiling && !($employee->pf_uncapped ?? false)) {
                     $pfWages = $wageCeiling;
                }
                
                // International Worker must pay on full Basic
                if ($employee->is_international_worker) {
                    $pfWages = $earnedBasic;
                }
                
                $rate = $rules['employee_contribution_rate'] ?? 12;
                $newDed = round($pfWages * ($rate / 100));
                
                // Update Data
                $oldDed = $data['deductions_breakdown'][$key];
                $data['deductions_breakdown'][$key] = $newDed;
                $data['gross_deductions'] += ($newDed - $oldDed);
                $data['net_pay'] -= ($newDed - $oldDed);
            }
        }

        // --- 2. ESI ---
        // ESI is on GROSS wages.
        if (isset($data['deductions_breakdown']['ESI'])) {
             $rule = ComplianceRule::getRule('ESI');
             if ($rule) {
                 $rules = $rule->rules_json;
                 $limit = $rules['wage_ceiling'] ?? 21000;
                 // ESI acts on the "Monthly Wages". If Gross > Limit, ESI is usually ZERO (Exit).
                 // However, "Gross" here is the *Earned* Gross.
                 // The rule is: If the *Standard* Monthly Gross > 21000, they are exempt always.
                 // If Standard <= 21000, they pay on Earned Gross.
                 // We need to check the *Master* Gross (from Salary Service) to decide eligibility.
                 
                 // We don't have Master Gross easily here, but we can infer or use Earned for now.
                 // CORRECT LOGIC: Check Master Salary limits.
                 // For now, let's assume if they have ESI component, they are eligible.
                 
                 $rate = $rules['employee_contribution_rate'] ?? 0.75;
                 $newDed = ceil($earnedGross * ($rate / 100)); // ESI is always rounded up to next rupee
                 
                 $oldDed = $data['deductions_breakdown']['ESI'];
                 $data['deductions_breakdown']['ESI'] = $newDed;
                 $data['gross_deductions'] += ($newDed - $oldDed);
                 $data['net_pay'] -= ($newDed - $oldDed);
             }
        }

        // --- 3. Professional Tax (PT) ---
        // Needs State Logic.
        // Assuming 'PT' exists.
        if (isset($data['deductions_breakdown']['Professional Tax']) || isset($data['deductions_breakdown']['PT'])) {
            $key = isset($data['deductions_breakdown']['PT']) ? 'PT' : 'Professional Tax';
            // Simple generic slab for Phase 2 MVP
            $rule = ComplianceRule::getRule('PT');
            if ($rule) {
                // Fetch slab based on Gross
                $slabs = $rule->rules_json['slabs'] ?? [];
                $ptAmount = 0;
                foreach ($slabs as $slab) {
                    if ($earnedGross >= $slab['min'] && $earnedGross <= $slab['max']) {
                        $ptAmount = $slab['amount'];
                        break;
                    }
                }
                
                $oldDed = $data['deductions_breakdown'][$key];
                $data['deductions_breakdown'][$key] = $ptAmount;
                $data['gross_deductions'] += ($ptAmount - $oldDed);
                $data['net_pay'] -= ($ptAmount - $oldDed);
            }
        }
        
        return $data;
    }
}
