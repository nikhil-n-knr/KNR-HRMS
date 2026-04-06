<?php

namespace App\Services\ProjectManagement;

use App\Models\User;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\WorkAssignment;
use Carbon\Carbon;

class AssignmentLogic
{
    /**
     * Check if a user is available for a specific date range.
     * Considers: Holidays, Approved Leaves.
     *
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return array ['available' => bool, 'reason' => string|null]
     */
    public function checkAvailability(int $userId, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // 1. Check Personal Leave
        // Fetch employee ID from user (Assuming User has One Employee)
        // TODO: Ensure User->Employee relation is solid. For now, assuming User ID.
        // Actually, LeaveRequest uses employee_id. We need to map User -> Employee.
        $user = User::with('employee')->find($userId);
        if (!$user || !$user->employee) {
            return ['available' => true, 'reason' => 'User has no employee record to check leaves against.'];
        }

        $conflictingLeave = LeaveRequest::where('employee_id', $user->employee->id)
            ->where('status', 'Approved')
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                      ->orWhereBetween('end_date', [$start, $end])
                      ->orWhere(function ($q) use ($start, $end) {
                          $q->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                      });
            })->first();

        if ($conflictingLeave) {
            return [
                'available' => false, 
                'reason' => "User is on leave: {$conflictingLeave->leaveType->name} ({$conflictingLeave->start_date->format('M d')} - {$conflictingLeave->end_date->format('M d')})"
            ];
        }

        // 2. Check System Holidays (Global)
        // Optimization: Checking raw dates. Location filtering added later if needed.
        $holidays = Holiday::whereBetween('date', [$start, $end])->get();
        if ($holidays->isNotEmpty()) {
            return [
                'available' => false, // Partial availability, but framing as conflict for now
                'reason' => "Conflict with Holiday: " . $holidays->first()->name
            ];
        }

        return ['available' => true, 'reason' => null];
    }

    /**
     * Suggest the best assignee primarily based on load (Hours Assigned).
     * 
     * @param array $candidateUserIds
     * @return int|null Best User ID
     */
    public function suggestAssignee(array $candidateUserIds)
    {
        // Calculate total allocated hours for each candidate in active projects
        $loads = WorkAssignment::whereIn('assignee_id', $candidateUserIds)
            ->where('assignee_type', User::class)
            ->selectRaw('assignee_id, SUM(allocated_hours) as total_load')
            ->groupBy('assignee_id')
            ->pluck('total_load', 'assignee_id')
            ->toArray();

        // Fill missing with 0
        foreach ($candidateUserIds as $id) {
            if (!isset($loads[$id])) {
                $loads[$id] = 0;
            }
        }

        // Sort by Load ASC
        asort($loads);
        
        // Return key of first (lowest load)
        return array_key_first($loads);
    }
}
