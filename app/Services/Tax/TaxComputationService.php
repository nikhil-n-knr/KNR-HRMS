<?php

namespace App\Services\Tax;

use App\Models\Employee;
use App\Models\Payslip;
use App\Models\TaxDeclaration;
use App\Services\Infrastructure\LoggerService;
use Carbon\Carbon;

class TaxComputationService
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Aggregate yearly taxable income and exemptions for an employee.
     */
    public function aggregateYearlyData(Employee $employee, int $financialYear): array
    {
        $startMonth = 4; // April
        $endMonth = 3;   // March next year
        
        $startDate = Carbon::create($financialYear, $startMonth, 1)->startOfMonth();
        $endDate = Carbon::create($financialYear + 1, $endMonth, 1)->endOfMonth();

        // 1. Fetch all Payslips for the period
        $payslips = Payslip::where('employee_id', $employee->id)
            ->whereHas('payroll', function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get();

        $grossSalary = $payslips->sum('gross_pay');
        $totalPf = 0;
        $totalPt = 0;
        $totalTds = $payslips->sum('tds') ?? 0;

        foreach ($payslips as $ps) {
            $breakdown = $ps->salary_breakdown['breakup'] ?? [];
            foreach ($breakdown as $name => $val) {
                if (stripos($name, 'Provident Fund') !== false) $totalPf += $val['amount'];
                if (stripos($name, 'Professional Tax') !== false) $totalPt += $val['amount'];
            }
        }

        // 2. Fetch Verified Tax Declarations
        $declarations = TaxDeclaration::where('employee_id', $employee->id)
            ->where('status', 'Verified')
            ->with('section')
            ->get();

        $sec80C = $declarations->filter(fn($d) => $d->section->section_code === '80C')->sum('verified_amount');
        $sec80D = $declarations->filter(fn($d) => $d->section->section_code === '80D')->sum('verified_amount');
        
        // Include EPF (Self) in 80C
        $total80C = min(150000, $sec80C + $totalPf);

        // 3. HRA Exemption Calculation
        $annualBasic = $payslips->sum('basic_salary');
        $annualHRA = 0;
        foreach ($payslips as $ps) {
            $earnings = $ps->earnings_breakdown ?? [];
            foreach ($earnings as $key => $amount) {
                // Check common HRA keys
                if (preg_match('/hra|house rent/i', $key)) {
                    $annualHRA += $amount;
                    break;
                }
            }
        }

        $hraExemption = $this->calculateHRAExemption($employee, $annualBasic, $annualHRA, $financialYear);

        $taxableIncome = $grossSalary - 50000 - $totalPt - $hraExemption - $total80C - $sec80D;

        $this->logger->logInfo("Yearly Tax Aggregation Detailed", [
            'employee_id' => $employee->id,
            'fy' => $financialYear,
            'gross' => $grossSalary,
            'basic' => $annualBasic,
            'hra_received' => $annualHRA,
            'hra_exemption' => $hraExemption,
            'taxable' => $taxableIncome
        ]);

        return [
            'fy' => $financialYear,
            'gross_salary' => $grossSalary,
            'standard_deduction' => 50000,
            'professional_tax' => $totalPt,
            'hra_exemption' => $hraExemption,
            'section_80c' => $total80C,
            'section_80d' => $sec80D,
            'taxable_income' => max(0, $taxableIncome),
            'tds_paid' => $totalTds
        ];
    }

    /**
     * Calculate HRA Exemption based on Income Tax Rules
     */
    private function calculateHRAExemption(Employee $employee, float $annualBasic, float $annualHRA, int $financialYear): float
    {
        if ($annualHRA <= 0 || $annualBasic <= 0) return 0;

        // Fetch Verified Declaration
        $declaration = \App\Models\EmployeeHraDeclaration::where('employee_id', $employee->id)
            ->where('fiscal_year', "$financialYear-" . ($financialYear + 1))
            ->where('status', 'Verified')
            ->first();

        if (!$declaration) return 0;

        $rentPaid = $declaration->rent_monthly * 12; // annualized
        $isMetro = $declaration->is_metro_city;

        // Rule: Min of
        // 1. Actual HRA Received
        // 2. Rent Paid - 10% of Basic
        // 3. 50% (Metro) or 40% (Non-Metro) of Basic

        $limit1 = $annualHRA;
        $limit2 = max(0, $rentPaid - (0.10 * $annualBasic));
        $limit3 = ($isMetro ? 0.50 : 0.40) * $annualBasic;

        return min($limit1, $limit2, $limit3);
    }
}
