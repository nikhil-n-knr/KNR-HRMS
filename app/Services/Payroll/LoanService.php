<?php

namespace App\Services\Payroll;

use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\LoanProduct;
use App\Services\Loan\LoanEngineService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LoanService
{
    protected $engine;

    public function __construct(LoanEngineService $engine)
    {
        $this->engine = $engine;
    }
    /**
     * Create a new Loan request and generate tentative schedule.
     */
    public function requestLoan($data, $userId)
    {
        $user = \App\Models\User::find($userId);
        
        // 1. Identify Product
        $product = LoanProduct::findOrFail($data['loan_product_id']);

        // 2. Check Eligibility
        $eligibility = $this->engine->calculateEligibility($user, $product);
        if (!$eligibility['eligible']) {
             throw new \Exception($eligibility['reason']);
        }
        
        // Check Cap
        if ($data['amount'] > $eligibility['max_amount']) {
             throw new \Exception("Requested amount exceeds eligibility limit of " . $eligibility['max_amount']);
        }
        
        // 3. Calculate Interest Rate Preview
        $rate = $this->engine->calculateInterestRate($product, $data['amount'], $data['tenure']);

        // 4. Calculate Tentative EMI (Flat Estimate for UI, refined on approval)
        // Simple preview logic: P + I
        $totalInterest = $data['amount'] * ($rate / 100) * ($data['tenure'] / 12);
        $emi = ($data['amount'] + $totalInterest) / $data['tenure'];

        return DB::transaction(function () use ($data, $user, $product, $rate, $emi) {
            $employee = $user->employee;
            
            $loan = Loan::create([
                'employee_id' => $employee->id,
                'loan_product_id' => $product->id,
                'loan_type' => $product->name, // Keeping redundancy or remove?
                'principal_amount' => $data['amount'],
                'tenure_months' => $data['tenure'],
                'reason' => $data['reason'] ?? null,
                'status' => 'Pending',
                'monthly_installment' => round($emi, 2),
                'interest_rate' => $rate,
                'interest_type_applied' => $product->interest_type,
                'interest_rate_applied' => $rate,
            ]);

            return $loan;
        });
    }

    /**
     * Approve Loan and Generate Repayment Schedule
     */
    public function approveLoan(Loan $loan, $approverId)
    {
        return DB::transaction(function () use ($loan, $approverId) {
            // Recalculate Final Schedule using Locked Rate
            $schedule = $this->engine->generateAmortizationSchedule(
                $loan->principal_amount,
                $loan->interest_rate_applied,
                $loan->tenure_months,
                $loan->interest_type_applied ?? 'Flat'
            );
            
            $loan->update([
                'status' => 'Approved',
                'approved_by' => $approverId,
                'approved_at' => now(),
                'monthly_installment' => $schedule[0]['emi'] ?? 0, // Update to actual EMI
            ]);
            
            // Generate Schedule Records
            $startDate = Carbon::now()->addMonth()->startOfMonth();

            foreach ($schedule as $i => $row) {
                // Determine Date: 1st month = index 0 ?? No, schedule is 1-based usually
                // row['month'] is 1..N
                $date = $startDate->copy()->addMonths($row['month'] - 1);
                
                LoanRepayment::create([
                    'loan_id' => $loan->id,
                    'scheduled_date' => $date,
                    'amount' => $row['emi'],
                    'status' => 'Pending',
                    'principal_component' => $row['principal_component'],
                    'interest_component' => $row['interest_component'],
                    'outstanding_balance' => $row['balance']
                ]);
            }
            
            // Disburse immediately? NO. We have a manual disbursement step in HR Dashboard.
            // $loan->update(['disbursement_date' => now(), 'status' => 'Active']);
        });
    }

    /**
     * Get Total Active Loan Deduction for an Employee for a specific Month
     */
    public function getMonthlyDeduction($employeeId, $month, $year)
    {
        // Find repayments where scheduled_date falls in this month
        // logic: scheduled_date between start and end of month
        
        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        return LoanRepayment::whereHas('loan', function($q) use ($employeeId) {
                $q->where('employee_id', $employeeId)->active();
            })
            ->pending()
            ->whereBetween('scheduled_date', [$start, $end])
            ->sum('amount');
    }
    
    /**
     * Mark Repayments as Deducted
     */
    public function markDeducted($employeeId, $month, $year, $payrollId)
    {
        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        LoanRepayment::whereHas('loan', function($q) use ($employeeId) {
                $q->where('employee_id', $employeeId)->active();
            })
            ->pending()
            ->whereBetween('scheduled_date', [$start, $end])
            ->update([
                'status' => 'Deducted',
                'payroll_id' => $payrollId
            ]);
            
        // Check if loan is closed
        $this->checkLoanClosure($employeeId);
    }
    
    /**
     * Foreclose Loan: Calculate remaining amount to pay today.
     */
    public function forecloseLoan(Loan $loan, $userId)
    {
        return DB::transaction(function () use ($loan, $userId) {
            // 1. Calculate Outstanding Principal
            // Sum of principal components of PENDING repayments?
            // Or `outstanding_balance` of the last PAID repayment?
            // Better: Sum of 'principal_component' of all PENDING repayments.
            
            $pendingRepayments = $loan->repayments()->pending()->get();
            $outstandingPrincipal = $pendingRepayments->sum('principal_component');
            
            // 2. Add Interest till date (Pro-rata)?
            // Strict banking: Interest for current month is applicable. Future interest is waived.
            // Simplified: Just pay outstanding principal + current month interest.
            // Let's assume user pays outstanding principal.
            
            // 3. Mark all pending as 'Waived' or 'Foreclosed'
            $loan->repayments()->pending()->update(['status' => 'Waived']);
            
            // 4. Create one final 'Foreclosure' repayment entry used for settlement
            // This needs to be paid immediately (Simulated payment).
            // Or matched against a "Settlement" record.
            
            $loan->update([
                'status' => 'Closed', 
                'closed_at' => now(), 
                'closure_type' => 'Foreclosure'
            ]);
            
            return $outstandingPrincipal;
        });
    }

    /**
     * Pause Repayment: Skip a month and extend tenure.
     */
    public function pauseRepayment(Loan $loan, $month, $year, $reason)
    {
        return DB::transaction(function () use ($loan, $month, $year, $reason) {
            // Find repayment for that month
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end = Carbon::createFromDate($year, $month, 1)->endOfMonth();
             
            $repayment = $loan->repayments()->pending()
                ->whereBetween('scheduled_date', [$start, $end])
                ->first();
                
            if (!$repayment) {
                throw new \Exception("No pending repayment found for $month/$year");
            }
            
            // Move this repayment to the END of the schedule (Extend tenure)
            // Find last repayment date
            $lastRepayment = $loan->repayments()->orderBy('scheduled_date', 'desc')->first();
            $newDate = $lastRepayment->scheduled_date->copy()->addMonth();
            
            $repayment->update([
                 'scheduled_date' => $newDate,
                 'remarks' => "Rescheduled from $month/$year: $reason"
            ]);
            
            // Extend loan tenure count?
            $loan->increment('tenure_months');
        });
    }
    
    protected function checkLoanClosure($employeeId)
    {
        $loans = Loan::where('employee_id', $employeeId)->active()->get();
        foreach($loans as $loan) {
            if ($loan->repayments()->pending()->count() === 0) {
                $loan->update(['status' => 'Closed']);
            }
        }
    }
}
