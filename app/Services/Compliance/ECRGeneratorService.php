<?php

namespace App\Services\Compliance;

use App\Models\Payroll;
use App\Models\Employee;
use Carbon\Carbon;

class ECRGeneratorService
{
    /**
     * Run Diagnostic Checks on the Batch
     */
    public function validateBatch(Payroll $payroll)
    {
        $errors = [
            'uan_missing' => [],
            'ncp_mismatch' => [],
            'wage_negative' => [],
            'kyc_missing' => []
        ];

        $items = $payroll->payslips()->with(['employee', 'employee.personalDetail'])->get();

        foreach ($items as $item) {
            $emp = $item->employee;
            
            // 1. UAN Check
            if (empty($emp->uan_number)) {
                $errors['uan_missing'][] = [
                    'id' => $emp->id,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                    'message' => 'UAN Number is empty'
                ];
            }

            // 2. NCP Logic Check (Days Paid vs Days Present)
            // Just a logic check: if total_days - paid_days < 0, data is weird
            $ncp = $item->lop_days;
            if ($ncp < 0) {
                $errors['ncp_mismatch'][] = [
                    'id' => $emp->id,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                    'message' => "LOP Days ({$item->lop_days}) detected"
                ];
            }

            // 3. Negative Wages
            if ($item->gross_earnings < 0) {
                 $errors['wage_negative'][] = [
                    'id' => $emp->id,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                    'message' => 'Gross salary is negative'
                ];
            }
            
            // 4. KYC Check (Flexible)
            if (empty($emp->aadhaar_number)) {
                 $errors['kyc_missing'][] = [
                    'id' => $emp->id,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                    'message' => 'Aadhaar Number missing'
                ];
            }
        }

        return $errors;
    }

    /**
     * Get Preview Data for Spot Check
     */
    public function getPreview(Payroll $payroll)
    {
        $rows = $this->calculateRows($payroll);
        
        $totals = [
            'gross' => 0,
            'epf' => 0,
            'eps' => 0,
            'employees' => count($rows)
        ];

        foreach ($rows as $row) {
            $totals['gross'] += $row['gross'];
            $totals['epf'] += $row['er_epf_share']; // Employer EPF
            $totals['eps'] += $row['er_eps_share']; // Employer Pension
        }

        return [
            'totals' => $totals,
            'sample' => array_slice($rows, 0, 5) // First 5
        ];
    }

    /**
     * Generate Actual Text Content
     */
    public function generatePFText(Payroll $payroll)
    {
        $rows = $this->calculateRows($payroll);
        $lines = [];

        foreach ($rows as $r) {
            // UAN#|MEMBER_NAME|GROSS|EPF_WAGES|EPS_WAGES|EE_SHARE_REM|EE_SHARE_DUE|ER_SHARE_EPF|ER_SHARE_EPS|NCP_DAYS|REFUND
            $line = implode('#~#', [
                $r['uan'],
                $r['name'],
                $r['gross'],
                $r['epf_wages'],
                $r['eps_wages'],
                $r['ee_share'],
                $r['ee_share'], // Remitted = Due usually
                $r['er_epf_share'],
                $r['er_eps_share'],
                $r['ncp_days'],
                0 // Refund
            ]);
            $lines[] = $line;
        }

        return implode("\n", $lines);
    }

    /**
     * Core Calculation Logic
     */
    private function calculateRows(Payroll $payroll)
    {
        $items = $payroll->payslips()->with(['employee', 'employee.personalDetail'])->get();
        $processed = [];

        foreach ($items as $item) {
            $emp = $item->employee;
            if (!$emp->uan_number) continue;

            $basic = $this->getComponentValue($item, 'Basic');
            $gross = $item->gross_earnings;

            // --- 1. Determine PF Wages ---
            // If International Worker -> Basics (No Cap)
            if ($emp->is_international_worker) {
                $epf_wages = $basic;
                $eps_wages = $basic; // IW gets full pension?? Usually check rules. Assuming full for now logic.
            } 
            //If Capped -> Min(Basic, 15000)
            elseif (!($emp->pf_uncapped ?? false)) {
                $epf_wages = min($basic, 15000);
                $eps_wages = min($basic, 15000);
            } 
            // If Uncapped -> Actual Basic
            else {
                $epf_wages = $basic;
                $eps_wages = min($basic, 15000); // EPS is always capped at 15k for domestic
            }

            // --- 2. Age Check (Senior Citizen Logic) ---
            // If Age > 58, EPS = 0, ER Share goes fully to EPF
            $isSenior = false;
            if ($emp->personalDetail?->dob) {
                $dob = Carbon::parse($emp->personalDetail->dob);
                if ($dob->age >= 58) {
                    $isSenior = true;
                    $eps_wages = 0; // No Pension
                }
            }

            // --- 3. Share Calculation ---
            $ee_share = round($epf_wages * 0.12); // Employee 12%
            
            if ($isSenior) {
                $er_eps_share = 0;
                $er_epf_share = round($epf_wages * 0.12); // Full 12% to EPF
            } else {
                $er_eps_share = round($eps_wages * 0.0833);
                // Balance goes to EPF. 
                // Note: Total ER Liability is 12% of EPF Wages (capped/uncapped based on rules)
                // Standard: 12% of EPF Wages  - EPS Share
                $er_epf_share = round($epf_wages * 0.12) - $er_eps_share;
            }

            // --- 4. NCP Days ---
            $ncp_days = $item->lop_days;

            $processed[] = [
                'uan' => $emp->uan_number,
                'name' => strtoupper($emp->first_name . ' ' . $emp->last_name),
                'gross' => round($gross),
                'epf_wages' => round($epf_wages),
                'eps_wages' => round($eps_wages),
                'ee_share' => $ee_share,
                'er_epf_share' => $er_epf_share,
                'er_eps_share' => $er_eps_share,
                'ncp_days' => $ncp_days
            ];
        }

        return $processed;
    }

    private function getComponentValue($item, $name)
    {
        $earnings = $item->earnings_breakdown;
        return $earnings[$name] ?? 0;
    }
}
