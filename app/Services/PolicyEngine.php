<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\AttendancePolicy;
use App\Models\Employee;
use Illuminate\Support\Carbon;

class PolicyEngine
{
    /**
     * Evaluate an attendance log against the effective policy.
     * Returns an array of status flags and calculated metrics.
     */
    public function evaluate(AttendanceLog $log)
    {
        $employee = $log->employee;
        $policy = $this->getEffectivePolicy($employee);

        if (!$policy) {
            return $this->getDefaultEvaluation($log);
        }

        $results = [
            'status' => $log->status,
            'late_minutes' => 0,
            'early_leave_minutes' => 0,
            'overtime_minutes' => 0,
            'is_half_day' => false,
            'wfh_violation' => false,
            'policy_applied' => $policy->name
        ];

        // 1. Evaluate Timings (Late/Early)
        $timings = $this->evaluateTimings($log, $policy);
        $results = array_merge($results, $timings);

        // 2. Evaluate WFH Integrity
        if ($log->is_wfh) {
            $wfhCheck = $this->evaluateWFH($log, $policy);
            if (!$wfhCheck['valid']) {
                $results['wfh_violation'] = true;
                $results['status'] = 'Absent'; // Strict policy: WFH violation = Absent? Or just flag it.
                // Let's keep status but maybe mark half day
                if ($wfhCheck['penalty'] === 'half_day') {
                    $results['is_half_day'] = true;
                }
            }
        }

        // 3. Evaluate Overtime
        $results['overtime_minutes'] = $this->evaluateOvertime($log, $policy);

        return $results;
    }

    /**
     * Resolve Policy Hierarchy: Employee > Department > Global (Tenant Default)
     */
    public function getEffectivePolicy(Employee $employee)
    {
        // 1. Employee Specific
        if ($employee->attendance_policy_id) {
            return AttendancePolicy::find($employee->attendance_policy_id);
        }

        // 2. Department Default
        if ($employee->department && $employee->department->attendance_policy_id) {
             return AttendancePolicy::find($employee->department->attendance_policy_id);
        }

        // 3. Global Default (Highest priority global policy)
        return AttendancePolicy::where('tenant_id', $employee->tenant_id)
            ->orderBy('priority', 'desc')
            ->first();
    }

    protected function evaluateTimings(AttendanceLog $log, AttendancePolicy $policy)
    {
        $rules = $policy->rules ?? [];
        $graceLate = $rules['grace_late_entry'] ?? 15; // default 15 mins
        $halfDayThreshold = $rules['half_day_hours'] ?? 4; 

        $shiftStart = Carbon::parse($log->shift_start);
        $checkIn = Carbon::parse($log->check_in);
        
        $lateMinutes = 0;
        if ($checkIn->gt($shiftStart->addMinutes($graceLate))) {
            $lateMinutes = $checkIn->diffInMinutes($shiftStart);
        }

        $isHalfDay = false;
        if ($log->duration) {
            $hours = $log->duration / 60;
            if ($hours < $halfDayThreshold) {
                $isHalfDay = true;
            }
        }

        return [
            'late_minutes' => $lateMinutes,
            'is_half_day' => $isHalfDay
        ];
    }

    protected function evaluateWFH(AttendanceLog $log, AttendancePolicy $policy)
    {
        $wfhRules = $policy->wfh_policy ?? [];
        if (empty($wfhRules)) {
            return ['valid' => true, 'penalty' => null];
        }

        // Rule: Minimum Duraton for WFH
        $minMinutes = $wfhRules['min_minutes'] ?? 480; // 8 hours
        if ($log->duration < $minMinutes) {
            return ['valid' => false, 'penalty' => 'half_day'];
        }

        // Rule: Require Check-in By
        if (isset($wfhRules['latest_check_in'])) {
             $latestTime = Carbon::parse($log->date . ' ' . $wfhRules['latest_check_in']);
             $checkIn = Carbon::parse($log->check_in);
             if ($checkIn->gt($latestTime)) {
                 return ['valid' => false, 'penalty' => 'late_mark'];
             }
        }

        return ['valid' => true, 'penalty' => null];
    }

    protected function evaluateOvertime(AttendanceLog $log, AttendancePolicy $policy)
    {
        $otRules = $policy->overtime_policy ?? [];
        $minOtMinutes = $otRules['min_minutes'] ?? 30;
        
        // Simple calculation: Duration - Shift Duration
        // Assuming shift is 9 hours (540 mins) context needed, but for now using raw duration > expected
        // Actually, logic usually is OutTime - ShiftEnd
        if (!$log->check_out || !$log->shift_end) return 0;

        $shiftEnd = Carbon::parse($log->shift_end);
        $checkOut = Carbon::parse($log->check_out);

        if ($checkOut->gt($shiftEnd)) {
            $rawOt = $checkOut->diffInMinutes($shiftEnd);
            if ($rawOt >= $minOtMinutes) {
                return $rawOt;
            }
        }

        return 0;
    }

    protected function getDefaultEvaluation($log)
    {
        // Fallback simple logic if no policy exists
        return [
            'status' => $log->status,
             'late_minutes' => 0,
            'early_leave_minutes' => 0,
            'overtime_minutes' => 0,
            'is_half_day' => false,
            'wfh_violation' => false,
            'policy_applied' => 'Hardcoded Fallback'
        ];
    }
}
