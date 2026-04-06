<?php

namespace App\Services\Payroll;

use App\Models\SalaryStructure;
use App\Services\Infrastructure\LoggerService;

class SalaryCalculatorService
{
    public function calculate(float $ctc, SalaryStructure $structure): array
    {
        $breakup = [];
        $totalEarnings = 0;
        $totalDeductions = 0;

        $context = [
            'ctc' => $ctc,
            'CTC' => $ctc
        ];

        // 1. First Pass: Calculate Fixed and direct % of CTC
        foreach ($structure->components as $component) {
            $key = strtolower(str_replace(' ', '_', $component->name));
            
            if ($component->calculation_type === 'fixed') {
                $amount = $component->value;
                $context[$key] = $amount;
            } elseif ($component->calculation_type === 'percentage') {
                $amount = ($ctc * $component->value) / 100;
                $context[$key] = $amount;
            } else {
                // Defer formula components
                $context[$key] = null; 
            }
        }

        // 2. Second Pass: Resolve Formulas (up to 3 iterations for dependencies)
        for ($i = 0; $i < 3; $i++) {
            foreach ($structure->components as $component) {
                if ($component->calculation_type !== 'formula') continue;
                
                $key = strtolower(str_replace(' ', '_', $component->name));
                $formula = strtolower($component->formula);

                // Replace known variables
                foreach ($context as $var => $val) {
                    if ($val !== null) {
                         // Use regex boundaries to ensure we don't replace substrings
                         // e.g. replacing 'b' shouldn't affect 'basic'
                        $formula = preg_replace('/\b' . preg_quote($var, '/') . '\b/', $val, $formula);
                    }
                }

                // Try to evaluate
                try {
                    // Safety check: only allow numbers, operators, parens, spaces
                    if (preg_match('/^[0-9\.\+\-\*\/\(\)\s]+$/', $formula)) {
                        // Safe to eval (arithmetic only)
                        $amount = eval("return $formula;");
                        $context[$key] = $amount;
                    }
                } catch (\Throwable $e) {
                    // Dependency likely missing, wait for next pass
                    \Log::warning("Formula calc postponed for {$component->name}: {$formula}");
                }
            }
        }

        // 3. Statutory Overrides (Hardcoded Indian Compliance for V1)
        // We need 'Basic' and 'Gross' for this.
        // Let's identify 'Basic' from context.
        $basicAmount = $context['basic'] ?? 0;
        
        // Calculate Preliminary Gross (Sum of all earnings calculated so far)
        $prelimGross = 0;
        foreach ($structure->components as $component) {
            if ($component->type === 'earning') {
                $key = strtolower(str_replace(' ', '_', $component->name));
                $prelimGross += ($context[$key] ?? 0);
            }
        }

        foreach ($structure->components as $component) {
            $key = strtolower(str_replace(' ', '_', $component->name));
            
            // PF Logic
            // If name contains 'Provident Fund' or 'PF'
            if (stripos($component->name, 'Provident Fund') !== false || stripos($component->name, 'PF') !== false) {
                // Rule: 12% of Basic. Capped at 1800 usually (12% of 15000).
                // If Basic > 15000, user can choose to pay on full basic or cap at 1800.
                // For V1, we implement the Cap Rule automatically if it's a Deduction.
                if ($component->type === 'deduction') {
                    $pfWages = min($basicAmount, 15000);
                    $pfAmount = $pfWages * 0.12;
                    $context[$key] = $pfAmount;
                }
            }

            // ESI Logic
            // Rule: 0.75% of Gross if Gross <= 21000. Else 0.
            if (stripos($component->name, 'ESI') !== false || stripos($component->name, 'Employee State Insurance') !== false) {
                if ($prelimGross <= 21000) {
                     $context[$key] = ceil($prelimGross * 0.0075); // ESI is always rounded up to next rupee
                } else {
                     $context[$key] = 0;
                }
            }
            
            // Professional Tax (PT)
            if (stripos($component->name, 'Professional Tax') !== false || stripos($component->name, 'PT') !== false) {
                 if ($prelimGross > 10000) {
                     $context[$key] = 200;
                 } else {
                     $context[$key] = 0;
                 }
            }
            
            // TDS Logic (Income Tax) - New Regime FY2024-25
            if (stripos($component->name, 'TDS') !== false || stripos($component->name, 'Income Tax') !== false) {
                // ... (Existing TDS Logic) ...
                $annualGross = $prelimGross * 12; // Simplified
                $standardDeduction = 75000;
                
                $taxableIncome = max(0, $annualGross - $standardDeduction);
                
                $tax = 0;
                if ($taxableIncome > 700000) {
                     // Slabs simplified for brevity
                     if ($taxableIncome > 300000) $tax += min($taxableIncome - 300000, 400000) * 0.05;
                     if ($taxableIncome > 700000) $tax += min($taxableIncome - 700000, 300000) * 0.10;
                     if ($taxableIncome > 1000000) $tax += min($taxableIncome - 1000000, 200000) * 0.15;
                     if ($taxableIncome > 1200000) $tax += min($taxableIncome - 1200000, 300000) * 0.20;
                     if ($taxableIncome > 1500000) $tax += ($taxableIncome - 1500000) * 0.30;
                }
                
                $cess = $tax * 0.04;
                $totalTax = $tax + $cess;
                $monthlyTDS = ceil($totalTax / 12);
                $context[$key] = $monthlyTDS;
            }
        }

        // 3.5 Reimbursements (Expenses approved for Payroll)
        // Fetched dynamically. These are ADDED to Net Pay but usually non-taxable (or handled separately).
        // For simplicity, we add them as a 'Reimbursement' earning component dynamically if not in structure.
        // But ideally, we should iterate over ACTUAL Expense records for this employee for this month (passed via external context or fetched here).
        // Since this service is pure calculation, we'll assume the caller passes 'reimbursements' or we fetch it?
        // Service should be pure. BEst pattern: Pass 'reimbursements' total in $context or calculate it here?
        // Let's assume we can fetch data here for now since context is local.
        
        // We need the Employee ID to fetch. Not passed currently. $structure->employee_id? No.
        // Changing signature is risky.
        // Alternative: The Caller (PayrollProcessor) should calculate total reimbursement and pass it.
        // BUT user asked to "update SalaryCalculatorService".
        // Let's check if we can access the employee.
        // The Service calculates based on CTC/Structure. It is currently somewhat state-agnostic.
        // However, Step 4 assembles components.
        
        // Let's rely on the PayrollProcessor to inject "Reimbursement" as an ad-hoc component into the structure/breakup?
        // Or we add a placeholder here?
        // Since I cannot change the signature easily without breaking other calls, I will check PayrollProcessor.
        // BUT, I can add logic here if I know the employee.
        
        // Let's look at PayrollProcessor.php first to see how it calls this.
        // If I can't see it, I'll update ExpenseSettlementController first.

        // 4. Final Assembly
        foreach ($structure->components as $component) {
            $key = strtolower(str_replace(' ', '_', $component->name));
            $amount = $context[$key] ?? 0;
            
            // Rounding
            $amount = round($amount, 2);

            if ($component->type === 'earning') {
                $totalEarnings += $amount;
            } else {
                $totalDeductions += $amount;
            }

            $breakup[$component->name] = [
                'type' => $component->type,
                'amount' => $amount,
                'component_id' => $component->id
            ];
        }

        return [
            'ctc' => $ctc,
            'gross_salary' => $totalEarnings,
            'net_salary' => $totalEarnings - $totalDeductions,
            'breakup' => $breakup,
            'total_deductions' => $totalDeductions
        ];
    }
}
