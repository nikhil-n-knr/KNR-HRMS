<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Timesheet;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\Workflow\WorkflowService;
use App\Services\AI\AnomalyDetectionService;

class TimesheetController extends Controller
{
    /**
     * Display a listing of personal timesheets.
     */
    public function dashboard()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return Inertia::render('Employee/Attendance/TimesheetDashboard', [
                'projects' => [],
                'all_projects' => []
            ]);
        }

        // 1. Assigned Projects
        $assignedProjects = $employee->projects()
            ->where('projects.status', 'active')
            ->select('projects.id', 'projects.name', 'projects.code')
            ->distinct()
            ->get();
            
        // 2. All Active Projects (for "Show All" option)
        // If user wants to log time on unassigned project
        $allProjects = \App\Models\Project::whereIn('status', ['active', 'planning', 'maintenance'])
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        return Inertia::render('Employee/Attendance/Hub', [
            'tab' => 'timesheets',
            'projects' => $assignedProjects,
            'all_projects' => $allProjects
        ]);
    }

    public function index(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            $error = 'No Employee profile found.';
            if ($request->wantsJson()) {
                return response()->json(['error' => $error], 403);
            }
            return back()->with('error', $error);
        }
        
        $query = Timesheet::where('employee_id', $employee->id)
            ->with('project') // Eager load project
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($request->has('date')) {
            $query->where('date', $request->date);
        }

        $timesheets = $query->paginate(15)->withQueryString();

        // Fetch Projects for Dropdown
        // Implementation: Fetch All Active Projects + Projects Assigned to Employee
        // For now: All Active Projects
        $projects = \App\Models\Project::where('status', 'Active')
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'timesheets' => $timesheets,
                'projects' => $projects,
                'filters' => $request->only(['date'])
            ]);
        }

        return Inertia::render('Employee/Attendance/Hub', [
            'tab' => 'timesheets',
            'timesheets' => $timesheets,
            'projects' => $projects, // Pass to View
            'filters' => $request->only(['date'])
        ]);
    }

    /**
     * Store a newly created timesheet entry.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'project_id' => 'required|exists:projects,id', // Enforce Project Selection
            'task_description' => 'required|string',
            'hours_spent' => 'required|numeric|min:0.1|max:24',
        ]);

        $employee = Auth::user()->employee;

        if (!$employee) {
            if ($request->wantsJson()) return response()->json(['message' => 'No Employee profile found.'], 404);
            return back()->with('error', 'No Employee profile found.');
        }

        // --- Policy Alignment: Max Hours Verification ---
        $existingHours = Timesheet::where('employee_id', $employee->id)
            ->where('date', $request->date)
            ->sum('hours_spent');

        // Fetch Employee Policy
        $policy = $employee->effectiveAttendancePolicy; 
        $maxHours = 24; // Absolute hard limit
        
        if ($policy && !empty($policy->timesheet_policy)) {
             $maxHours = $policy->timesheet_policy['daily_max_hours'] ?? 24;
             
             // Check min hours? Usually min hours is a warning on submit, not on individual entry creation.
        }

        if (($existingHours + $request->hours_spent) > $maxHours) {
             return back()->with('error', "Total hours for the day cannot exceed {$maxHours}. You have already logged {$existingHours} hours.");
        }
        // ---------------------------------------------------

        $project = \App\Models\Project::find($request->project_id);

        $timesheet = Timesheet::create([
            'employee_id' => $employee->id,
            'date' => $request->date,
            'project_id' => $request->project_id,
            'project_name' => $project->name, // Legacy support
            'task_description' => $request->task_description,
            'hours_spent' => $request->hours_spent,
            'status' => 'Draft' 
        ]);

        // Run AI Anomaly Detection immediately
        $detector = app(AnomalyDetectionService::class);
        $detector->analyzeTimesheet($timesheet);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Timesheet entry added.', 'timesheet' => $timesheet], 201);
        }

        return back()->with('success', 'Timesheet entry added. Please submit it when ready.');
    }

    /**
     * Submit timesheet for approval (Starts Workflow).
     */
    public function submit(Request $request, Timesheet $timesheet)
    {
        if (!Auth::user()->employee) {
             if ($request->wantsJson()) return response()->json(['message' => 'No Employee Profile'], 403);
             return back()->with('error', 'No Employee Profile');
        }
        if ($timesheet->employee_id !== Auth::user()->employee?->id) {
             if ($request->wantsJson()) return response()->json(['message' => 'Unauthorized'], 403);
             return back()->with('error', 'Unauthorized');
        }
        
        try {
            $timesheet->update(['status' => 'Submitted']);
            
            $workflow = app(WorkflowService::class);
            $instance = $workflow->initializeWorkflow('timesheet', $timesheet->id, Auth::user());

            if (!$instance) {
                // No workflow defined -> Auto Approve
                $timesheet->update(['status' => 'Approved']);
                return back()->with('success', 'Timesheet approved automatically (No approval workflow configured).');
            }

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Timesheet submitted for approval successfully.']);
            }

            return back()->with('success', 'Timesheet submitted for approval successfully.');
        } catch (\Exception $e) {
             if ($request->wantsJson()) return response()->json(['message' => 'Submission failed: ' . $e->getMessage()], 500);
            return back()->with('error', 'Submission failed: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified timesheet.
     */
    public function update(Request $request, Timesheet $timesheet)
    {
        // Ownership Check
        if ($timesheet->employee_id !== Auth::user()->employee?->id) {
            if ($request->wantsJson()) return response()->json(['message' => 'Unauthorized'], 403);
            return back()->with('error', 'Unauthorized');
        }

        // Only allow editing if Draft or Rejected
        if (!in_array($timesheet->status, ['Draft', 'Rejected'])) {
             if ($request->wantsJson()) return response()->json(['message' => 'Cannot edit submitted or approved timesheets.'], 403);
            return back()->with('error', 'Cannot edit submitted or approved timesheets.');
        }

        $request->validate([
            'project_name' => 'nullable|string|max:255',
            'task_description' => 'required|string',
            'hours_spent' => 'required|numeric|min:0.1|max:24',
        ]);

        $timesheet->update($request->only(['project_name', 'task_description', 'hours_spent']));

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Timesheet updated.', 'timesheet' => $timesheet]);
        }

        return back()->with('success', 'Timesheet updated.');
    }

    /**
     * Remove the specified timesheet.
     */
    public function destroy(Timesheet $timesheet)
    {
        // ... existing destroy logic ...
        if (request()->wantsJson()) {
             // For destroy, we rely on implicit binding but need manual check if we want to be safe with injection
             // But usually implicit binding works.
        }

        if ($timesheet->employee_id !== Auth::user()->employee?->id) {
             if (request()->wantsJson()) return response()->json(['message' => 'Unauthorized'], 403);
             return back()->with('error', 'Unauthorized');
        }

        if ($timesheet->status !== 'Draft') {
             if (request()->wantsJson()) return response()->json(['message' => 'Only draft timesheets can be deleted.'], 403);
            return back()->with('error', 'Only draft timesheets can be deleted.');
        }

        $timesheet->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Entry deleted.']);
        }

        return back()->with('success', 'Entry deleted.');
    }

    /**
     * Fetch Tasks for a Project (Assigned vs All)
     */
    public function getProjectTasks(Request $request, $projectId)
    {
        $project = \App\Models\Project::findOrFail($projectId);
        $user = Auth::user();

        // 1. Assigned Tasks (My Tasks)
        // 1. Assigned Tasks (My Tasks)
        // Note: Task::assignees() is BelongsToMany(Employee), so we filter by employee id
        $employeeId = $user->employee ? $user->employee->id : null;
        
        $assignedTasks = \App\Models\Task::where('project_id', $project->id)
            ->whereHas('assignees', function($q) use ($employeeId) {
                if ($employeeId) {
                    $q->where('employees.id', $employeeId);
                } else {
                    $q->whereRaw('1 = 0'); // No employee profile, no tasks
                }
            })
            ->where('stage_id', '!=', 6) // Exclude done? Assumed from previous code
            ->select('id', 'title') 
            ->get();

        // 2. All Tasks (If requested)
        $allTasks = collect([]);
        if ($request->boolean('all')) {
            $allTasks = \App\Models\Task::where('project_id', $project->id)
                ->select('id', 'title')
                ->get();
        }

        return response()->json([
            'assigned_tasks' => $assignedTasks,
            'all_tasks' => $allTasks
        ]);
        return response()->json([
            'assigned_tasks' => $assignedTasks,
            'all_tasks' => $allTasks
        ]);
    }

    /**
     * Fetch Timesheet Entries for a Date Range (for Weekly Grid)
     */
    public function getWeeklyLog(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);

        $entries = \App\Models\Timesheet::where('employee_id', Auth::user()->employee->id)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->with(['project:id,name,code', 'task:id,title,code'])
            ->get();

        return response()->json($entries);
    }
    /**
     * Fetch ALL Assigned Tasks for the User (Across Projects) for Weekly Grid
     */
    public function getAssignedTasks(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Configuration for Timesheet Range (Hardcoded for now as per plan, can be moved to DB settings later)
            // "past which is more than month and future more than momth"
            $config = [
                'allowed_past_days' => 31,
                'allowed_future_days' => 31 // or maybe 7? Users usually don't log far future. defaulting to month.
            ];

            // Verify User has Employee Profile
            if (!$user->employee) {
                // If no employee profile, they can't be assigned tasks via Employee model usually.
                // But let's return empty to avoid 500.
                return response()->json(['tasks' => [], 'config' => $config]);
            }

            $tasks = \App\Models\Task::query()
                ->select('project_tasks.id', 'project_tasks.title', 'project_tasks.project_id', 'project_tasks.stage_id') // Removed tasks.code
                ->whereHas('assignees', function($q) use ($user) {
                    $q->where('employees.user_id', $user->id);
                })
                ->whereHas('project', function($q) {
                    $q->where('projects.status', 'active');
                })
                ->where('project_tasks.stage_id', '!=', 6)
                ->with(['project:id,name,code'])
                ->orderBy('project_tasks.project_id')
                ->get()
                ->map(function($t) {
                    return [
                        'id' => $t->id,
                        'title' => $t->title,
                        'code' => '', // No Code column
                        'project_id' => $t->project_id,
                        'project_name' => $t->project->name ?? 'Unknown Project',
                        'project_code' => $t->project->code ?? '',
                    ];
                });

            return response()->json([
                'tasks' => $tasks,
                'config' => $config
            ]);
        } catch (\Exception $e) {
            \Log::error('Timesheet Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch tasks', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk Store (Weekly Grid)
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'entries' => 'required|array',
            'entries.*.date' => 'required|date',
            'entries.*.project_id' => 'required|exists:projects,id',
            'entries.*.hours' => 'required|numeric|min:0.1|max:24',
            'entries.*.task_id' => 'nullable|exists:project_tasks,id',
            'entries.*.task_title' => 'nullable|string',
        ]);

        $employee = Auth::user()->employee;
        if (!$employee) return response()->json(['message' => 'No Employee Profile'], 403);

        // Date Restrictions
        $allowedPast = now()->subDays(31)->startOfDay();
        $allowedFuture = now()->addDays(31)->endOfDay();

        $entries = $request->input('entries');
        $savedCount = 0;
        $errors = [];

        \DB::transaction(function() use ($entries, $employee, $allowedPast, $allowedFuture, &$savedCount, &$errors) {
            foreach ($entries as $index => $entry) {
                $date = \Carbon\Carbon::parse($entry['date']);

                // 1. Range Check
                if ($date->lt($allowedPast) || $date->gt($allowedFuture)) {
                    // Skip or fail? Let's skip and warn.
                    // Actually throwing exception rolls back everything.
                    // Let's throw validation error for the first one found.
                    throw new \Illuminate\Validation\ValidationException(\Illuminate\Support\Facades\Validator::make([], []), 
                        \Illuminate\Validation\ValidationException::withMessages(['entries.'.$index.'.date' => ["Date $entry[date] is outside allowed range."]])
                    );
                }

                
                // Check Daily Limit (Aggregate per day)
                $currentDailyTotal = Timesheet::where('employee_id', $employee->id)
                    ->where('date', $entry['date'])
                    ->sum('hours_spent');
                
                // Fetch Employee Policy
                $policy = $employee->effectiveAttendancePolicy; 
                $maxHours = 24; 
                if ($policy && !empty($policy->timesheet_policy)) {
                        $maxHours = $policy->timesheet_policy['daily_max_hours'] ?? 24;
                }

                if (($currentDailyTotal + $entry['hours']) > $maxHours) {
                     // Better exception
                     throw new \Illuminate\Validation\ValidationException(\Illuminate\Support\Facades\Validator::make([], []), 
                        \Illuminate\Validation\ValidationException::withMessages(['entries' => ["Daily limit exceeded for $entry[date]. You already have $currentDailyTotal hours logged, and the limit is $maxHours."]])
                    );
                }

                // Create
                $project = \App\Models\Project::find($entry['project_id']);
                
                Timesheet::create([
                    'employee_id' => $employee->id,
                    'date' => $entry['date'],
                    'project_id' => $project->id,
                    'project_name' => $project->name,
                    'task_id' => $entry['task_id'] ?? null,
                    'task_title' => $entry['task_title'] ?? null,
                    'task_description' => $entry['description'] ?? 'Weekly Log',
                    'hours_spent' => $entry['hours'],
                    'status' => 'Submitted' // Auto-submit for now to appear in Approvals
                ]);
                
                $savedCount++;
            }
        });

        return response()->json(['message' => "Successfully logged $savedCount entries."]);
    }
}
