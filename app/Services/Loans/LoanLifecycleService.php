<?php

namespace App\Services\Loans;

use App\Models\Loan;
use App\Models\Employee;
use Carbon\Carbon;

class LoanLifecycleService
{
    /**
     * Calculate Foreclosure Amount.
     * Logic: Pending Principal + (Optional Interest to date).
     * For Flat Rate: Remaining Principal + Remaining Interest? Or Pro-rata?
     * Simplified: Remaining Installments * EMI (Includes future interest) - Rebate?
     * MVP: Sum of remaining scheduled repayments.
     */
    public function getForeclosureDetails(Loan $loan)
    {
        // Get pending repayments
        $pendingRepayments = $loan->repayments()->where('status', 'Pending')->get();
        $totalPending = $pendingRepayments->sum('amount');
        
        // If we want to offer a rebate on future interest (reducing balance logic), complex.
        // MVP: Total Payoff = Total Pending.
        
        return [
            'amount' => $totalPending,
            'pending_installments' => $pendingRepayments->count(),
            'foreclosure_date' => now()->toDateString()
        ];
    }

    /**
     * Foreclose the loan.
     */
    public function foreclose(Loan $loan, $amount = null)
    {
        $details = $this->getForeclosureDetails($loan);
        $finalAmount = $amount ?? $details['amount'];

        $loan->update([
            'status' => 'Closed',
            'foreclosure_date' => now(),
            'foreclosure_amount' => $finalAmount
        ]);
        
        // Mark all pending repayments as Cancelled or Paid?
        // Maybe "Waived" if rebate? Or just delete?
        // Let's mark as 'Foreclosed'
        $loan->repayments()->where('status', 'Pending')->update(['status' => 'Foreclosed']);
        
        return $loan;
    }

    /**
     * Pause EMI for a given month.
     * Logic: Push all future dates by 1 month.
     */
    public function pauseLoan(Loan $loan, $months = 1, $reason = null)
    {
        // 1. Update Loan status
        $loan->update([
            'is_paused' => true,
            'paused_until' => now()->addMonths($months)
        ]);

        // 2. Shift Pending Repayments
        $repayments = $loan->repayments()->where('status', 'Pending')->orderBy('scheduled_date')->get();
        foreach ($repayments as $repayment) {
            $newDate = Carbon::parse($repayment->scheduled_date)->addMonths($months);
            $repayment->update(['scheduled_date' => $newDate]);
        }
        
        return $loan;
    }

    /**
     * The "Resignation Trap"
     * Convert active loans to Immediate Recovery upon resignation.
     */
    public function handleResignation(Employee $employee)
    {
        $activeLoans = $employee->loans()->active()->get();
        
        foreach ($activeLoans as $loan) {
            // Calculate total pending
            $details = $this->getForeclosureDetails($loan);
            
            // Update Loan
            $loan->update([
                'status' => 'Recovery', // Special status
                'is_resignation_recovery' => true,
                'foreclosure_amount' => $details['amount'],
                'foreclosure_date' => now()
            ]);
            
            // We might add this amount to FnF table here or later.
            // For now, flagging it is enough for the FnF module to pick up.
        }
    }

    /**
     * Recovery Sync: Push EMIs to Payroll.
     * Returns list of deductions processed.
     */
    public function processRecovery($monthStr = null)
    {
        // 1. Get all active loans with pending repayment for this month
        // In a real system, we look at 'scheduled_date' falling in current payroll month.
        // For MVP, we stick to 'Active' loans and find the next pending repayment.
        
        $activeLoans = Loan::active()->where('is_paused', false)->with('employee')->get();
        $processed = [];
        $totalAmt = 0;

        foreach ($activeLoans as $loan) {
             // Find next pending repayment
             $repayment = $loan->repayments()->where('status', 'Pending')->orderBy('scheduled_date')->first();
             
             if ($repayment) {
                 // In a real app, we insert into 'payroll_deductions' table.
                 // For now, we update the repayment status to 'Deducted' assuming Payroll picked it up.
                 // OR we return this data to the Controller to show what WILL be pushed.
                 $repayment->update(['status' => 'Deducted']);
                 $processed[] = [
                     'employee' => $loan->employee->user->name,
                     'amount' => $repayment->amount,
                     'loan_id' => $loan->id
                 ];
                 $totalAmt += $repayment->amount;
             }
        }
        
        return [
            'count' => count($processed),
            'total_amount' => $totalAmt,
            'details' => $processed
        ];
    }
}
