<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Task;
use App\Models\BugTicket;
use App\Models\AttendanceLog;
use App\Services\Attendance\AttendanceRegistryService;
use Carbon\Carbon;

class MobileController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee && config('app.env') === 'local') {
            $employee = \App\Models\Employee::first();
            if ($employee) {
                $user->employee_id = $employee->id; // Temporary link for session
            }
        }

        if (!$employee) {
            return Inertia::render('Generics/Error', ['message' => 'Your user account is not linked to any Employee profile. Please contact HR to link your account.']);
        }

        $today = Carbon::today();
        
        $assignedTasksCount = Task::whereHas('assignees', fn($q) => $q->where('employee_id', $employee->id))
            ->whereIn('status', ['In Progress', 'To Do'])
            ->count();

        $openBugsCount = BugTicket::whereHas('assignees', function($q) use ($user) {
            $q->where('assignee_id', $user->id)
              ->where('assignee_type', get_class($user));
        })
            ->where('workflow_stage_id', '!=', 5) // status_id was used, but model uses workflow_stage_id
            ->count();

        $attendanceLog = AttendanceLog::with('sessions')
            ->where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        // Tactical Insights Calculation
        $taskVelocity = 0; // Future math
        $bugUrgency = $openBugsCount > 5 ? 'High' : 'Normal';
        
        $shiftProgress = 0;
        if ($attendanceLog && $attendanceLog->check_in) {
            $checkIn = Carbon::parse($attendanceLog->check_in);
            $shiftProgress = min(100, round(($checkIn->diffInMinutes(now()) / 540) * 100)); // Assuming 9hr shift
        }

        return Inertia::render('MobileApp/Pages/Dashboard', [
            'assignedTasksCount' => $assignedTasksCount,
            'openBugsCount' => $openBugsCount,
            'attendanceData' => [
                'log' => $attendanceLog,
                'progress' => $shiftProgress,
                'status' => $attendanceLog ? 'Active' : 'Offline'
            ],
            'tactical' => [
                'velocity' => 85, // Mocked for UI
                'urgency' => $bugUrgency,
                'health' => 92
            ]
        ]);
    }

    public function tasks()
    {
        return Inertia::render('MobileApp/Pages/Tasks');
    }

    public function chat()
    {
        return Inertia::render('MobileApp/Pages/Chat');
    }

    public function approvals()
    {
        return Inertia::render('MobileApp/Pages/Approvals');
    }

    public function profile()
    {
        return Inertia::render('MobileApp/Pages/Profile');
    }

    public function timesheet()
    {
        return Inertia::render('MobileApp/Pages/Timesheet');
    }

    public function requests()
    {
        return Inertia::render('MobileApp/Pages/Requests');
    }

    public function notifications()
    {
        return Inertia::render('MobileApp/Pages/Notifications');
    }
}
