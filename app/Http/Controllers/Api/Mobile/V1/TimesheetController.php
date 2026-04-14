<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Timesheet;
use App\Models\Project;
use App\Models\Task;
use App\Services\Infrastructure\LoggerService;
use Carbon\Carbon;

class TimesheetController extends Controller
{
    /**
     * Get Timesheet History & Summaries
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['error' => 'No employee profile.'], 403);
        }

        $timesheets = Timesheet::where('employee_id', $employee->id)
            ->with(['project:id,name', 'task:id,title'])
            ->latest('date')
            ->paginate(20);

        // Weekly Summary
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        $weeklyHours = Timesheet::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->sum('hours_spent');

        return response()->json([
            'timesheets' => $timesheets,
            'summary' => [
                'weekly_hours' => round($weeklyHours, 2),
                'target_hours' => 40, // Base target
            ]
        ]);
    }

    /**
     * Log Time (Store Entry)
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'nullable|exists:project_tasks,id',
            'hours' => 'required|numeric|min:0.5|max:24',
            'description' => 'required|string|max:500',
        ]);

        $employee = $request->user()->employee;
        
        // Daily Limit Check
        $existing = Timesheet::where('employee_id', $employee->id)
            ->where('date', $request->date)
            ->sum('hours_spent');
            
        if (($existing + $request->hours) > 24) {
            return response()->json(['message' => 'Total hours for the day cannot exceed 24.'], 422);
        }

        $project = Project::find($request->project_id);

        $timesheet = Timesheet::create([
            'employee_id' => $employee->id,
            'date' => $request->date,
            'project_id' => $request->project_id,
            'project_name' => $project->name,
            'task_id' => $request->task_id,
            'task_description' => $request->description,
            'hours_spent' => $request->hours,
            'status' => 'Submitted' // Auto-submit from mobile for simplicity
        ]);

        \Log::context(['user_id' => $request->user()->id, 'action' => 'mobile_timesheet_log']);
        LoggerService::info("Timesheet logged via Mobile", ['hours' => $request->hours, 'project' => $project->name]);

        return response()->json([
            'success' => true,
            'message' => 'Time logged successfully.',
            'entry' => $timesheet
        ]);
    }
}
