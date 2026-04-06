<?php

namespace App\Services\Payroll;

use App\Models\VariablePayout;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BonusService
{
    /**
     * Import Bonuses from CSV Data
     * CSV Format: Employee Code, Amount, Type, Remarks
     */
    public function importFromCsv(array $rows, int $month, int $year)
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($rows as $index => $row) {
            // Mapping: 0 => Code, 1 => Amount, 2 => Type, 3 => Remarks
            $code = trim($row[0] ?? '');
            if (empty($code) || $code === 'Employee Code') continue; // Skip header

            $employee = Employee::where('employee_code', $code)->first();
            
            if (!$employee) {
                $results['failed']++;
                $results['errors'][] = "Row " . ($index + 1) . ": Employee $code not found.";
                continue;
            }

            $amount = floatval($row[1] ?? 0);
            if ($amount <= 0) {
                $results['failed']++;
                $results['errors'][] = "Row " . ($index + 1) . ": Invalid Amount.";
                continue;
            }

            VariablePayout::create([
                'employee_id' => $employee->id,
                'amount' => $amount,
                'type' => $row[2] ?? 'Bonus',
                'pay_month' => $month,
                'pay_year' => $year,
                'remarks' => $row[3] ?? 'Bulk Import',
                'status' => 'Pending',
                'created_by' => auth()->id()
            ]);

            $results['success']++;
        }

        return $results;
    }

    /**
     * Generate Bonuses based on Rules
     */
    public function generateByRule($filters, $ruleData, int $month, int $year)
    {
        // 1. Fetch Target Employees
        $query = Employee::where('status', 'active');
        
        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }
        
        // Exclude those who already have this payout type for this month?
        // Optional. For now, we allow multiple.

        $employees = $query->with('latestSalary')->get();
        $count = 0;

        DB::transaction(function () use ($employees, $ruleData, $month, $year, &$count) {
            foreach ($employees as $emp) {
                $amount = 0;

                if ($ruleData['type'] === 'fixed') {
                    $amount = floatval($ruleData['value']);
                } elseif ($ruleData['type'] === 'percent_ctc') {
                    // % of Annual CTC
                    $ctc = $emp->latestSalary?->annual_ctc ?? 0;
                    $amount = ($ctc * ($ruleData['value'] / 100)); // Usually Annual? Or Monthly?
                    // Usually "10% of CTC" means 10% of Annual CTC distributed? 
                    // Or 10% of Monthly CTC. Context matters. 
                    // Let's assume input is "Percentage of Monthly Gross".
                    $monthlyGross = ($ctc / 12); 
                    $amount = ($monthlyGross * ($ruleData['value'] / 100));
                }

                if ($amount > 0) {
                    VariablePayout::create([
                        'employee_id' => $emp->id,
                        'amount' => round($amount, 2),
                        'type' => $ruleData['payout_type'] ?? 'Performance Bonus',
                        'pay_month' => $month,
                        'pay_year' => $year,
                        'remarks' => $ruleData['remarks'] ?? 'Auto-Generated Rule',
                        'status' => 'Pending', // Draft
                        'created_by' => auth()->id()
                    ]);
                    $count++;
                }
            }
        });

        return $count;
    }
}
