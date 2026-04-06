<?php

namespace App\Services\Payroll;

use App\Models\EmployeeSalary;
use App\Models\SalaryComponent;
use App\Models\SalaryStructure;
use App\Models\Employee;
use Carbon\Carbon;

class SalaryService
{
    /**
     * Assign salary structure to an employee (Create/Update Salary Card).
     */
    public function assignSalary(Employee $employee, int $structureId, float $annualCtc, string $effectiveDate, string $remarks = null): EmployeeSalary
    {
        // 1. Deactivate previous active salary
        EmployeeSalary::where('employee_id', $employee->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // 2. Compute Breakdown based on Structure Rules
        $breakdown = $this->calculateBreakdown($annualCtc, $structureId);

        // 3. Create new Record
        return EmployeeSalary::create([
            'employee_id' => $employee->id,
            'salary_structure_id' => $structureId,
            'annual_ctc' => $annualCtc,
            'effective_date' => $effectiveDate,
            'breakdown' => $breakdown,
            'is_active' => true,
            'remarks' => $remarks
        ]);
    }

    /**
     * Calculate component-wise breakdown from CTC.
     */
    public function calculateBreakdown(float $ctc, int $structureId): array
    {
        $structure = SalaryStructure::with('components')->find($structureId);
        if (!$structure) {
            throw new \Exception("Salary Structure not found.");
        }

        $monthlyCtc = $ctc / 12;
        $breakdown = [];
        $totalEarnings = 0;
        $totalDeductions = 0;

        foreach ($structure->components as $component) {
            $amount = 0;

            if ($component->calculation_type === 'fixed') {
                $amount = $component->value;
            } elseif ($component->calculation_type === 'percentage') {
                // Percentage of CTC (Annual or Monthly logic matters here. Usually defined on Annual or Monthly Basis)
                // Let's assume the percentage is applied to the Monthly CTC for simplicity unless specified.
                // Or verify if 'value' is percentage logic (e.g. 40 = 40%).
                
                // For now, let's treat formula/percentage simply:
                // If it's percentage, it's % of Basic usually, but here likely % of CTC.
                $amount = ($monthlyCtc * $component->value) / 100;
            }
            
            // Basic Handling: 
            // Often "Basic" is 40-50% of CTC.
            // "HRA" is 40% of Basic.
            // This simple sequential logic might struggle with dependencies (HRA depends on Basic).
            // Advanced system needs an evaluated formula engine.
            // For MVP: We will assume the structure defines percentages of TOTAL CTC.
            
            $breakdown[$component->name] = round($amount, 2);

            if ($component->type === 'earning') {
                $totalEarnings += $amount;
            } else {
                $totalDeductions += $amount;
            }
        }

        // Special handling if "Special Allowance" is a balancer component?
        // Skipped for now.

        return [
            'monthly_ctc' => round($monthlyCtc, 2),
            'components' => $breakdown,
            'gross_earnings' => round($totalEarnings, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_pay' => round($totalEarnings - $totalDeductions, 2)
        ];
    }

    /**
     * Get Current Active Salary for Employee
     */
    public function getCurrentSalary(Employee $employee): ?EmployeeSalary
    {
        return EmployeeSalary::where('employee_id', $employee->id)
            ->where('is_active', true)
            ->latest('effective_date')
            ->first();
    }
}
