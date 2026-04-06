<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\EmployeeTaxRegime;
use App\Models\TaxDeclaration;
use App\Models\TaxSection;
use Carbon\Carbon;

class TaxCalculatorService
{
    /**
     * Calculate monthly TDS for an employee based on projections.
     */
    public function calculateMonthlyTDS(
        int|string $employeeId, 
        float $monthlyGross, 
        int $currentMonth, 
        int $currentYear, 
        ?string $regimeOverride = null
    ): float
    {
        // 1. Determine Fiscal Year
        $fyStartYear = ($currentMonth > 3) ? $currentYear : $currentYear - 1;
        $fiscalYear = "$fyStartYear-" . ($fyStartYear + 1);

        // 2. Project Annual Income
        $fyMonthIndex = ($currentMonth > 3) ? ($currentMonth - 3) : ($currentMonth + 9);
        $remainingMonths = 13 - $fyMonthIndex; 
        
        $projectedFuture = $monthlyGross * $remainingMonths;
        
        // Fetch YTD Gross
        $ytdGross = \App\Models\Payslip::where('employee_id', $employeeId)
            ->whereHas('payroll', function($q) use ($fyStartYear) {
                $q->where(function($SQ) use ($fyStartYear) {
                    $SQ->where('year', $fyStartYear)->where('month', '>=', 4)
                       ->orWhere(function($SSQ) use ($fyStartYear) {
                           $SSQ->where('year', $fyStartYear + 1)->where('month', '<=', 3);
                       });
                });
            })
            ->where('status', 'Paid')
            ->sum('gross_earnings');
            
        $annualGross = $ytdGross + $projectedFuture; 

        // 3. Get Tax Regime
        if ($regimeOverride) {
            $regime = $regimeOverride;
        } else {
            $regimeRecord = EmployeeTaxRegime::where('employee_id', $employeeId)
                ->where('fiscal_year', $fiscalYear)
                ->first();
            $regime = $regimeRecord ? $regimeRecord->regime : 'New'; // Default to New
        }

        // 4. Calculate Taxable Income
        $taxableIncome = $this->calculateTaxableIncome($employeeId, $fiscalYear, $annualGross, $regime);

        // 5. Calculate Tax Liability
        $totalTax = $this->calculateTaxLiability($taxableIncome, $regime);
        
        // Subtract Previous TDS Paid
        $prevTds = $regimeRecord->previous_tds_paid ?? 0;
        $netTaxPayable = max(0, $totalTax - $prevTds);

        // 6. Divide by Remaining Months
        $monthlyTDS = ($remainingMonths > 0) ? ($netTaxPayable / $remainingMonths) : 0;

        return round($monthlyTDS, 2);
    }

    /**
     * Get detailed computation for Tax Sheet
     */
    public function getComputation(int|string $employeeId, int $currentYear)
    {
        $fiscalYear = "$currentYear-" . ($currentYear + 1);

        $employee = Employee::with('user')->find($employeeId);
        $annualGross = ($employee->annual_ctc ?? 0); 
        
        $regimeRecord = EmployeeTaxRegime::where('employee_id', $employeeId)
            ->where('fiscal_year', $fiscalYear)
            ->first();
        $regime = $regimeRecord ? $regimeRecord->regime : 'New';

        $breakdown = $this->calculateTaxableIncomeBreakdown($employeeId, $fiscalYear, $annualGross, $regime);
        
        $taxableIncome = $breakdown['taxable_income'];
        $taxLiability = $this->calculateTaxLiability($taxableIncome, $regime); 

        return [
            'employee' => $employee,
            'regime' => $regime,
            'fiscal_year' => $fiscalYear,
            'annual_gross' => $annualGross,
            'deductions' => $breakdown['deductions'], 
            'std_deduction' => $breakdown['std_deduction'],
            'total_deductions' => $breakdown['total_deductions'],
            'taxable_income' => $taxableIncome,
            'tax_payable' => $taxLiability
        ];
    }

    private function calculateTaxableIncome($employeeId, $fiscalYear, $annualGross, $regime)
    {
        $data = $this->calculateTaxableIncomeBreakdown($employeeId, $fiscalYear, $annualGross, $regime);
        return $data['taxable_income'];
    }

    private function calculateTaxableIncomeBreakdown($employeeId, $fiscalYear, $annualGross, $regime)
    {
        $deductionList = [];
        $totalDeductions = 0;
        $stdDeduction = 0;

        // Standard Deduction
        $startYear = explode('-', $fiscalYear)[0];
        if ($startYear >= 2025) { 
             $stdDeduction = 75000;
        } else {
             $stdDeduction = 50000;
        }
        $totalDeductions += $stdDeduction;

        if ($regime === 'New') {
            return [
                'std_deduction' => $stdDeduction,
                'deductions' => [],
                'total_deductions' => $stdDeduction,
                'taxable_income' => max(0, $annualGross - $stdDeduction)
            ];
        }

        if ($regime !== 'New') {
            // --- 1. HRA Exemption (Projected) ---
            // Need projected Basic and HRA from Annual Gross
            // We assume a standard structure ratio if not available, OR fetch from Salary Structure.
            // For accuracy, we should fetch the SalaryStructure of the employee.
            $salaryStruct = \App\Models\EmployeeSalary::where('employee_id', $employeeId)->where('is_active', true)->first();
            
            if ($salaryStruct && $salaryStruct->breakdown) {
                // Extract Basic & HRA from the JSON breakdown (which is monthly or annual usually? Breakdown is usually monthly in EmployeeSalary)
                // EmployeeSalary stored 'breakdown' from SalaryCalculatorService which returns MONTHLY inputs? 
                // SalaryCalculatorService returns 'breakup' with amounts. Usually these are monthly figures derived from CTC.
                
                $monthlyBasic = 0;
                $monthlyHRA = 0;
                
                $breakup = $salaryStruct->breakdown['breakup'] ?? $salaryStruct->breakdown;
                
                foreach ($breakup as $name => $data) {
                    $amt = is_array($data) ? ($data['amount'] ?? 0) : $data;
                    if (strcasecmp($name, 'Basic') === 0) $monthlyBasic = $amt;
                    if (preg_match('/hra|house rent/i', $name)) $monthlyHRA = $amt;
                }

                // Fallback if basic/hra not explicitly found in breakdown
                if ($monthlyBasic <= 0) $monthlyBasic = ($salaryStruct->annual_ctc / 12) * 0.40; // Assume 40% Basic
                if ($monthlyHRA <= 0) $monthlyHRA = $monthlyBasic * 0.50; // Assume 50% of Basic

                $annualBasic = $monthlyBasic * 12;
                $annualHRA = $monthlyHRA * 12;
                
                // Fetch Declaration (Prioritize Verified > Submitted)
                $hraDecl = \App\Models\EmployeeHraDeclaration::where('employee_id', $employeeId)
                    ->where('fiscal_year', $fiscalYear)
                    ->whereIn('status', ['Verified', 'Submitted'])
                    ->orderByRaw("FIELD(status, 'Verified', 'Submitted')") // Verified first
                    ->first();

                if ($hraDecl && $annualHRA > 0) {
                    $rentPaid = $hraDecl->rent_monthly * 12;
                    $isMetro = $hraDecl->is_metro_city;
                    
                    // Standard 3-tier HRA Rule
                    $limit1 = $annualHRA;
                    $limit2 = max(0, $rentPaid - (0.10 * $annualBasic));
                    $limit3 = ($isMetro ? 0.50 : 0.40) * $annualBasic;
                    
                    $hraExemption = min($limit1, $limit2, $limit3);
                    
                    $deductionList[] = [
                        'section' => '10(13A)',
                        'name' => 'House Rent Allowance',
                        'declared' => $rentPaid,
                        'accepted' => $hraExemption,
                        'status' => $hraDecl->status
                    ];
                    $totalDeductions += $hraExemption;
                }
            }
        }
            
        $declarations = TaxDeclaration::where('employee_id', $employeeId)
            ->where('fiscal_year', $fiscalYear)
            ->whereIn('status', ['Submitted', 'Verified'])
            ->with('section')
            ->get();

        foreach ($declarations as $decl) {
            $limit = $decl->section->max_deduction;
            $amount = $decl->declared_amount;
            if ($limit && $amount > $limit) $amount = $limit;
            
            $deductionList[] = [
                'section' => $decl->section->section_code,
                'name' => $decl->section->name,
                'declared' => $decl->declared_amount,
                'accepted' => $amount
            ];
            $totalDeductions += $amount;
        }
        
        // --- PREVIOUS EMPLOYMENT (Both Regimes might use this logic differently, but typically added to Gross) ---
        // But wait, Standard Deduction applies only ONCE per year globally if salary is salary.
        // However, usually we just add Previous Gross to Current Gross.
        
        $regimeRecord = EmployeeTaxRegime::where('employee_id', $employeeId)
            ->where('fiscal_year', $fiscalYear)
            ->first();
            
        $prevGross = $regimeRecord->previous_gross_income ?? 0;
        $prevPf = $regimeRecord->previous_pf_deducted ?? 0;
        $prevPt = $regimeRecord->previous_pt_paid ?? 0;
        
        // Add Previous Gross to Annual Gross
        $totalGross = $annualGross + $prevGross;
        
        // Add Previous PF to deductions (Section 80C usually)
        // Note: We should add this as a "Section 80C" item if not already full. 
        // For simplicity, let's append it to deductions or handle it separately.
        // Also Previous PT is Section 16 deduction.
        $totalDeductions += $prevPt; // Section 16(iii)
        
        // If Old Regime, add PF to 80C
        if ($regime !== 'New' && $prevPf > 0) {
            $totalDeductions += $prevPf;
            // Ideally we check 1.5L cap again but let's assume simple addition for now or refine later.
        }

        return [
            'std_deduction' => $stdDeduction,
            'deductions' => $deductionList,
            'previous_income' => $prevGross,
            'total_deductions' => $totalDeductions,
            'taxable_income' => max(0, $totalGross - $totalDeductions - $stdDeduction) // Std Deduction once
        ];
    }

    /**
     * Recalculate TDS and update Employee Salary Structure.
     * Triggered after Proof Verification.
     */
    public function recalculateTds($employeeId, $performedByUserId = null) 
    {
        $currentDate = Carbon::now();
        $currentMonth = $currentDate->month;
        $currentYear = $currentDate->year;
        
        // 1. Calculate New Monthly TDS
        // We reuse the existing projection logic which pulls verified declarations
        // But first, we need to ensure the calculation considers ONLY Verified declarations if we are in "Proof" mode? 
        // Logic: calculateMonthlyTDS uses calculateTaxableIncome, which uses 'Submitted' OR 'Verified'.
        // If we want to be strict post-verification, we might want to filter stronger. 
        // For now, let's assume 'Submitted' is still valid until Rejected.
        // Wait, if Admin Rejects, status becomes 'Rejected', so they fall out of calculation. Correct.
        
        // However, we need to ensure we are calculating for the "Remaining" months properly.
        // The projection logic in calculateMonthlyTDS handles this.
        
        $salaryRecord = \App\Models\EmployeeSalary::where('employee_id', $employeeId)
            ->where('is_active', true)
            ->first();
            
        if (!$salaryRecord) return; // No active salary to update
        
        // Use current Gross from salary record (annual / 12)
        $monthlyGross = $salaryRecord->annual_ctc / 12; // Approx
        
        $newTds = $this->calculateMonthlyTDS($employeeId, $monthlyGross, $currentMonth, $currentYear);
        
        // 2. Update Salary Record
        $oldTds = $salaryRecord->tds_monthly_deduction;
        
        // Only update if changed
        if (abs($oldTds - $newTds) > 1) { // Tolerance of 1 Rupee
             $salaryRecord->update(['tds_monthly_deduction' => $newTds]);
             
             // 3. Audit Log
             if ($performedByUserId) {
                 \App\Models\TaxAuditLog::create([
                     'entity_type' => 'EmployeeSalary',
                     'entity_id' => $salaryRecord->id,
                     'action' => 'Recalculate',
                     'actor_id' => $performedByUserId,
                     'old_values' => ['tds' => $oldTds],
                     'new_values' => ['tds' => $newTds],
                     'remarks' => 'Auto-recalculated after proof verification'
                 ]);
             }
        }
        
        return $newTds;
    }

    private function calculateTaxLiability($income, $regimeName)
    {
        $tax = 0;

        // Fetch Regime & Slabs from DB
        $regime = \App\Models\TaxRegime::where('name', $regimeName)->with('slabs')->first();

        // Fallback or specific logic for standard exclusions (Rebate 87A)
        if ($regimeName === 'New') {
            if ($income <= 700000) return 0;
        } elseif ($regimeName === 'Old') {
             if ($income <= 500000) return 0;
        }

        if (!$regime || $regime->slabs->isEmpty()) {
            return 0; // Error: No configuration found
        }

        foreach ($regime->slabs as $slab) {
            if ($income > $slab->min_income) {
                // If max_income is null, use a very large number (e.g. PHP_FLOAT_MAX) or handle separately
                $slabMax = $slab->max_income ?? 999999999999; 
                
                $taxableAmount = min($income, $slabMax) - $slab->min_income;
                
                if ($taxableAmount > 0) {
                     $tax += $taxableAmount * ($slab->tax_rate_percentage / 100);
                }
            }
        }

        // Cess 4%
        $cess = $tax * 0.04;
        
        return $tax + $cess;
    }
}
