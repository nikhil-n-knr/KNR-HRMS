<?php

namespace App\Services\Attendance;

use App\Models\AttendanceLog;
use App\Models\AttendancePolicy;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PolicyEngine
{
    protected $policy;

    public function __construct()
    {
        // For MVP, assuming single global policy or fetching per request context
        // Ideally, pass Tenant ID or Policy ID
        $this->policy = AttendancePolicy::first();
    }

    /**
     * Apply Late Mark Deductions.
     * Logic: If employee has > X late marks in current month, trigger deduction.
     */
    public function applyLatePolicy(AttendanceLog $log)
    {
        if (!$this->policy || !$log->is_late) {
            return;
        }

        $threshold = $this->policy->late_mark_threshold ?? 3;
        
        // Count lates in current month
        $startOfMonth = Carbon::parse($log->date)->startOfMonth();
        $endOfMonth = Carbon::parse($log->date)->endOfMonth();

        $lateCount = AttendanceLog::where('employee_id', $log->employee_id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('is_late', true)
            ->count();

        // Check if we hit a multiple of the threshold (e.g., 3rd, 6th, 9th late)
        // Note: This logic triggers EVERY time the count hits the mark.
        if ($lateCount > 0 && ($lateCount % $threshold == 0)) {
            $this->applyDeduction($log, "Late Mark Limit Reached ({$lateCount} lates)");
        }
    }

    /**
     * Apply "Sandwich Rule".
     * Logic: If Absent on Friday AND Monday, mark Weekend as Absent (or Leave).
     * This usually runs via Cron Job or on Monday's EOD Calculation.
     */
    public function applySandwichRule($employeeId, $mondayDate)
    {
        if (!$this->policy || !$this->policy->sandwich_rule_enabled) {
            return;
        }

        $monday = Carbon::parse($mondayDate);
        $friday = $monday->copy()->subDays(3); // Assuming Sat/Sun off

        // Check Friday Status
        $fridayLog = AttendanceLog::where('employee_id', $employeeId)
            ->where('date', $friday->toDateString())
            ->where('status', 'Absent')
            ->first();

        // Check Monday Status
        $mondayLog = AttendanceLog::where('employee_id', $employeeId)
            ->where('date', $monday->toDateString())
            ->where('status', 'Absent')
            ->first();

        if ($fridayLog && $mondayLog) {
            // Sandwich Detected!
            // Action: Mark Sat & Sun as Absent or Deduct Leave
            // Implementation: Create Absent Logs for Sat/Sun
            $this->createSandwichDeduction($employeeId, $monday->copy()->subDays(2)); // Sat
            $this->createSandwichDeduction($employeeId, $monday->copy()->subDays(1)); // Sun
            
            Log::info("Sandwich Rule Applied for Employee {$employeeId} on {$mondayDate}");
        }
    }

    private function applyDeduction(AttendanceLog $sourceLog, $reason)
    {
        $rule = $this->policy->deduction_rule; // e.g. { "deduct_leave": 0.5, "type": "CL" }
        
        if (!$rule || !isset($rule['deduct_leave'])) return;

        // In a real system, this would interact with LeaveBalanceService to debit balance
        // For now, we log it or create a discrete "Adjustment" record
        
        Log::info("POLICY ACTION: [{$reason}] - Deducting {$rule['deduct_leave']} of {$rule['type']} for User {$sourceLog->employee_id}");

        // Example: Create a "System" Leave Request that is auto-approved
        /*
        LeaveRequest::create([
            'employee_id' => $sourceLog->employee_id,
            'leave_type_id' => $this->getLeaveTypeId($rule['type']),
            'start_date' => $sourceLog->date,
            'end_date' => $sourceLog->date,
            'reason' => $reason,
            'status' => 'Approved',
            'is_auto_deduction' => true
        ]);
        */
    }

    private function createSandwichDeduction($employeeId, $date)
    {
        AttendanceLog::updateOrCreate(
            ['employee_id' => $employeeId, 'date' => $date->toDateString()],
            [
                'status' => 'Absent',
                'is_regularized' => false,
                'total_work_minutes' => 0,
                // 'remarks' => 'Sandwich Rule Deduction'
            ]
        );
    }
}
