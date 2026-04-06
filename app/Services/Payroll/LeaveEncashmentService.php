<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Services\Infrastructure\LoggerService;

class LeaveEncashmentService
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Calculate encashment amount based on accumulated leaves and salary structure.
     * 
     * Formula: (Monthly Basic / 26) * Days
     */
    public function calculate(Employee $employee, float $days): array
    {
        $salary = $employee->latestSalary;
        if (!$salary) {
            return ['amount' => 0, 'basis' => 0, 'error' => 'No salary structure found'];
        }

        // Logic: Standard practice uses Monthly Basic
        // Fallback: 40% of CTC if structure is complex
        $monthlyBasic = ($salary->annual_ctc / 12) * 0.4;
        
        // Check if we can get actual Basic from components
        $components = $salary->structure?->components;
        if ($components) {
            foreach ($components as $comp) {
                if (stripos($comp['name'], 'Basic') !== false) {
                    $monthlyBasic = $comp['monthly_amount'];
                    break;
                }
            }
        }

        $dayRate = round($monthlyBasic / 26, 2);
        $totalAmount = round($dayRate * $days, 0);

        $this->logger->logInfo("Leave Encashment Calculated", [
            'employee_id' => $employee->id,
            'days' => $days,
            'amount' => $totalAmount
        ]);

        return [
            'amount' => $totalAmount,
            'basis' => $monthlyBasic,
            'day_rate' => $dayRate,
            'days' => $days
        ];
    }
}
