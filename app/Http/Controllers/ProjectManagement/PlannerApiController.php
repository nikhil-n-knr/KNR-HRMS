<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\WorkAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Project\PlanOverwrittenNotification;
use App\Models\Timesheet;
use App\Traits\ProjectGovernanceTrait;

class PlannerApiController extends Controller
{
    use ProjectGovernanceTrait;

    protected $logger;

    public function __construct(\App\Services\Infrastructure\LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get all data required for the Planner init (Projects, Tasks, Resources).
     * optimized for a single heavy payload instead of multiple requests.
     */
    public function loadData(Request $request)
    {
        try {
            $projectId = $request->input('project'); // Optional filter

            // 1. Projects (visible to user)
            $query = Project::visibleTo($request->user())
                ->with(['client', 'stages', 'modules' => fn($q) => $q->whereNull('parent_id')->with('childrenRecursive')])
                ->whereIn('status', ['active', 'planning']);

            if ($projectId) {
                $query->where('id', $projectId);
            }

            $projects = $query->get()
                ->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'text' => $p->name, // Gantt standard
                        'name' => $p->name, // Explicit name for Header
                        'code' => $p->code ?? 'PROJ-' . $p->id,
                        'client' => $p->client ? ['name' => $p->client->name] : null,
                        'start_date' => $p->start_date ? $p->start_date->format('Y-m-d') : null,
                        'duration' => $p->start_date && $p->deadline ? $p->start_date->diffInDays($p->deadline) : 0,
                        'type' => 'project',
                        'client_name' => $p->client?->name,
                        'open' => true,
                        'stages' => $p->stages->map(function($s) {
                             return ['id' => $s->id, 'name' => $s->name];
                        }),
                        'modules' => $p->modules,
                        'is_locked' => (bool) $p->is_locked,
                        'plan_lock_recipients' => $p->plan_lock_recipients
                    ];
                });

            $projectIds = $projects->pluck('id')->toArray();

            // 2. Tasks (for these projects)
            $tasks = Task::whereIn('project_id', $projectIds)
                ->with(['assignments.assignee', 'bugTicket', 'stage'])
                ->withSum(['timesheets as actual_hours' => function($q) {
                    $q->whereIn('status', ['Approved', 'approved']);
                }], 'hours_spent')
                ->get()
                ->map(function ($t) {
                    $assignees = $t->assignments->map(function ($a) {
                        // Resolve User ID/Name from Employee or User
                        $userId = null;
                        $userName = 'Unknown';
                        $avatar = null;

                        if ($a->assignee_type === \App\Models\Employee::class && $a->assignee) {
                            $userId = $a->assignee->user_id;
                            $userName = $a->assignee->first_name . ' ' . $a->assignee->last_name;
                            $avatar = $a->assignee->avatar;
                        } elseif ($a->assignee_type === User::class && $a->assignee) {
                            $userId = $a->assignee->id;
                            $userName = $a->assignee->name;
                            $avatar = $a->assignee->avatar;
                        }

                        return [
                            'id' => $userId ?? $a->assignee_id, // Fallback (This is User ID)
                            'assignment_id' => $a->id, // Actual Primary Key
                            'name' => $userName,
                            'avatar' => $avatar,
                            'allocated_hours' => (float) $a->allocated_hours, 
                            'start_date' => $a->start_date ? $a->start_date->format('Y-m-d') : null,
                            'end_date' => $a->end_date ? $a->end_date->format('Y-m-d') : null,
                            'force_allocation' => (bool) $a->force_allocation,
                        ];
                    });

                    return [
                        'id' => $t->id,
                        'text' => $t->title,
                        'title' => $t->title, // Duplicate for modal
                        'start_date' => $t->start_date ? (is_string($t->start_date) ? \Carbon\Carbon::parse($t->start_date)->format('Y-m-d') : $t->start_date->format('Y-m-d')) : null,
                        'due_date' => $t->due_date ? (is_string($t->due_date) ? \Carbon\Carbon::parse($t->due_date)->format('Y-m-d') : $t->due_date->format('Y-m-d')) : null,
                        'duration' => $t->estimated_hours / 8, 
                        'estimated_hours' => (float) $t->estimated_hours,
                        'actual_hours' => (float) ($t->actual_hours ?? 0), 
                        'parent' => $t->project_id, 
                        'project_id' => $t->project_id, // Explicit for modal
                        'assignments' => $assignees,
                        'priority' => $t->priority,
                        'status' => $t->status,
                        'stage_id' => $t->stage_id,
                        'stage_name' => $t->stage?->name ?? $t->status,
                        'blocked_by_task_id' => $t->blocked_by_task_id,
                        'progress' => $t->status === 'Done' ? 1 : 0,
                        'is_bug' => $t->bugTicket ? true : false,
                        'bug_severity' => $t->bugTicket ? $t->bugTicket->severity : null,
                        'is_locked' => (bool) $t->is_locked,
                        'total_efforts' => (float) $t->total_efforts,
                        'baseline_start_date' => $t->baseline_start_date ? $t->baseline_start_date->format('Y-m-d') : null,
                        'baseline_due_date' => $t->baseline_due_date ? $t->baseline_due_date->format('Y-m-d') : null,
                    ];
                });

            // 3. Resources (Users with Employee Profile)
            $resources = \App\Models\Employee::with('user', 'department')
                ->where('status', 'active')
                ->get()
                ->map(function ($e) {
                    return [
                        'id' => $e->user_id, // Map to User ID for assignments
                        'employee_id' => $e->id,
                        'name' => $e->first_name . ' ' . $e->last_name,
                        'department' => $e->department ? $e->department->name : 'General',
                        'avatar' => $e->avatar ?? null
                    ];
                });

            // Ensure Current User is in Resources (even if status is not active, for Admins)
            $currentUser = $request->user();
            if ($currentUser && $currentUser->employee && !$resources->contains('id', $currentUser->id)) {
                 $resources->push([
                    'id' => $currentUser->id,
                    'employee_id' => $currentUser->employee->id,
                    'name' => $currentUser->name . ' (Me)',
                    'department' => $currentUser->employee->department ? $currentUser->employee->department->name : 'General',
                    'avatar' => $currentUser->avatar ?? null
                 ]);
            }
            
            // Teams (For Document Sharing)
            $teams = \App\Models\Team::select('id', 'name')->get();

            // 5. Global Actuals (Approved Timesheets)
            // Fetch ALL approved timesheets for visible resources (Global Load)
            $resourceEmployeeIds = $resources->pluck('employee_id')->filter()->toArray();
            $tsStart = now()->subMonths(3);
            $tsEnd = now()->addMonths(6);

            $timesheetData = [];
            $timesheets = \App\Models\Timesheet::whereIn('employee_id', $resourceEmployeeIds)
                 ->whereIn('status', ['Approved', 'approved']) 
                 ->whereBetween('date', [$tsStart, $tsEnd])
                 ->selectRaw('employee_id, date, SUM(hours_spent) as total_hours')
                 ->groupBy('employee_id', 'date')
                 ->with('employee:id,user_id')
                 ->get();

            foreach ($timesheets as $ts) {
                if ($ts->employee && $ts->employee->user_id) {
                    $uid = $ts->employee->user_id;
                    $date = Carbon::parse($ts->date)->format('Y-m-d');
                    if (!isset($timesheetData[$uid])) $timesheetData[$uid] = [];
                    $timesheetData[$uid][$date] = (float) $ts->total_hours;
                }
            }

            // 4. Availability (Leaves & Holidays)
            $startRange = now()->subMonth();
            $endRange = now()->addMonths(6);
            
            // Map User ID -> Availability
            $availability = [];

            // A. Leaves (Linked via Employee ID)
            $leaves = \App\Models\LeaveRequest::whereIn('status', ['approved', 'pending'])
                ->where('end_date', '>=', $startRange)
                ->where('start_date', '<=', $endRange)
                ->get();

            foreach ($leaves as $leave) {
                // Find User ID from Employee ID
                $user = $resources->firstWhere('employee_id', $leave->employee_id);
                if (!$user) continue;

                $period = \Carbon\CarbonPeriod::create($leave->start_date, $leave->end_date);
                foreach ($period as $date) {
                    $d = $date->format('Y-m-d');
                    $availability[$user['id']][$d] = [
                        'type' => 'leave',
                        'status' => $leave->status, // approved | pending
                        'name' => 'Leave: ' . $leave->reason
                    ];
                }
            }

            // B. Floating Holidays (Linked via User ID)
            $floating = \App\Models\FloatingHolidayRequest::where('status', 'approved')
                ->with('holiday')
                ->get(); // Assuming small dataset, otherwise filter by holiday date

            foreach ($floating as $fh) {
                if (!$fh->holiday) continue;
                $d = $fh->holiday->date->format('Y-m-d');
                $availability[$fh->user_id][$d] = [
                    'type' => 'holiday',
                    'status' => 'approved',
                    'name' => $fh->holiday->name
                ];
            }

            // 5. Global Holidays & Shift Config
            $holidays = \App\Models\Holiday::whereYear('date', '>=', now()->subMonths(6)->year) 
                ->get(['date', 'name', 'type'])
                ->map(function($h) {
                    return [
                        'date' => $h->date->format('Y-m-d'),
                        'name' => $h->name,
                        'type' => $h->type ?? 'fixed'
                    ];
                });

            $defaultShift = \App\Models\Shift::where('is_default', true)->first();
            $workDays = $defaultShift ? $defaultShift->work_days : [
                'mon'=>true, 'tue'=>true, 'wed'=>true, 'thu'=>true, 'fri'=>true, 
                'sat'=>false, 'sun'=>false
            ];

            return response()->json([
                'success' => true,
                'data' => $tasks->merge($projects),
                'resources' => $resources,
                'teams' => $teams, 
                'timesheets' => $timesheetData, // Global Actuals
                'availability' => $availability,
                'holidays' => $holidays,
                'workDays' => $workDays,
                'links' => [] 
            ]);
        } catch (\Exception $e) {
            $this->logger->log('project_management', 'planner_load_error', "Failed to load planner data: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    /**
     * Store new Task (from Planner)
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'project_id' => 'required|exists:projects,id',
                'stage_id' => 'nullable|exists:project_stages,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'priority' => 'nullable|in:Low,Medium,High,Critical',
                'assignees' => 'nullable|array',
                'assignees.*' => 'exists:users,id'
            ]);

            $task = new Task();
            $task->title = $validated['title'];
            $task->project_id = $validated['project_id'];
            $task->start_date = $validated['start_date'] ? Carbon::parse($validated['start_date']) : null;
            $task->priority = $validated['priority'] ?? 'Medium';
            $task->status = 'To Do';
            
            // Assign default stage (Dynamic Board Support)
            $defaultStage = \App\Models\ProjectStage::where('project_id', $validated['project_id'])
                ->where('type', 'todo')
                ->orderBy('order')
                ->first();
                
            // Assign stage
            if ($request->stage_id) {
                $task->stage_id = $request->stage_id;
            } elseif ($defaultStage) {
                $task->stage_id = $defaultStage->id;
            } else {
                $stage = \App\Models\ProjectStage::create([
                    'project_id' => $validated['project_id'],
                    'name' => 'To Do',
                    'slug' => 'to-do',
                    'type' => 'todo',
                    'color' => '#64748b',
                    'order' => 0
                ]);
                $task->stage_id = $stage->id;
            }

            $task->created_by = auth()->id();

            // Calculate duration/due_date
            if ($validated['start_date'] && $validated['end_date']) {
                $start = Carbon::parse($validated['start_date']);
                $end = Carbon::parse($validated['end_date']);
                $task->due_date = $end;
                $days = $start->diffInDays($end) + 1;
                $task->estimated_hours = $days * 8;
            }

            $task->save();

            // Handle Assignment (Multiple)
            if (!empty($validated['assignees'])) {
                $employees = \App\Models\Employee::whereIn('user_id', $validated['assignees'])->get();
                foreach ($employees as $employee) {
                    WorkAssignment::create([
                        'task_id' => $task->id,
                        'assignee_id' => $employee->id,
                        'assignee_type' => \App\Models\Employee::class,
                        'project_id' => $task->project_id,
                        'start_date' => $task->start_date,
                        'end_date' => $task->due_date
                    ]);
                }
            }

            $this->logger->log('project_management', 'task_create', "Task created via Planner", ['task_id' => $task->id]);

            return response()->json(['status' => 'ok', 'task' => $task]);

        } catch (\Exception $e) {
            $this->logger->log('project_management', 'task_create_error', "Failed to create task: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update Task (General Update)
     */
    public function update(Request $request, $id)
    {
        try {
            $task = Task::findOrFail($id);
            
            // Validate broadly
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'start_date' => 'sometimes|nullable|date',
                'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',
                'priority' => 'sometimes|in:Low,Medium,High,Critical',
                'status' => 'sometimes|string',
                'stage_id' => 'sometimes|nullable|exists:project_stages,id',
                'assignees' => 'sometimes|nullable|array',
                'assignees.*' => 'exists:users,id'
            ]);

            if (isset($validated['title'])) $task->title = $validated['title'];
            if (isset($validated['priority'])) $task->priority = $validated['priority'];
            if (isset($validated['status'])) $task->status = $validated['status'];
            if (isset($validated['stage_id'])) {
                $task->stage_id = $validated['stage_id'];
                // Sync status if it's a known stage
                $stage = \App\Models\ProjectStage::find($validated['stage_id']);
                if ($stage) {
                    $task->status = $stage->name;
                }
            }
            
            // Date Logic
            if (isset($validated['start_date'])) {
                $task->start_date = Carbon::parse($validated['start_date']);
            }
            if (isset($validated['end_date'])) {
                $oldDue = $task->due_date ? $task->due_date->format('Y-m-d') : null;
                $task->due_date = Carbon::parse($validated['end_date']);
                
                // Track changes for notification
                $changes = [];
                if ($oldDue !== $task->due_date->format('Y-m-d')) {
                    $changes['due_date'] = ['old' => $oldDue, 'new' => $task->due_date->format('Y-m-d')];
                }

                // Check lock
                $this->checkLockAndNotify($task->project, $task, $changes);

                 // Update Estimated Hours
                 if ($task->start_date && $task->due_date) {
                    $days = $task->start_date->diffInDays($task->due_date) + 1;
                    $task->estimated_hours = $days * 8;
                 }
            }

            $task->save();

            // Re-assign if provided (Full Sync)
            if (array_key_exists('assignees', $validated)) {
                $task->assignments()->delete();
                
                if (!empty($validated['assignees'])) {
                    $employees = \App\Models\Employee::whereIn('user_id', $validated['assignees'])->get();
                    foreach ($employees as $employee) {
                        WorkAssignment::create([
                            'task_id' => $task->id,
                            'assignee_id' => $employee->id,
                            'assignee_type' => \App\Models\Employee::class,
                            'project_id' => $task->project_id,
                            'start_date' => $task->start_date,
                            'end_date' => $task->due_date
                        ]);
                    }
                }
            }

            $this->logger->log('project_management', 'task_update', "Task updated via Planner", ['task_id' => $task->id]);
            
            return response()->json(['status' => 'ok', 'task' => $task]);

        } catch (\Exception $e) {
            $this->logger->log('project_management', 'task_update_error', "Failed to update task {$id}: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete Task
     */
    public function destroy($id)
    {
        try {
            $task = Task::findOrFail($id);
            $task->delete(); // Soft delete

            $this->logger->log('project_management', 'task_delete', "Task deleted via Planner", ['task_id' => $id]);
            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            $this->logger->log('project_management', 'task_delete_error', "Failed to delete task {$id}: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update Task Dates (Drag & Drop in Gantt)
     */
    public function moveTask(Request $request, $id)
    {
        try {
            $task = Task::findOrFail($id);
            
            $request->validate([
                'start_date' => 'required|date',
                'duration' => 'required|numeric'
            ]);

            $oldStart = $task->start_date ? $task->start_date->format('Y-m-d') : null;
            $task->start_date = Carbon::parse($request->start_date);
            
            // Calculate end date based on duration
            if ($request->duration > 0) {
                 $daysToAdd = max(0, ceil($request->duration) - 1);
                 $task->due_date = $task->start_date->copy()->addDays($daysToAdd);
                 $task->estimated_hours = $request->duration * 8;
            }

            // Check lock
            if ($oldStart !== $request->start_date) {
                $this->checkLockAndNotify($task->project, $task, [
                    'start_date' => ['old' => $oldStart, 'new' => $request->start_date],
                    'duration' => ['old' => '-', 'new' => $request->duration]
                ]);
            }

            $task->save();

            $this->logger->log('project_management', 'task_reschedule', "Task moved: {$task->id} to {$request->start_date}");

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
             $this->logger->log('project_management', 'task_move_error', "Failed to move task {$id}: " . $e->getMessage());
             return response()->json(['error' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Assign User to Task (Drag from sidebar)
     */
    /**
     * Assign User(s) to Task (Sync Mode)
     */
    public function assign(Request $request)
    {
        try {
            $request->validate([
                'task_id' => 'required|exists:project_tasks,id',
                'user_id' => 'sometimes|required_without:user_ids',
                'user_ids' => 'sometimes|array',
                'user_ids.*' => 'exists:users,id',
                'hours' => 'nullable|numeric|min:0|max:24',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date'
            ]);

            $task = Task::findOrFail($request->task_id);
            
            // Check lock
            $this->checkLockAndNotify($task->project, $task, ['action' => 'Assignment Update']);

            // Normalize input to array
            $userIds = $request->input('user_ids', []);
            if ($request->has('user_id')) {
                $userIds[] = $request->input('user_id');
            }
            $userIds = array_unique($userIds);

            // Strategy: 'replace' (Sync all) or 'merge' (Update specific / Add new) or 'delete'
            $strategy = $request->input('strategy', 'replace');
            $assignmentId = $request->input('assignment_id');

            // 1. Delete (Specific Segment)
            if ($strategy === 'delete') {
                if ($assignmentId) {
                    WorkAssignment::destroy($assignmentId);
                    return response()->json(['status' => 'ok', 'action' => 'deleted_segment']);
                }
                // If checking for bulk delete assignment only?
                // For now, assume delete strategy with ID is for segment.
            }

            // 2. Specific Update (Edit one segment)
            if ($assignmentId && $strategy === 'merge') {
                $assignment = WorkAssignment::findOrFail($assignmentId);
                $assignment->update([
                    'allocated_hours' => $request->input('hours', 8),
                    'start_date' => $request->input('start_date', $task->start_date),
                    'end_date' => $request->input('end_date', $task->due_date),
                    'force_allocation' => $request->boolean('force_allocation')
                ]);
                return response()->json(['status' => 'ok', 'action' => 'updated_segment']);
            }

            // Optional: Update Main Task Dates (Used in "Edit All" / "Replace")
            if ($request->boolean('update_task_dates')) {
                $task->update([
                    'start_date' => $request->input('start_date'),
                    'due_date' => $request->input('end_date')
                ]);
            }

            // 3. Bulk Replace (Delete all assignments first)
            if ($strategy === 'replace') {
                WorkAssignment::where('task_id', $task->id)->delete();
            }

            // 4. Create (Merge New / Replace Create)
            $employees = \App\Models\Employee::whereIn('user_id', $userIds)->get();

            foreach ($employees as $employee) {
                WorkAssignment::create([
                    'task_id' => $task->id,
                    'assignee_id' => $employee->id,
                    'assignee_type' => \App\Models\Employee::class,
                    'project_id' => $task->project_id,
                    'allocated_hours' => $request->input('hours', 8),
                    'start_date' => $request->input('start_date', $task->start_date),
                    'end_date' => $request->input('end_date', $task->due_date),
                    'force_allocation' => $request->boolean('force_allocation')
                ]);
            }
            
            $this->logger->log('project_management', 'task_assign', "Synced assignments for Task {$task->id}", ['count' => count($employees)]);

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            $this->logger->log('project_management', 'task_assign_failed', "Assignment failed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()], 'error');
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reports Data Endpoint (Advanced)
     */
    public function getReports(Request $request) 
    {
        $start = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
        $end = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now()->endOfMonth();
        $pid = $request->input('project_id');

        // 1. Fetch Assignments in Range
        $query = WorkAssignment::with(['project', 'task', 'assignee'])
            ->where(function($q) use ($start, $end) {
                $q->where('start_date', '<=', $end)
                  ->where('end_date', '>=', $start);
            });

        if ($pid) {
            $query->where('project_id', $pid);
        }

        $assignments = $query->get();
        $holidays = \App\Models\Holiday::whereBetween('date', [$start, $end])->get()->keyBy('date');
        
        // 2. Fetch Scope (Budget & Points) - Separate Query needed for "Total Project Scope" regardless of assignment dates?
        // Usually reports show "Active Scope". I'll use the tasks associated with assignments for granular accuracy, 
        // OR fetch all tasks if a specific Project is selected. 
        // For accurate "Budget vs Actual", we need Project Total.
        $totalScopeHours = 0;
        $totalScopePoints = 0;

        if ($pid) {
            $projectStats = Task::where('project_id', $pid)->selectRaw('SUM(estimated_hours) as hours, SUM(scrum_points) as points')->first();
            $totalScopeHours = $projectStats->hours ?? 0;
            $totalScopePoints = $projectStats->points ?? 0;
        } else {
            // If report is "All Projects", summing all tasks might be heavy. 
            // We'll sum based on the *assignments* found, which proxies "Active Scope".
            // Alternatively, can sum unique tasks from assignments.
            $uniqueTaskIds = $assignments->pluck('task_id')->unique();
            $scopeStats = Task::whereIn('id', $uniqueTaskIds)->selectRaw('SUM(estimated_hours) as hours, SUM(scrum_points) as points')->first();
            $totalScopeHours = $scopeStats->hours ?? 0;
            $totalScopePoints = $scopeStats->points ?? 0;
        }

        $reportData = [];
        $stats = [
            'total_hours' => 0,
            'holiday_hours' => 0,
            'resource_count' => 0,
            'avg_daily' => 0,
            'total_scope' => (float)$totalScopeHours,
            'total_points' => (int)$totalScopePoints,
            'earned_points' => 0, // Points from completed tasks
            'remaining_hours' => 0
        ];
        $charts = [
            'projects' => [],
            'employees' => [],
            'points' => [] // Leaderboard
        ];
        
        $uniqueResources = [];

        foreach ($assignments as $a) {
            $s = $a->start_date < $start ? $start->copy() : $a->start_date->copy();
            $e = $a->end_date > $end ? $end->copy() : $a->end_date->copy();
            
            $regularHours = 0;
            $holidayHours = 0;
            
            $curr = $s->copy();
            while ($curr <= $e) {
                // Carbon isWeekend() is Saturday/Sunday. 
                // Need to use workDays config in real app, but defaulting to Sat/Sun here + holidays
                $isWeekend = $curr->isWeekend(); 
                $dateStr = $curr->format('Y-m-d');
                $isHoliday = isset($holidays[$dateStr]);
                
                if ($isWeekend || $isHoliday) {
                    if ($a->force_allocation) {
                        $holidayHours += $a->allocated_hours;
                    }
                } else {
                    $regularHours += $a->allocated_hours;
                }
                $curr->addDay();
            }

            $total = $regularHours + $holidayHours;
            if ($total == 0) continue; 

            $stats['total_hours'] += $total;
            $stats['holiday_hours'] += $holidayHours;
            // Normalize Key to User ID for accurate "People" count
            $userId = null;
            if ($a->assignee_type === \App\Models\Employee::class && $a->assignee) {
                $userId = $a->assignee->user_id;
            } elseif ($a->assignee_type === \App\Models\User::class) {
                $userId = $a->assignee_id;
            }
            
            if ($userId) {
                $uniqueResources[$userId] = true;
            } else {
                 // Fallback
                $uniqueResources['raw_' . $a->assignee_id] = true;
            }

            // Gamification Logic
            $taskPoints = $a->task ? ($a->task->scrum_points ?? 0) : 0;
            // Contribution Score:  (Hours Contributed / Task Est Hours) * Points? 
            // Simple approach: Assignee gets credit for points if they are assigned.
            // If multiple assignees, points shared? Keeping it simple: Full points attribution for leaderboard heat.
            
            // Stats Update
            if ($a->task && $a->task->status === 'done') {
                // Approximate: If task is done, points are "Earned". 
                // We add unique task points to global stats. 
                // But simplified: Just sum points of tasks worked on in this period for "Velocity".
            }

            // Charts Calculation
            $pName = $a->project ? $a->project->name : 'Unknown';
            $charts['projects'][$pName] = ($charts['projects'][$pName] ?? 0) + $total;

            $eName = $a->assignee ? $a->assignee->name : 'Unknown';
            $charts['employees'][$eName] = ($charts['employees'][$eName] ?? 0) + $total;
            
            // Points Leaderboard (Sum of points of tasks assigned)
            $charts['points'][$eName] = ($charts['points'][$eName] ?? 0) + $taskPoints;

            // Table Row
            $reportData[] = [
                'id' => $a->id,
                'project' => $pName,
                'task' => $a->task ? $a->task->text : 'N/A',
                'task_status' => $a->task ? $a->task->status : 'todo',
                'points' => $taskPoints,
                'estimated_hours' => $a->task ? $a->task->estimated_hours : 0,
                'employee_name' => $eName,
                'employee_initials' => $a->assignee ? substr($a->assignee->name, 0, 2) : '??',
                'avatar' => $a->assignee ? $a->assignee->avatar : null,
                'start_date' => $s->format('Y-m-d'),
                'end_date' => $e->format('Y-m-d'),
                'hours' => $total,
                'holiday_hours' => $holidayHours,
                'force_allocation' => $a->force_allocation
            ];
        }

        $stats['resource_count'] = count($uniqueResources);
        $days = $start->diffInDays($end) + 1;
        $stats['avg_daily'] = $days > 0 ? round($stats['total_hours'] / $days, 1) : 0;
        $stats['remaining_hours'] = max(0, $stats['total_scope'] - $stats['total_hours']); // Burn metric

        return response()->json([
            'success' => true,
            'data' => $reportData,
            'stats' => $stats,
            'charts' => $charts
        ]);
    }

    public function exportReports(Request $request)
    {
        $start = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
        $end = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now()->endOfMonth();
        $pid = $request->input('project_id');

        $query = WorkAssignment::with(['project', 'task', 'assignee'])
            ->where(function($q) use ($start, $end) {
                // Overlap check
                $q->where('start_date', '<=', $end)
                  ->where('end_date', '>=', $start);
            });

        if ($pid) {
            $query->where('project_id', $pid);
        }

        $assignments = $query->get();
        $holidays = \App\Models\Holiday::whereBetween('date', [$start, $end])->get()->keyBy('date');
        
        $format = $request->input('format', 'csv');

        if ($format === 'excel') {
            // Aggregate high-level stats for the Overview Sheet
            $totalScopeHours = 0;
            $totalScopePoints = 0;
            if ($pid) {
                $projectStats = \App\Models\Task::where('project_id', $pid)->selectRaw('SUM(estimated_hours) as hours, SUM(scrum_points) as points')->first();
                $totalScopeHours = $projectStats->hours ?? 0;
                $totalScopePoints = $projectStats->points ?? 0;
            } else {
                $uniqueTaskIds = $assignments->pluck('task_id')->unique();
                $scopeStats = \App\Models\Task::whereIn('id', $uniqueTaskIds)->selectRaw('SUM(estimated_hours) as hours, SUM(scrum_points) as points')->first();
                $totalScopeHours = $scopeStats->hours ?? 0;
                $totalScopePoints = $scopeStats->points ?? 0;
            }

            // Quick scan for Holiday/Weekend hours and resource count
            $uniqueResources = [];
            $totalHours = 0;
            $holidayHours = 0;
            foreach ($assignments as $a) {
                $s = $a->start_date < $start ? $start->copy() : $a->start_date->copy();
                $e = $a->end_date > $end ? $end->copy() : $a->end_date->copy();
                $curr = $s->copy();
                while ($curr <= $e) {
                    $isWeekend = $curr->isWeekend(); 
                    $dateStr = $curr->format('Y-m-d');
                    $isHoliday = isset($holidays[$dateStr]);
                    if ($isWeekend || $isHoliday) {
                        if ($a->force_allocation) $holidayHours += $a->allocated_hours;
                    } else {
                        $totalHours += $a->allocated_hours;
                    }
                    $curr->addDay();
                }
                $userId = null;
                if ($a->assignee_type === \App\Models\Employee::class && $a->assignee) $userId = $a->assignee->user_id;
                elseif ($a->assignee_type === \App\Models\User::class) $userId = $a->assignee_id;
                if ($userId) $uniqueResources[$userId] = true;
            }
            $totalHours += $holidayHours;
            $days = $start->diffInDays($end) + 1;

            $stats = [
                'total_hours' => $totalHours,
                'holiday_hours' => $holidayHours,
                'resource_count' => count($uniqueResources),
                'avg_daily' => $days > 0 ? round($totalHours / $days, 1) : 0,
                'total_scope' => (float)$totalScopeHours,
                'total_points' => (int)$totalScopePoints,
                'remaining_hours' => max(0, $totalScopeHours - $totalHours)
            ];

            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\ProjectComprehensiveExport($assignments, $holidays, $start, $end, $stats, $pid),
                'Project_Comprehensive_Analytics_' . date('Y-m-d') . '.xlsx'
            );
        }

        // --- LEGACY CSV STREAM ---
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=Project_Analytics_Report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($assignments, $holidays, $start, $end) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Project', 'Task', 'Status', 'Employee', 'Start Date', 'End Date', 'Allocated Hours', 'Estimated Hours', 'Scrum Points', 'Holiday/Forced Hours', 'Type']); 
            foreach ($assignments as $a) {
                $s = $a->start_date < $start ? $start->copy() : $a->start_date->copy();
                $e = $a->end_date > $end ? $end->copy() : $a->end_date->copy();
                $regularHours = 0;
                $holidayHours = 0;
                $curr = $s->copy();
                while ($curr <= $e) {
                    $isWeekend = $curr->isWeekend(); 
                    $dateStr = $curr->format('Y-m-d');
                    $isHoliday = isset($holidays[$dateStr]);
                    if ($isWeekend || $isHoliday) {
                        if ($a->force_allocation) $holidayHours += $a->allocated_hours;
                    } else {
                        $regularHours += $a->allocated_hours;
                    }
                    $curr->addDay();
                }

                $total = $regularHours + $holidayHours;
                if ($total == 0) continue;

                fputcsv($file, [
                    $a->project ? $a->project->name : 'Unknown',
                    $a->task ? $a->task->title : 'N/A', // Swapped text to title for clarity
                    $a->task ? $a->task->status : 'N/A',
                    $a->assignee ? $a->assignee->name : 'Unknown',
                    $s->format('Y-m-d'),
                    $e->format('Y-m-d'),
                    $total,
                    $a->task ? $a->task->estimated_hours : 0,
                    $a->task ? $a->task->scrum_points : 0,
                    $holidayHours,
                    $holidayHours > 0 ? 'Includes Weekend/Holiday' : 'Standard'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Document Management Methods (CRUD + Access Control)
     */

    public function getShareables(Request $request)
    {
        // 1. Teams
        $teams = \App\Models\Team::select('id', 'name')->get();

        // 2. Users (Employees)
        $users = User::whereHas('employee')->select('id', 'name')->get();

        return response()->json([
             'teams' => $teams,
             'users' => $users
        ]);
    }

    public function getDocuments(Request $request)
    {
        try {
            $projectId = $request->input('project_id');
            if (!$projectId) return response()->json(['error' => 'Project ID required'], 400);

            $user = $request->user();
            
            // Logic:
            // 1. Admin/Manager see all
            // 2. Uploader sees their own
            // 3. 'public' -> Visible to everyone
            // 4. 'team' -> Visible if:
            //    - User is assigned to project (Matrix)
            //    - User is specifically shared_with
            //    - User's Team is in shared_with_teams
            // 5. 'private' -> Not handled here specifically, falls through to "shared" checks if not uploader.

            $query = \App\Models\ProjectDocument::where('project_id', $projectId)
                ->with(['uploader', 'module']);

            if (!$user->hasRole(['Admin', 'Manager'])) {
                
                // Determine membership
                $isProjectMember = WorkAssignment::where('project_id', $projectId)
                    ->whereHas('assignee', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })->exists();
                
                $userTeamId = $user->team_id;

                $query->where(function($q) use ($user, $isProjectMember, $userTeamId) {
                    // 1. Uploader always sees
                    $q->where('uploader_id', $user->id)
                      
                      // 2. Public
                      ->orWhere('visibility', 'public')
                      
                      // 3. Team / Shared
                      ->orWhere(function($sub) use ($user, $isProjectMember, $userTeamId) {
                          $sub->whereIn('visibility', ['team', 'private']) // 'private' only if shared explicitly
                              ->where(function($access) use ($user, $isProjectMember, $userTeamId) {
                                  
                                  // Explicit User Share
                                  $access->whereJsonContains('shared_with', (string)$user->id) // cast to string commonly for JSON arrays of strings/ints
                                         ->orWhereJsonContains('shared_with', $user->id); 
                                  
                                  // Explicit Team Share
                                  if ($userTeamId) {
                                      $access->orWhereJsonContains('shared_with_teams', (string)$userTeamId)
                                             ->orWhereJsonContains('shared_with_teams', $userTeamId);
                                  }

                                  // Auto Matrix Access (Only if visibility is NOT private)
                                  if ($isProjectMember) {
                                      // If visibility is 'team', all members allowed.
                                      // Query logic: IF (visibility = 'team') return true
                                      // We can't do IF inside SQL easily without Raw.
                                      // Let's rely on grouping:
                                      // OR (visibility = 'team')
                                      $access->orWhere('visibility', 'team');
                                  }
                              });
                      });
                });
            }

            // Filter by Module or Category if needed
            if ($request->input('module_id')) {
                $query->where('module_id', $request->input('module_id'));
            }
            if ($request->input('category')) {
                $query->where('category', $request->input('category'));
            }

            $docs = $query->orderBy('created_at', 'desc')->get()
                ->map(function($d) {
                    return [
                        'id' => $d->id,
                        'name' => $d->name,
                        'category' => $d->category,
                        'module' => $d->module ? $d->module->name : null,
                        'uploader' => $d->uploader->name,
                        'size' => $d->formatted_size,
                        'mime' => $d->mime_type,
                        'created_at' => $d->created_at->format('Y-m-d H:i'),
                        'visibility' => $d->visibility,
                        'url' => route('planner.documents.download', $d->id)
                    ];
                });

            return response()->json(['success' => true, 'data' => $docs]);

        } catch (\Exception $e) {
            $this->logger->log('documents', 'fetch_error', $e->getMessage());
            return response()->json(['error' => 'Failed to fetch documents'], 500);
        }
    }

    public function uploadDocument(Request $request)
    {
        try {
            $request->validate([
                'project_id' => 'required|exists:projects,id',
                'file' => 'required|file|max:10240', // 10MB
                'category' => 'required|string',
                'visibility' => 'required|in:public,team,private',
                'shared_with' => 'nullable|array',
                'shared_with_teams' => 'nullable|array'
            ]);

            $file = $request->file('file');
            $pid = $request->input('project_id');
            $category = $request->input('category', 'General');
            
            // Path: public/projects/{id}/{category}/
            $path = $file->storeAs(
                "public/projects/{$pid}/{$category}", 
                $file->getClientOriginalName()
            );

            // Create Record
            $doc = \App\Models\ProjectDocument::create([
                'project_id' => $pid,
                'uploader_id' => auth()->id(),
                'name' => $file->getClientOriginalName(),
                'category' => $category,
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'visibility' => $request->input('visibility'),
                'module_id' => $request->input('module_id'),
                'shared_with' => $request->input('shared_with'), // Array of User IDs
                'shared_with_teams' => $request->input('shared_with_teams') // Array of Team IDs
            ]);

            $this->logger->log('documents', 'upload_success', "File uploaded: {$doc->name}", ['doc_id' => $doc->id]);

            return response()->json(['success' => true, 'message' => 'File uploaded successfully']);

        } catch (\Exception $e) {
            $this->logger->log('documents', 'upload_error', $e->getMessage(), [], 'error');
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteDocument($id)
    {
        try {
            $doc = \App\Models\ProjectDocument::findOrFail($id);
            
            // ACL: Owner or Admin
            if (auth()->id() !== $doc->uploader_id && !auth()->user()->hasRole(['Admin', 'Manager'])) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Logic: Soft Delete DB, Keep File (or delete file if hard delete preferred)
            // Keeping file for safety as per standard enteprise logic
            $doc->delete();

            $this->logger->log('documents', 'delete_success', "File deleted: {$doc->name}", ['doc_id' => $id]);
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
             $this->logger->log('documents', 'delete_error', $e->getMessage(), [], 'error');
            return response()->json(['error' => 'Failed to delete'], 500);
        }
    }

    public function downloadDocument($id)
    {
        $doc = \App\Models\ProjectDocument::findOrFail($id);
        
        $user = auth()->user();
        
        // Comprehensive ACL Check
        if ($user->hasRole(['Admin', 'Manager'])) {
            // Allow
        } elseif ($doc->uploader_id === $user->id) {
            // Allow
        } elseif ($doc->visibility === 'public') {
            // Allow
        } else {
             // Team/Private Check
             $allowed = false;
             
             // 1. Explicit User Share
             $sharedUsers = $doc->shared_with ?? [];
             if (in_array($user->id, $sharedUsers)) $allowed = true;

             // 2. Explicit Team Share
             $sharedTeams = $doc->shared_with_teams ?? [];
             if ($user->team_id && in_array($user->team_id, $sharedTeams)) $allowed = true;

             // 3. Matrix Access (Team Visibility Only)
             if (!$allowed && $doc->visibility === 'team') {
                 $isMember = WorkAssignment::where('project_id', $doc->project_id)
                    ->whereHas('assignee', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })->exists();
                 if ($isMember) $allowed = true;
             }

             if (!$allowed) abort(403);
        }

        if (\Storage::exists($doc->file_path)) {
            return \Storage::download($doc->file_path, $doc->name);
        }
        
        return response()->json(['error' => 'File not found'], 404);
    }

    /**
     * Reports API
     * Returns stats, table data, and charts for Planner Reports view.
     */
    public function reports(Request $request)
    {
        try {
            $projectId = $request->input('project_id'); 
            $start = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : now()->startOfMonth();
            $end = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : now()->endOfMonth();

            // 1. All Projects for the user to filter
            $projectsList = Project::visibleTo($request->user())->get();
            $projectIds = $projectId ? [$projectId] : $projectsList->pluck('id')->toArray();

            // 2. Base Query for Timesheets (Actuals)
            $timesheetQuery = \App\Models\Timesheet::whereIn('status', ['Approved', 'approved']) 
                ->whereIn('project_id', $projectIds)
                ->whereBetween('date', [$start, $end]);

            $timesheets = $timesheetQuery->with(['employee.user', 'project', 'task'])->get();

            // 2. Base Query for Tasks (Scope/Plan)
            $taskQuery = \App\Models\Task::whereIn('project_id', $projectIds)
                ->whereNull('deleted_at'); 
            
            $tasks = $taskQuery->with(['assignees', 'project'])->get();

            // 3. Stats Calculation
            // Prioritize total_efforts (decoupled logic)
            $totalPlanned = (float) $tasks->sum(fn($t) => $t->total_efforts > 0 ? $t->total_efforts : $t->estimated_hours);
            $totalPoints = $tasks->sum('scrum_points') ?? 0;
            
            // Actuals
            $totalActual = $timesheets->sum('hours_spent');
            $remainingHours = max(0, $totalPlanned - $totalActual);

            // Resource Count
            $activeResourceIds = $timesheets->pluck('employee_id')->unique();
            $resourceCount = $activeResourceIds->count();

            // Avg Burn Rate (Daily)
            $daysDiff = $start->diffInDays($end) + 1;
            $avgDaily = $daysDiff > 0 ? round($totalActual / $daysDiff, 1) : 0;

            $stats = [
                'total_hours' => (float) $totalPlanned, 
                'total_scope' => (float) $totalPlanned, 
                'total_actual' => (float) $totalActual,
                'total_points' => (int) $totalPoints, 
                'remaining_hours' => (float) $remainingHours,
                'holiday_hours' => 0, 
                'resource_count' => $resourceCount,
                'avg_daily' => $avgDaily,
                'health_score' => $this->calculateHealthScore($totalActual, $totalPlanned),
                'forecast_finish' => $this->calculateForecastFinish($avgDaily, $remainingHours)
            ];

            // 4. Detailed Table (Actually loaded timesheets)
            // Identify Critical Path
            $criticalPathIds = $this->getCriticalPathIds($tasks);

            $tableData = $timesheets->map(function($t) use ($criticalPathIds) {
                return [
                    'id' => $t->id,
                    'project' => $t->project ? $t->project->name : 'Unknown',
                    'task' => $t->task ? $t->task->title : 'General/Timesheet',
                    'task_status' => $t->task ? strtolower($t->task->status) : 'n/a',
                    'is_critical' => $t->task ? in_array($t->task->id, $criticalPathIds) : false,
                    'employee_name' => $t->employee ? ($t->employee->first_name . ' ' . $t->employee->last_name) : 'Unknown',
                    'employee_initials' => $t->employee ? substr($t->employee->first_name, 0, 1) . substr($t->employee->last_name, 0, 1) : 'NA',
                    'avatar' => $t->employee ? $t->employee->avatar : null,
                    'start_date' => $t->date->format('Y-m-d'),
                    'end_date' => $t->date->format('Y-m-d'), 
                    'hours' => (float) $t->hours_spent,
                    'points' => $t->task ? $t->task->scrum_points : 0
                ];
            });

            // 5. Build Chart Datasets (Planned vs Actual)
            $projectsChart = [];
            foreach ($projectIds as $pid) {
                $pObj = \App\Models\Project::find($pid);
                if (!$pObj) continue;
                $projectsChart[$pObj->name] = [
                    'planned' => (float) $tasks->where('project_id', $pid)->sum('estimated_hours'),
                    'actual' => (float) $timesheets->where('project_id', $pid)->sum('hours_spent')
                ];
            }

            $employeesChart = [];
            // Map Actuals
            $empActuals = $timesheets->groupBy('employee_id');
            foreach ($empActuals as $eid => $records) {
                $e = $records->first()->employee;
                if (!$e) continue;
                $name = $e->first_name . ' ' . $e->last_name;
                if (!isset($employeesChart[$name])) $employeesChart[$name] = ['planned' => 0, 'actual' => 0];
                $employeesChart[$name]['actual'] = (float) $records->sum('hours_spent');
            }
            // Map Planned (Tasks assigned to them in current scope)
            foreach ($tasks as $task) {
                $count = $task->assignees->count();
                if ($count > 0) {
                    $split = $task->estimated_hours / $count;
                    foreach ($task->assignees as $assignee) {
                        $name = $assignee->name; 
                        if (!isset($employeesChart[$name])) $employeesChart[$name] = ['planned' => 0, 'actual' => 0];
                        $employeesChart[$name]['planned'] += $split;
                    }
                }
            }

            // 6. Points Leaderboard (Completed)
            $pointsChart = [];
            $doneTasks = $tasks->where('status', 'done'); // Status case safety
            if ($doneTasks->isEmpty()) $doneTasks = $tasks->where('status', 'Done');

            foreach ($doneTasks as $task) {
                $count = $task->assignees->count();
                if ($count > 0) {
                    $split = ($task->scrum_points ?? 0) / $count;
                    foreach ($task->assignees as $assignee) {
                        $name = $assignee->name;
                        if (!isset($pointsChart[$name])) $pointsChart[$name] = 0;
                        $pointsChart[$name] += $split;
                    }
                }
            }
            arsort($pointsChart);

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'data' => $tableData,
                'charts' => [
                    'projects' => $projectsChart,
                    'employees' => $employeesChart,
                    'points' => $pointsChart
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->log('project_management', 'reports_error', $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Extend Task (Handles overrun)
     */
    public function extendTask(Request $request, $id)
    {
        try {
            $task = Task::findOrFail($id);
            $project = $task->project;
            
            $request->validate([
                'new_due_date' => 'required|date|after:due_date'
            ]);

            $oldDue = $task->due_date->format('Y-m-d');
            $task->due_date = Carbon::parse($request->new_due_date);
            $task->save();

            // Check lock & notify if needed
            $this->checkLockAndNotify($project, $task, [
                'due_date' => ['old' => $oldDue, 'new' => $task->due_date->format('Y-m-d')],
                'action' => 'Task Extension'
            ]);

            return response()->json(['status' => 'ok', 'task' => $task]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Helpers (checkLockAndNotify & notifyStakeholders) removed, now using ProjectGovernanceTrait.

    /**
     * AI Health Logic & Critical Path
     */
    private function calculateHealthScore($actual, $planned)
    {
        if ($planned <= 0) return 'N/A';
        $ratio = $actual / $planned;
        if ($ratio > 1.1) return 'Critical (Overrun)';
        if ($ratio > 0.9) return 'Healthy';
        return 'Underutilized';
    }

    private function calculateForecastFinish($avgDaily, $remaining)
    {
        if ($avgDaily <= 0) return 'Unknown';
        $days = ceil($remaining / $avgDaily);
        return Carbon::now()->addDays($days)->format('Y-m-d');
    }

    private function getCriticalPathIds($tasks)
    {
        // Simple heuristic: Tasks with no successors (nothing blocks them) 
        // that have the latest due dates, and their dependencies.
        $ids = [];
        $latestTasks = $tasks->sortByDesc('due_date')->take(3);
        
        foreach ($latestTasks as $lt) {
            $ids[] = $lt->id;
            // Trace back blockers
            $curr = $lt;
            while ($curr && $curr->blocked_by_task_id) {
                $blocker = Task::find($curr->blocked_by_task_id);
                if ($blocker) {
                    $ids[] = $blocker->id;
                    $curr = $blocker;
                } else {
                    $curr = null;
                }
            }
        }
        return array_unique($ids);
    }
}
