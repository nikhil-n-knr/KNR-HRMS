<?php

namespace App\Services\Compliance;

use App\Models\Payroll;
use App\Models\Employee;

class StatutoryRegisterService
{
    // CSV Header Helper
    private function toCsv($headers, $rows)
    {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        return stream_get_contents($output);
    }

    public function generateForm5(Payroll $payroll)
    {
        // New Joinees in this month
        // Form 5 Requirements: UAN, Name, Father's Name, DOB, Gender, DOJ, prev_service (N)
        $monthStart = $payroll->start_date ?? \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->startOfMonth();
        $monthEnd = $payroll->end_date ?? \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->endOfMonth();

        $employees = Employee::whereBetween('joining_date', [$monthStart, $monthEnd])
            ->with(['personalDetail'])
            ->get();

        $rows = [];
        foreach ($employees as $emp) {
            $rows[] = [
                $emp->uan_number,
                $emp->first_name . ' ' . $emp->last_name,
                $emp->personalDetail?->father_name ?? '',
                $emp->personalDetail?->dob,
                $emp->gender,
                $emp->joining_date,
                '0' // Previous Service
            ];
        }

        return $this->toCsv(
            ['UAN', 'Name of Member', 'Father\'s/Husband\'s Name', 'Date of Birth', 'Gender', 'Date of Joining', 'Previous Service'],
            $rows
        );
    }

    public function generateForm10(Payroll $payroll)
    {
        // Resigned in this month
        $monthStart = $payroll->start_date ?? \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->startOfMonth();
        $monthEnd = $payroll->end_date ?? \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->endOfMonth();

        // Use whereHas on exits to find employees who resigned/left in this period
        $employees = Employee::where('status', 'terminated')
                             ->whereHas('exits', function($q) use ($monthStart, $monthEnd) {
                                 $q->whereBetween('resignation_date', [$monthStart, $monthEnd]);
                             })->with(['currentExit'])->get();
        
        $rows = [];
        foreach ($employees as $emp) {
            $exit = $emp->currentExit;
            $rows[] = [
                $emp->uan_number,
                $emp->first_name . ' ' . $emp->last_name,
                'Cessation', // Reason Code
                $exit?->resignation_date?->format('Y-m-d') // DOE
            ];
        }

        return $this->toCsv(
            ['UAN', 'Name of Member', 'Reason for Leaving', 'Date of Leaving'],
            $rows
        );
    }

    public function generateForm12A(Payroll $payroll)
    {
        // Monthly Abstract
        $items = $payroll->payslips()->with('employee')->get();
        
        $rows = [];
        foreach ($items as $item) {
             // Mock Values for Wages - Logic similar to ECR
             $gross = $item->gross_earnings;
             $basic = $this->getComponentValue($item, 'Basic'); 
             $pf = $item->pf_amount ?? round($basic * 0.12); // Use actual deduction
             
             $rows[] = [
                 $item->employee->uan_number,
                 $item->employee->first_name,
                 $gross,
                 $basic, // EPF Wages
                 $pf, // EE Share
                 round($pf * 0.0833), // Pension (Approx)
                 round($pf - ($pf * 0.0833)) // ER Share
             ];
        }

        return $this->toCsv(
            ['UAN', 'Name', 'Gross Wages', 'EPF Wages', 'EE Share', 'ER Pension', 'ER EPF'],
            $rows
        );
    }

    public function generateESIReturn(Payroll $payroll)
    {
        // ESI Excel Format
        // IP Number, IP Name, No of Days, Total Wages, Employee Contribution, Reason Code (0)
        $items = $payroll->payslips()->with('employee')->get();
        
        // Fetch Limit dynamically
        $rule = \App\Models\ComplianceRule::getRule('ESI');
        $wageLimit = $rule ? ($rule->rules_json['wage_ceiling'] ?? 21000) : 21000;
        
        $rows = [];
        foreach ($items as $item) {
            if (!$item->employee->esi_number) continue; // Skip non-ESI

            $gross = $item->gross_earnings;
            // Note: If they paid ESI, we report it even if gross > limit (e.g. they crossed limit this month but were covered at start of contribution period).
            // Logic: Filter by IF DEDUCTED > 0.
            
            $esiAmount = ($item->deductions_breakdown['ESI'] ?? 0);
            
            if ($esiAmount <= 0 && $gross > $wageLimit) continue; // Skip if exempt and no deduction
            
            $rows[] = [
                $item->employee->esi_number,
                $item->employee->first_name . ' ' . $item->employee->last_name,
                $item->payable_days,
                $gross,
                $esiAmount,
                '0' // Reason Code 0 = Regular
            ];
        }

        return $this->toCsv(
            ['IP Number', 'IP Name', 'No of Days', 'Total Month Wages', 'Employee Contribution', 'Reason Code'],
            $rows
        );
    }

    public function generatePTReport(Payroll $payroll)
    {
        // IP Name, Location (State), PT Amount
        $items = $payroll->payslips()->with(['employee', 'employee.location'])->get();
        
        $rows = [];
        foreach ($items as $item) {
            $ptAmount = ($item->deductions_breakdown['PT'] ?? 0);
            if ($ptAmount <= 0) continue;

            $rows[] = [
                $item->employee->first_name . ' ' . $item->employee->last_name,
                $item->employee->location?->state ?? 'N/A',
                $ptAmount
            ];
        }

        return $this->toCsv(
            ['Employee Name', 'State', 'PT Amount'],
            $rows
        );
    }

    private function getComponentValue($item, $name)
    {
        $earnings = $item->earnings_breakdown;
        return $earnings[$name] ?? 0;
    }
}
