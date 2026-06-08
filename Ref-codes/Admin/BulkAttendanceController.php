<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\AttendanceLog;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BulkAttendanceController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'status' => 'required|in:Present,Absent,On Leave',
            'shift_id' => 'nullable|exists:shifts,id',
            'exclude_ids' => 'array'
        ]);

        $date = Carbon::parse($validated['date']);
        
        // 1. Fetch Candidates (Active Employees)
        $query = Employee::where('status', 'Active');
        if (!empty($validated['department_id'])) {
            $query->where('department_id', $validated['department_id']);
        }
        if (!empty($validated['exclude_ids'])) {
            $query->whereNotIn('id', $validated['exclude_ids']);
        }
        $employees = $query->get();

        $count = 0;
        $skipped = 0;
        
        DB::transaction(function () use ($employees, $date, $validated, &$count, &$skipped) {
            foreach ($employees as $emp) {
                // Check existing log
                $exists = AttendanceLog::where('employee_id', $emp->id)
                    ->where('date', $date->toDateString())
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue; 
                }

                // Create Log
                $log = AttendanceLog::create([
                    'employee_id' => $emp->id,
                    'date' => $date->toDateString(),
                    'status' => $validated['status'],
                    'shift_id' => $validated['shift_id'] ?? null, // Default shift logic could go here
                    'is_late' => false,
                ]);

                // Create a mock "Session" if marked Present, to satisfy reports
                if ($validated['status'] === 'Present') {
                    // Assume 9 to 6 for simplicity, or use shift times
                    $inTime = $date->copy()->setTime(9, 0);
                    $outTime = $date->copy()->setTime(18, 0);

                    $log->sessions()->create([
                        'in_time' => $inTime,
                        'out_time' => $outTime,
                        'ip_address' => '127.0.0.1', // System Generated
                        'device_type' => 'System Bulk Action' 
                    ]);
                }

                $count++;
            }
        });

        return response()->json([
            'message' => "Successfully marked {$count} employees as {$validated['status']}.",
            'skipped' => $skipped
        ]);
    }
}
