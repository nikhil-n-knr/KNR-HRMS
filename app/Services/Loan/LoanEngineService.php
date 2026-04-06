<?php

namespace App\Services\Loan;

use App\Models\User;
use App\Models\LoanProduct;
use App\Models\LoanInterestRule;
use Carbon\Carbon;

class LoanEngineService
{
    /**
     * Calculate Loan Eligibility based on Product Rules and User's Salary.
     */
    public function calculateEligibility(User $user, LoanProduct $product): array
    {
        // 1. Fetch Employee Salary (CTC)
        // Assuming Employee -> EmployeeSalary (Active)
        $employee = $user->employee;
        if (!$employee) {
            return ['eligible' => false, 'reason' => 'User is not an employee'];
        }

        $salary = $employee->latestSalary; // Corrected relationship
        if (!$salary) {
            return ['eligible' => false, 'reason' => 'No active salary structure found'];
        }

        $ctc = $salary->annual_ctc ?? ($salary->monthly_ctc * 12) ?? 0;
        $monthlyCtc = $ctc / 12;

        // 2. Calculate Cap
        // Multiplier * Monthly CTC. e.g. 6.0 * 50,000 = 300,000
        $maxEligibleAmount = $monthlyCtc * $product->eligibility_multiplier;

        // 3. Absolute Cap from Product
        if ($product->max_amount_limit > 0) {
            $maxEligibleAmount = min($maxEligibleAmount, $product->max_amount_limit);
        }

        // 4. Check Existing Active Loans (Burden)
        // Optional: Reduce eligibility by outstanding balance? 
        // For V1, we just return the theoretical limit.
        
        return [
            'eligible' => true,
            'max_amount' => round($maxEligibleAmount, 2),
            'max_tenure' => $product->max_tenure_months,
            'salary_basis' => $monthlyCtc
        ];
    }

    /**
     * Determine Interest Rate based on Rules.
     */
    public function calculateInterestRate(LoanProduct $product, float $amount, int $tenureMonths): float
    {
        // default to 0 or base rate?
        // Let's find a matching rule
        $rule = $product->interestRules()
            ->where(function($q) use ($amount) {
                $q->where('min_amount', '<=', $amount)
                  ->where(function($SQ) use ($amount) {
                      $SQ->whereNull('max_amount')
                         ->orWhere('max_amount', '>=', $amount);
                  });
            })
            ->where(function($q) use ($tenureMonths) {
                $q->where('min_tenure_months', '<=', $tenureMonths)
                  ->where(function($SQ) use ($tenureMonths) {
                      $SQ->whereNull('max_tenure_months')
                         ->orWhere('max_tenure_months', '>=', $tenureMonths);
                  });
            })
            ->orderBy('min_amount', 'desc') // Most specific (highest threshold) first
            ->orderBy('interest_rate', 'asc') // Then lowest rate if tied
            ->first();

        return $rule ? (float)$rule->interest_rate : (float)$product->interest_rate; // Fallback to product interest_rate if no rule matches
    }

    /**
     * Generate Amortization Schedule.
     */
    public function generateAmortizationSchedule(float $principal, float $annualRate, int $tenureMonths, string $type = 'Flat'): array
    {
        $schedule = [];
        $balance = $principal;
        
        if ($type === 'Flat') {
            // Flat Rate: Interest is calculated on full Principal for entire tenure
            // Total Interest = P * R * (T/12)
            $totalInterest = $principal * ($annualRate / 100) * ($tenureMonths / 12);
            $totalPayable = $principal + $totalInterest;
            $emi = $totalPayable / $tenureMonths;
            
            $monthlyPrincipal = $principal / $tenureMonths;
            $monthlyInterest = $totalInterest / $tenureMonths;

            for ($i = 1; $i <= $tenureMonths; $i++) {
                $balance -= $monthlyPrincipal;
                $schedule[] = [
                    'month' => $i,
                    'emi' => round($emi, 2),
                    'principal_component' => round($monthlyPrincipal, 2),
                    'interest_component' => round($monthlyInterest, 2),
                    'balance' => round(max(0, $balance), 2)
                ];
            }
        } 
        elseif ($type === 'Reducing') {
            // Standard Reducing Balance (PMT formula)
            // r = annual rate / 12 / 100
            $r = ($annualRate / 12) / 100;
            
            if ($r <= 0) {
                // Formatting for 0%
                 $emi = $principal / $tenureMonths;
                 for ($i = 1; $i <= $tenureMonths; $i++) {
                    $balance -= $emi;
                    $schedule[] = [
                        'month' => $i,
                        'emi' => round($emi, 2),
                        'principal_component' => round($emi, 2),
                        'interest_component' => 0,
                        'balance' => round(max(0, $balance), 2)
                    ];
                }
            } else {
                $emi = ($principal * $r * pow(1 + $r, $tenureMonths)) / (pow(1 + $r, $tenureMonths) - 1);

                for ($i = 1; $i <= $tenureMonths; $i++) {
                    $interest = $balance * $r;
                    $principalPart = $emi - $interest;
                    $balance -= $principalPart;
                    
                    $schedule[] = [
                        'month' => $i,
                        'emi' => round($emi, 2),
                        'principal_component' => round($principalPart, 2),
                        'interest_component' => round($interest, 2),
                        'balance' => round(max(0, $balance), 2)
                    ];
                }
            }
        }

        return $schedule;
    }
}
