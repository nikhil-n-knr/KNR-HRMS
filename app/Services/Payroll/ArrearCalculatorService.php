<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\Payslip;
use App\Models\Payroll;
use App\Models\EmployeeSalary;
use Carbon\Carbon;

class ArrearCalculatorService
{
    /**
     * Calculate arrears for an employee based on effective date of current salary.
     * 
     * @param int $employeeId
     * @param int $currentMonth
     * @param int $currentYear
     * @return array ['amount' => float, 'breakdown' => array]
     */
    public function calculateArrears(int $employeeId, int $currentMonth, int $currentYear): array
    {
        $employeeSalary = EmployeeSalary::where('employee_id', $employeeId)->first();
        if (!$employeeSalary || !$employeeSalary->effective_date) {
            return ['amount' => 0, 'breakdown' => []];
        }

        $effectiveDate = Carbon::parse($employeeSalary->effective_date);
        
        // If effective date is in the future or this month, no arrears (this month is processed normally)
        $currentPeriodStart = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfMonth();
        if ($effectiveDate->greaterThanOrEqualTo($currentPeriodStart)) {
            return ['amount' => 0, 'breakdown' => []];
        }

        // Identify months to check for arrears
        // From Effective Date -> Until Month Before Current
        $checkDate = $effectiveDate->copy()->startOfMonth();
        $totalArrears = 0;
        $breakdown = [];

        while ($checkDate->lt($currentPeriodStart)) {
            $month = $checkDate->month;
            $year = $checkDate->year;

            // Check if Payroll was already run for this period
            $payslip = Payslip::where('employee_id', $employeeId)
                ->whereHas('payroll', function($q) use ($month, $year) {
                    $q->where('month', $month)->where('year', $year)->where('status', 'Paid');
                })
                ->first();

            // Only calculate if a payslip exists (meaning we underpaid them)
            // If they weren't paid at all, that's not arrears, that's just unpaid salary (handled separately or manual)
            if ($payslip) {
                // 1. What was paid?
                $paidBasic = $payslip->basic_salary;
                // We focus on Basic for now as it drives most components. 
                // Getting full breakdown diff is complex, let's stick to Gross diff or Basic diff.
                // Better approach: Calculate "New Gross" for that month.
                
                // 2. What should have been paid? 
                // (Assuming current structure was effective then)
                // Note: accurate calculation requires knowing if *other* factors changed. 
                // We assume the ONLY change is the structure adjustment.
                
                // Re-calculate projected earnings based on current master
                $projectedComponents = $this->calculateProjectedEarnings($employeeSalary);
                $shouldBeGross = $projectedComponents['gross'];
                
                // 3. Difference
                $paidGross = $payslip->gross_earnings; 
                
                // Only if New > Old
                if ($shouldBeGross > $paidGross) {
                    $diff = $shouldBeGross - $paidGross;
                    
                    // Add to total
                    $totalArrears += $diff;
                    $breakdown[] = [
                        'month' => $checkDate->format('F Y'),
                        'paid' => $paidGross,
                        'should_be' => $shouldBeGross,
                        'diff' => $diff
                    ];
                }
            }

            $checkDate->addMonth();
        }

        return [
            'amount' => $totalArrears,
            'breakdown' => $breakdown
        ];
    }

    private function calculateProjectedEarnings($salaryRecord)
    {
        // 1. Try to use the breakdown array if available
        if (!empty($salaryRecord->breakdown) && is_array($salaryRecord->breakdown)) {
            // Sum all components to get Gross
            $gross = array_sum($salaryRecord->breakdown);
            return ['gross' => $gross];
        }

        // 2. Fallback: Use stored Annual CTC / 12
        if ($salaryRecord->annual_ctc) {
             return ['gross' => $salaryRecord->annual_ctc / 12];
        }

        return ['gross' => 0];
    }
}
