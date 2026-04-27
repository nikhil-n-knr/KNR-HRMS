<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Timesheet;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\WorkflowService;
use App\Services\AI\AnomalyDetectionService;
use Illuminate\Validation\ValidationException;

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
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'nullable|exists:project_tasks,id',
            'task_description' => 'nullable|string|max:1000|required_without:task_id',
            'hours_spent' => 'required|numeric|min:0.1|max:24',
        ]);

        $employee = Auth::user()->employee;

        if (!$employee) {
            if ($request->wantsJson()) return response()->json(['message' => 'No Employee profile found.'], 404);
            return back()->with('error', 'No Employee profile found.');
        }

        if ($this->hasApprovedTimesheetForDate($employee->id, $request->date)) {
            $message = 'Timesheet is locked for this date because it has already been approved.';
            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }
            return back()->with('error', $message);
        }

        // Upsert by employee/date/project/task-slot to avoid duplicates on repeated save/edit.
        $description = trim((string) $request->input('task_description', ''));
        if (!$request->filled('task_id') && $description === '') {
            $description = 'Weekly Log';
        }

        $matchingEntry = $this->findMatchingEntry(
            $employee->id,
            $request->date,
            (int) $request->project_id,
            $request->input('task_id'),
            $description
        );

        // --- Policy Alignment: Max Hours Verification ---
        $existingHours = Timesheet::where('employee_id', $employee->id)
            ->where('date', $request->date)
            ->sum('hours_spent');

        $effectiveExistingHours = $existingHours;
        if ($matchingEntry) {
            $effectiveExistingHours = max(0, (float) $existingHours - (float) $matchingEntry->hours_spent);
        }

        // Fetch Employee Policy
        $policy = $employee->effectiveAttendancePolicy; 
        $maxHours = 24; // Absolute hard limit
        
        if ($policy && !empty($policy->timesheet_policy)) {
             $maxHours = $policy->timesheet_policy['daily_max_hours'] ?? 24;
             
             // Check min hours? Usually min hours is a warning on submit, not on individual entry creation.
        }

        if (($effectiveExistingHours + (float) $request->hours_spent) > $maxHours) {
            $message = "Total hours for the day cannot exceed {$maxHours}. You have already logged {$existingHours} hours.";
            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }
            return back()->with('error', $message);
        }
        // ---------------------------------------------------

        $project = \App\Models\Project::find($request->project_id);

        $payload = [
            'date' => $request->date,
            'project_id' => $request->project_id,
            'project_name' => $project?->name,
            'task_id' => $request->input('task_id'),
            'task_title' => null,
            'task_description' => $description,
            'hours_spent' => $request->hours_spent,
        ];

        if ($matchingEntry) {
            if ($this->normalizeStatus($matchingEntry->status) === 'approved') {
                $message = 'Cannot edit this entry because the date has been approved.';
                if ($request->wantsJson()) {
                    return response()->json(['message' => $message], 422);
                }
                return back()->with('error', $message);
            }

            $matchingEntry->update($payload);
            $timesheet = $matchingEntry->fresh();
        } else {
            $timesheet = Timesheet::create(array_merge($payload, [
                'employee_id' => $employee->id,
                'status' => 'Draft',
            ]));
        }

        // Run AI Anomaly Detection immediately
        $detector = app(AnomalyDetectionService::class);
        $detector->analyzeTimesheet($timesheet);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $matchingEntry ? 'Timesheet entry updated.' : 'Timesheet entry added.',
                'timesheet' => $timesheet
            ], $matchingEntry ? 200 : 201);
        }

        return back()->with('success', $matchingEntry
            ? 'Timesheet entry updated. Please submit it when ready.'
            : 'Timesheet entry added. Please submit it when ready.');
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

        // Approved entries are immutable.
        if ($this->normalizeStatus($timesheet->status) === 'approved') {
            if ($request->wantsJson()) return response()->json(['message' => 'Cannot edit approved timesheets.'], 403);
            return back()->with('error', 'Cannot edit approved timesheets.');
        }

        $request->validate([
            'date' => 'nullable|date',
            'project_id' => 'nullable|exists:projects,id',
            'task_id' => 'nullable|exists:project_tasks,id',
            'project_name' => 'nullable|string|max:255',
            'task_description' => 'nullable|string|max:1000|required_without:task_id',
            'hours_spent' => 'required|numeric|min:0.1|max:24',
        ]);

        $targetDate = $request->input('date', $timesheet->date?->toDateString() ?? $timesheet->date);
        if ($this->hasApprovedTimesheetForDate($timesheet->employee_id, $targetDate, $timesheet->id)) {
            $message = 'Timesheet is locked for this date because it has already been approved.';
            if ($request->wantsJson()) return response()->json(['message' => $message], 422);
            return back()->with('error', $message);
        }

        $policy = Auth::user()->employee?->effectiveAttendancePolicy;
        $maxHours = 24;
        if ($policy && !empty($policy->timesheet_policy)) {
            $maxHours = $policy->timesheet_policy['daily_max_hours'] ?? 24;
        }

        $existingHours = Timesheet::where('employee_id', $timesheet->employee_id)
            ->whereDate('date', $targetDate)
            ->where('id', '!=', $timesheet->id)
            ->sum('hours_spent');

        if (((float) $existingHours + (float) $request->hours_spent) > $maxHours) {
            $message = "Total hours for the day cannot exceed {$maxHours}. You already have {$existingHours} hours logged.";
            if ($request->wantsJson()) return response()->json(['message' => $message], 422);
            return back()->with('error', $message);
        }

        $description = trim((string) $request->input('task_description', $timesheet->task_description));
        if ($request->filled('task_id') && $description === '') {
            $description = $timesheet->task_description ?: 'Weekly Log';
        }

        $timesheet->update([
            'date' => $targetDate,
            'project_id' => $request->input('project_id', $timesheet->project_id),
            'project_name' => $request->input('project_name', $timesheet->project_name),
            'task_id' => $request->input('task_id', $timesheet->task_id),
            'task_description' => $description,
            'hours_spent' => $request->hours_spent,
        ]);

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
            ->with(['project:id,name,code', 'task:id,title'])
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
            'entries.*.task_title' => 'nullable|string|max:255',
            'entries.*.description' => 'nullable|string|max:1000',
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

                if ($this->hasApprovedTimesheetForDate($employee->id, $entry['date'])) {
                    throw ValidationException::withMessages([
                        'entries' => ["Date {$entry['date']} is locked because it already has approved timesheet entries."]
                    ]);
                }

                $description = trim((string) ($entry['description'] ?? ''));
                if (empty($entry['task_id']) && $description === '') {
                    $description = trim((string) ($entry['task_title'] ?? 'Other Task'));
                }

                $existingEntry = $this->findMatchingEntry(
                    $employee->id,
                    $entry['date'],
                    (int) $entry['project_id'],
                    $entry['task_id'] ?? null,
                    $description
                );

                // Check Daily Limit (Aggregate per day)
                $currentDailyTotal = Timesheet::where('employee_id', $employee->id)
                    ->where('date', $entry['date'])
                    ->sum('hours_spent');

                $effectiveDailyTotal = $currentDailyTotal;
                if ($existingEntry) {
                    if ($this->normalizeStatus($existingEntry->status) === 'approved') {
                        throw ValidationException::withMessages([
                            'entries' => ["Cannot edit approved entry on {$entry['date']}."]
                        ]);
                    }
                    $effectiveDailyTotal = max(0, (float) $currentDailyTotal - (float) $existingEntry->hours_spent);
                }
                
                // Fetch Employee Policy
                $policy = $employee->effectiveAttendancePolicy; 
                $maxHours = 24; 
                if ($policy && !empty($policy->timesheet_policy)) {
                        $maxHours = $policy->timesheet_policy['daily_max_hours'] ?? 24;
                }

                if (($effectiveDailyTotal + (float) $entry['hours']) > $maxHours) {
                     // Better exception
                     throw new \Illuminate\Validation\ValidationException(\Illuminate\Support\Facades\Validator::make([], []), 
                        \Illuminate\Validation\ValidationException::withMessages(['entries' => ["Daily limit exceeded for $entry[date]. You already have $currentDailyTotal hours logged, and the limit is $maxHours."]])
                    );
                }

                $project = \App\Models\Project::find($entry['project_id']);

                if ($existingEntry) {
                    $existingEntry->update([
                        'project_id' => $project?->id,
                        'project_name' => $project?->name,
                        'task_id' => $entry['task_id'] ?? null,
                        'task_title' => $entry['task_title'] ?? null,
                        'task_description' => $description !== '' ? $description : 'Weekly Log',
                        'hours_spent' => $entry['hours'],
                    ]);
                } else {
                    $timesheet = Timesheet::create([
                        'employee_id' => $employee->id,
                        'date' => $entry['date'],
                        'project_id' => $project?->id,
                        'project_name' => $project?->name,
                        'task_id' => $entry['task_id'] ?? null,
                        'task_title' => $entry['task_title'] ?? null,
                        'task_description' => $description !== '' ? $description : 'Weekly Log',
                        'hours_spent' => $entry['hours'],
                        'status' => 'Submitted'
                    ]);

                    $workflow = app(\App\Services\WorkflowService::class);
                    $instance = $workflow->initializeWorkflow('timesheet', $timesheet->id, Auth::user());
                    if (!$instance) {
                        $timesheet->update(['status' => 'Approved']);
                    }
                }
                
                $savedCount++;
            }
        });

        return response()->json(['message' => "Successfully logged $savedCount entries."]);
    }

    private function hasApprovedTimesheetForDate(int $employeeId, string $date, ?int $exceptId = null): bool
    {
        $query = Timesheet::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->whereRaw('LOWER(status) = ?', ['approved']);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    private function findMatchingEntry(int $employeeId, string $date, int $projectId, $taskId = null, string $description = ''): ?Timesheet
    {
        $query = Timesheet::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->where('project_id', $projectId);

        if (!empty($taskId)) {
            $query->where('task_id', $taskId);
        } else {
            $query->whereNull('task_id')
                ->where('task_description', $description !== '' ? $description : 'Weekly Log');
        }

        return $query->orderByDesc('id')->first();
    }

    private function normalizeStatus(?string $status): string
    {
        return strtolower(trim((string) $status));
    }
}
