<?php

namespace App\Services\Payroll;

use App\Models\Payroll;
use App\Models\EmployeeSalary;
use Illuminate\Support\Str;

class ComplianceService
{
    /**
     * Generate PF ECR Text File Content (v2 format roughly)
     * Format: UAN#MEMBER_NAME#GROSS#EPF_WAGES#EPS_WAGES#EDLI_WAGES#EPF_CONTRI#EPS_CONTRI#EPF_EPS_DIFF#NCP_DAYS#REFUND
     */
    public function generatePfEcr(Payroll $payroll)
    {
        $content = "";
        
        // Eager load necessary relations
        $payslips = $payroll->payslips()->with(['employee.user', 'employee.personalDetail'])->get();

        foreach ($payslips as $slip) {
            $pfComponent = $slip->earnings_breakdown['Basic'] ?? 0; // Assuming Basic implies PF Wages basics
            $pfDeduction = $slip->deductions_breakdown['PF'] ?? 0;
            
            if ($pfDeduction <= 0) continue; // Skip non-PF members

            $uan = $slip->employee->personalDetail->uan_number ?? '';
            $name = strtoupper($slip->employee->user->name);
            $gross = $slip->gross_earnings;

            // Logic: PF Wages usually capped at 15000 for EPS
            $epfWages = min($gross, 15000); // Simplification, strictly should follow actual calculation used
            $epsWages = min($gross, 15000);
            $edliWages = min($gross, 15000);

            // Contributions
            $epfShare = $pfDeduction; // Employee Share 12%
            $epsShare = round($epsWages * 0.0833); // 8.33%
            $epfDiff = $epfShare - $epsShare; // Difference goes to EPF

            if ($epfDiff < 0) $epfDiff = 0; // Safety

            $ncpDays = $slip->lop_days;
            $refund = 0;

            // Line format
            $line = implode('#', [
                $uan,
                $name,
                round($gross),
                round($epfWages),
                round($epsWages),
                round($edliWages),
                round($epfShare),
                round($epsShare),
                round($epfDiff),
                $ncpDays,
                $refund
            ]);

            $content .= $line . "\r\n";
        }

        return $content;
    }

    /**
     * Generate ESI Return Data (Array for Excel/CSV)
     */
    public function generateEsiReturn(Payroll $payroll)
    {
        $data = [];
        // Header
        $data[] = ['IP Number', 'IP Name', 'No of Days', 'Total Wages', 'Reason Code 0', 'Last Working Day'];

        $payslips = $payroll->payslips()->with(['employee.user', 'employee.personalDetail'])->get();

        foreach ($payslips as $slip) {
            $esiDeduction = $slip->deductions_breakdown['ESI'] ?? 0;
            
            if ($esiDeduction <= 0) continue; // Skip non-ESI

            $ipNumber = $slip->employee->personalDetail->esi_number ?? '';
            $name = $slip->employee->user->name;
            $days = $slip->payable_days;
            $wages = $slip->gross_earnings;

            $data[] = [
                $ipNumber,
                $name,
                $days,
                $wages,
                0, // Reason code (0 = Regular)
                '' // Last working day if exit
            ];
        }

        return $data;
    }

    /**
     * Generate Generic PT Report Data
     */
    public function generatePtReport(Payroll $payroll)
    {
        // Simple list of PT deductions
        $data = [];
        $data[] = ['Employee ID', 'Name', 'Gross Salary', 'PT Deducted', 'State'];

        $payslips = $payroll->payslips()->with(['employee.user', 'employee.location'])->get();

        foreach ($payslips as $slip) {
             $pt = $slip->deductions_breakdown['Professional Tax'] ?? 0;
             if ($pt <= 0) continue;

             $data[] = [
                 $slip->employee->employee_id,
                 $slip->employee->user->name,
                 $slip->gross_earnings,
                 $pt,
                 $slip->employee->location->state ?? 'N/A'
             ];
        }

        return $data;
    }
}
