<?php

namespace App\Services\Exit;

use App\Models\Employee;
use App\Models\ExitClearance;
use App\Services\Infrastructure\LoggerService;
use Carbon\Carbon;

class ClearanceService
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Initiate clearance for an employee across standard departments.
     */
    public function initiateClearance(Employee $employee)
    {
        $departments = [
            ['module' => 'it', 'name' => 'IT Department', 'remarks' => 'Asset Recovery (Laptop, ID Card)'],
            ['module' => 'finance', 'name' => 'Finance Department', 'remarks' => 'Loan/Advance Recovery'],
            ['module' => 'admin', 'name' => 'Admin/Facilities', 'remarks' => 'Access Cards & Keys'],
            ['module' => 'hr', 'name' => 'Human Resources', 'remarks' => 'Exit Interview & Docs']
        ];

        foreach ($departments as $dept) {
            ExitClearance::firstOrCreate(
                ['employee_id' => $employee->id, 'module' => $dept['module']],
                [
                    'type' => $dept['name'],
                    'status' => 'Pending',
                    'remarks' => $dept['remarks']
                ]
            );
        }

        $this->logger->logInfo("Clearance Initiated", ['employee_id' => $employee->id]);
    }

    /**
     * Get the clearance status matrix for an employee.
     */
    public function getMatrix(Employee $employee)
    {
        return ExitClearance::where('employee_id', $employee->id)
            ->with('approver')
            ->get();
    }

    /**
     * Check if all mandatory clearances are marked as 'Cleared'.
     */
    public function isFullyCleared(Employee $employee): bool
    {
        return !ExitClearance::where('employee_id', $employee->id)
            ->where('status', '!=', 'Cleared')
            ->exists();
    }

    /**
     * Get total recovery amount from all clearances.
     */
    public function getTotalRecovery(Employee $employee): float
    {
        return (float) ExitClearance::where('employee_id', $employee->id)
            ->sum('due_amount');
    }
}
