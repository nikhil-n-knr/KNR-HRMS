<?php

namespace App\Services\Loans;

use App\Models\Loan;
use App\Models\Employee;
use App\Models\LoanProduct;

class LoanRiskService
{
    /**
     * Calculate Risk Score and Analysis for a Loan Application.
     * Score: 0 (Safe) to 100 (High Risk).
     * 
     * @param Employee $employee
     * @param float $amount
     * @param int $tenureMonths
     * @return array
     */
    public function analyzeRisk(Employee $employee, float $amount, int $tenureMonths, LoanProduct $product = null)
    {
        $salary = $employee->latestSalary;
        $netSalary = $salary ? ($salary->breakdown['net_pay'] ?? 0) : 0;
        
        // 1. Debt-to-Income Ratio (DTI)
        // Assume EMI = Amount / Tenure (Flat 0% for simple Calc, or use formula if interest)
        // If Product has interest, we should use PMT formula. For now, estimate simple EMI.
        $estimatedEMI = $amount / $tenureMonths;
        
        $existingEMIs = $employee->loans()->active()->sum('monthly_installment');
        $totalObligation = $estimatedEMI + $existingEMIs;
        
        $dtiRatio = $netSalary > 0 ? ($totalObligation / $netSalary) * 100 : 100;
        
        // 2. Risk Score Calculation
        $riskScore = 0;
        $flags = [];

        // Factor A: DTI
        if ($dtiRatio > 50) {
            $riskScore += 40;
            $flags[] = "High Debt-to-Income Ratio ({$dtiRatio}%)";
        } elseif ($dtiRatio > 30) {
            $riskScore += 20;
            $flags[] = "Moderate Debt-to-Income Ratio ({$dtiRatio}%)";
        }

        // Factor B: Tenure Cap
        if ($product && $tenureMonths > $product->max_tenure_months) {
             $riskScore += 50; // Critical
             $flags[] = "Tenure exceeds product limit of {$product->max_tenure_months} months";
        }

        // Factor C: Probation / Wait Period
        // Check joining date
        if ($product && isset($product->policy_settings['wait_period_months'])) {
            $monthsJoined = $employee->joining_date->diffInMonths(now());
            if ($monthsJoined < $product->policy_settings['wait_period_months']) {
                 $riskScore += 100;
                 $flags[] = "Employee in probation (Joined {$monthsJoined} months ago)";
            }
        }
        
        // Factor D: Salary Multiplier Cap
        if ($product && isset($product->policy_settings['salary_multiplier'])) {
             $maxAmount = $netSalary * $product->policy_settings['salary_multiplier'];
             if ($amount > $maxAmount) {
                  $riskScore += 30;
                  $flags[] = "Amount exceeds {$product->policy_settings['salary_multiplier']}x Salary Limit";
             }
        }
        
        // Normalize Score
        $riskScore = min($riskScore, 100);

        return [
            'score' => $riskScore,
            'dti_ratio' => round($dtiRatio, 2),
            'estimated_emi' => round($estimatedEMI, 2),
            'net_salary' => $netSalary,
            'flags' => $flags,
            'recommendation' => $riskScore > 50 ? 'Reject or Lower Amount' : 'Safe to Approve'
        ];
    }
}
