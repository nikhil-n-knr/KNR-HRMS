<?php

namespace App\Services\Calendar\Sources;

use App\Services\Calendar\CalendarEvent;
use App\Services\Calendar\CalendarEventSource;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LeaveEventSource implements CalendarEventSource
{
    public function getEvents(Carbon $start, Carbon $end, User $user): Collection
    {
        $employeeId = $user->employee_id;
        
        if (!$employeeId) {
             $employee = \App\Models\Employee::where('user_id', $user->id)->first();
             $employeeId = $employee?->id;
        }

        if (!$employeeId) {
            return collect([]);
        }

        $leaves = LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->where('status', '!=', 'Rejected')
            ->where('status', '!=', 'Cancelled')
            ->where(function($q) use ($start, $end) {
                // Determine intersection of [start_date, end_date] with [start, end]
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end])
                  ->orWhere(function($sub) use ($start, $end) {
                      $sub->where('start_date', '<', $start)
                          ->where('end_date', '>', $end);
                  });
            })
            ->with('leaveType')
            ->get();

        return $leaves->map(function (LeaveRequest $leave) {
            $startDate = Carbon::parse($leave->start_date)->startOfDay();
            $endDate = Carbon::parse($leave->end_date)->endOfDay();
            
            $color = match($leave->status) {
                'Approved' => 'green',
                'Pending' => 'orange',
                default => 'gray'
            };

            $typeName = $leave->leaveType->name ?? 'Leave';

            return new CalendarEvent(
                id: 'leave-' . $leave->id,
                title: "On Leave: $typeName",
                start: $startDate,
                end: $endDate,
                type: 'leave',
                color: $color,
                allDay: true,
                metadata: [
                    'leave_id' => $leave->id,
                    'status' => $leave->status,
                    'reason' => $leave->reason
                ]
            );
        });
    }
}
