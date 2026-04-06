<?php

namespace App\Services\Tax;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\EmployeeTaxDeclaration;
use App\Models\EmployeeHraDeclaration;
use Carbon\Carbon;
use PDF; // Barryvdh DomPDF

class Form16GeneratorService
{
    public function generatePartB(Employee $employee, int $fyEndYear)
    {
        $start = Carbon::create($fyEndYear - 1, 4, 1); // April 2025
        $end = Carbon::create($fyEndYear, 3, 31);      // March 2026

        // 1. Fetch Payrolls
        $payrolls = Payroll::whereHas('items', function($q) use ($employee) {
                $q->where('employee_id', $employee->id);
            })
            ->whereBetween('payment_date', [$start, $end]) // Or processed_date
            ->with(['items' => function($q) use ($employee) {
                $q->where('employee_id', $employee->id);
            }])
            ->get();

        // 2. Aggregate Salary
        $grossSalary = 0;
        $allowances = 0; // Exempt allowances logic can be complex, simplifying
        $tdsDeducted = 0;

        foreach ($payrolls as $p) {
            foreach ($p->items as $item) {
                $grossSalary += $item->gross_earnings;
                $tdsDeducted += ($item->deductions['TDS'] ?? 0);
            }
        }

        // 3. Fetch Exemptions (HRA)
        $hra = EmployeeHraDeclaration::where('employee_id', $employee->id)
            ->where('financial_year', "$fyEndYear") // Assuming format "2026" or "2025-2026"
            ->first();
        $hraExemption = $hra ? ($hra->verified_amount ?? 0) : 0;

        // 4. Standard Deduction (Fixed)
        $standardDeduction = 50000;
        // Check Regime? If New Regime, Std Ded allowed from FY 23-24
        // Assuming we check regime.
        
        // 5. Chapter VI-A Deductions
        $declarations = EmployeeTaxDeclaration::where('employee_id', $employee->id)
            ->where('financial_year', "$fyEndYear")
            ->where('status', 'verified')
            ->get();
        
        $chapter6A = $declarations->sum('verified_amount');

        // 6. Taxable Income
        $netSalary = $grossSalary - $hraExemption - $standardDeduction; // Simplified
        $taxableIncome = max(0, $netSalary - $chapter6A);

        return [
            'employee' => $employee,
            'fy' => ($fyEndYear - 1) . '-' . $fyEndYear,
            'gross_salary' => $grossSalary,
            'hra_exemption' => $hraExemption,
            'standard_deduction' => $standardDeduction,
            'chapter_6a' => $chapter6A, // Can break this down by section if needed
            'taxable_income' => $taxableIncome,
            'tax_payable' => 0, // Need calc logic or fetch from TaxCalculator
            'tax_paid' => $tdsDeducted,
            'generated_date' => now()->format('d-M-Y')
        ];
    }
}
