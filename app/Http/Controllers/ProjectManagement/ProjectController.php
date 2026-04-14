<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    protected $logger;
    protected $assetService;

    public function __construct(
        \App\Services\Infrastructure\LoggerService $logger,
        \App\Services\Assets\AssetService $assetService
    )
    {
        $this->logger = $logger;
        $this->assetService = $assetService;
    }

    /**
     * Dashboard: The "God View" of all projects.
     */
    /**
     * Dashboard: The "God View" of all projects.
     */
    public function index(Request $request)
    {
        $viewMode = $request->input('view', 'active'); // 'active', 'archived', 'all'
        
        $query = Project::query();
        
        // Filter: Status based on View Mode
        if ($viewMode === 'archived') {
            $query->where('status', 'archived');
        } elseif ($viewMode === 'active') {
            $query->where('status', '!=', 'archived');
        }
        // 'all' shows everything
        
        // Visibility Scope
        if (!$request->user()->hasRole(['Super Admin', 'Admin'])) {
             $query->visibleTo($request->user(), true); 
        } else {
             // Admin seeing All (unless they filter by 'mine' which we could add later)
             $query->visibleTo($request->user(), false); 
        }

        $query->with(['client', 'assignments', 'tasks'])
             ->withCount('tasks');
            
        // 2. Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // 3. Pagination & Ordering
        $projects = $query->orderBy('id', 'desc')
                          ->paginate(10)
                          ->withQueryString();

        return Inertia::render('Project/Dashboard', [
            'projects' => $projects,
            'stats' => [
                'total' => Project::visibleTo($request->user())->count(),
                'active' => Project::visibleTo($request->user())->where('status', '!=', 'archived')->count(),
                'archived' => Project::visibleTo($request->user())->where('status', 'archived')->count(),
            ],
            'filters' => $request->only(['search', 'view'])
        ]);
    }
    
    public function archive(Project $project)
    {
        $project->update(['status' => 'archived']);
        return back()->with('success', 'Project archived.')->setStatusCode(303);
    }
    
    public function unarchive(Project $project)
    {
        $project->update(['status' => 'active']); // Or 'planning', but 'active' acts as restore
        return back()->with('success', 'Project unarchived.')->setStatusCode(303);
    }

    public function destroy(Project $project)
    {
        // Soft Delete (Trait handles it)
        $project->delete();
        return back()->with('success', 'Project moved to trash.')->setStatusCode(303);
    }

    // ... planner, index, show ...

    public function show(\App\Models\Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['client', 'sprints' => function($q) {
             $q->where('status', 'active');
        }, 'assignments.assignee']);

        // Members (unique)
        // Members (unique Users)
        // Members (Merge Board Assignees + Matrix Assignments)
        $matrixMembers = $project->assignments->map(function ($a) {
             if ($a->assignee_type === \App\Models\Employee::class && $a->assignee) {
                 return $a->assignee->user; 
             }
             if ($a->assignee_type === \App\Models\User::class && $a->assignee) {
                 return $a->assignee;
             }
             return null;
        })->filter();
        
        // Calculate Detailed Contributor Stats (For Matrix Users)
        // Calculate Detailed Contributor Stats (For Matrix Users)
        $today = now()->startOfDay();
        
        // 1. Get Assignees
        $assignments = $project->assignments;
        
        // 2. Get Time Loggers (who might not be assigned)
        $timeLoggerIds = \App\Models\Timesheet::where('project_id', $project->id)
            ->where('status', 'Approved') // or approved
            ->with(['employee.user'])
            ->get()
            ->pluck('employee.user.id')
            ->unique()
            ->filter();

        // 3. Merge Unique User IDs
        $allUserIds = $assignments->map(function($a) {
             return $a->assignee_type === \App\Models\Employee::class ? $a->assignee->user_id : $a->assignee_id;
        })->merge($timeLoggerIds)->unique();

        $contributors = $allUserIds->mapWithKeys(function($userId) use ($project, $assignments, $today) {
             // Find User & Avatar
             $userObj = \App\Models\User::find($userId);
             if (!$userObj) return [$userId => null];
             
             // Get User's Assignments
             $userAssignments = $assignments->filter(function($a) use ($userId) {
                 return ($a->assignee_type === \App\Models\Employee::class && $a->assignee->user_id === $userId) ||
                        ($a->assignee_type === \App\Models\User::class && $a->assignee_id === $userId);
             });
             
             return [$userId => $userAssignments];
        })->map(function($userAssignments, $userId) use ($today, $project) {
             if (!$userAssignments && $userId) {
                 // Fetch User for fallback (Time Logger Only)
                 $userObj = \App\Models\User::find($userId);
                 if (!$userObj) return null;
                 $avatar = $userObj->avatar;
             } else {
                 // Existing Logic for Avatar
                 if ($userAssignments->isNotEmpty()) {
                    $first = $userAssignments->first();
                    $userObj = ($first->assignee_type === \App\Models\Employee::class) ? $first->assignee->user : $first->assignee;
                    $avatar = $userObj->avatar ?? (($first->assignee_type === \App\Models\Employee::class) ? $first->assignee->avatar : null);
                 } else {
                    $userObj = \App\Models\User::find($userId);
                    $avatar = $userObj->avatar;
                 }
             }

             // Use $userAssignments (Collection) for calculations. If empty (Logger only), hours will be 0.
             $assignments = $userAssignments ?: collect([]);

             // ... (Rest of calculation)

             // 1. Plan to Date (Expected Effort till Yesterday)
             // User Request: "PROJECT START TILL TODAY DONT CONSIDER TODAY"
             $yesterday = $today->copy()->subDay();
             
             $planHours = $assignments->sum(function($a) use ($yesterday) {
                 if (!$a->start_date || !$a->end_date) return 0;
                 if ($a->start_date->gt($yesterday)) return 0; // Future assignment

                 // Cap at yesterday or assignment end, whichever is earlier
                 $effectiveEnd = $a->end_date->lt($yesterday) ? $a->end_date : $yesterday;
                 
                 $days = 0;
                 $curr = $a->start_date->copy();
                 while ($curr->lte($effectiveEnd)) {
                     if (!$curr->isWeekend()) $days++;
                     $curr->addDay();
                 }
                 return $days * $a->allocated_hours;
             });

             // 2. Actual (Invested from APPROVED Timesheets)
             // "ACTUAL WILL BE TAKEN FROM THE TIME SHEET HE HAS FILLED UP AFTER APPROVED"
             $investedHours = \App\Models\Timesheet::where('project_id', $project->id)
                 ->whereHas('employee', function($q) use ($userId) {
                     $q->where('user_id', $userId);
                 })
                 ->where('status', 'Approved')
                 ->sum('hours_spent');
             
             // Fallback: If no timesheets, check if we have task actuals? 
             // Task actuals are per Task, difficult to attribute to specific user if multiple assignees.
             // We will stick to Timesheets for "Invested" per person. 

             // 3. Pending (Future from Today)
             $pendingHours = $assignments->sum(function($a) use ($today) {
                 if (!$a->start_date || !$a->end_date || $a->end_date->lt($today)) return 0;
                 
                 $futureStart = $a->start_date->greaterThan($today) ? $a->start_date : $today;
                 $days = 0;
                 $curr = $futureStart->copy();
                 while ($curr->lte($a->end_date)) {
                     if (!$curr->isWeekend()) $days++;
                     $curr->addDay();
                 }
                 return $days * $a->allocated_hours;
             });

             // 4. Total Scope (Grand Total Assignment: Start -> End)
             $totalScopeHours = $assignments->sum(function($a) {
                 if (!$a->start_date || !$a->end_date) return 0;
                 $days = 0;
                 $curr = $a->start_date->copy();
                 while ($curr->lte($a->end_date)) {
                     if (!$curr->isWeekend()) $days++;
                     $curr->addDay();
                 }
                 return $days * $a->allocated_hours;
             });

             return [
                 'id' => $userObj->id,
                 'name' => $userObj->name,
                 'email' => $userObj->email,
                 'avatar' => $userObj->profile_photo_url, 
                 'plan_hours' => round($planHours, 1),      // To Date
                 'invested_hours' => round($investedHours, 1), // Actual
                 'pending_hours' => round($pendingHours, 1),   // Future
                 'total_hours' => round($totalScopeHours, 1)   // Grand Total
             ];
        })->filter()->values();

        // Also fetch from Tasks directly (Legacy/Board Sync)
        $taskMembers = $project->tasks()->with('assignees.user')->get()->pluck('assignees')->flatten()->map(function($e) {
            return $e->user; // Employee -> User
        })->filter();

        $members = $matrixMembers->merge($taskMembers)->unique('id')->values();
        
        // Calculate Involved Teams
        $involvedTeams = $project->assignments->map(function ($assignment) {
            $assignee = $assignment->assignee;
            if (!$assignee) return null;
            
            // Should verify if assignee is Employee or User and get Team ID
            if ($assignee instanceof \App\Models\User) {
                return $assignee->team_id;
            } elseif ($assignee instanceof \App\Models\Employee) {
                $assignee->loadMissing('user'); // Ensure user is loaded
                return $assignee->user ? $assignee->user->team_id : null;
            }
            return null;
        })->filter()->unique()->count();

        // Global Stats (Aggregated from Contributors for consistency)
        $totalExpected = $contributors->sum('plan_hours'); // Exp (Plan to Date)
        $totalInvested = $contributors->sum('invested_hours'); // Act (Approved)
        $totalPending = $contributors->sum('pending_hours'); // Rem (Future)
        $totalScope = $contributors->sum('total_hours'); // Tot (Grand Total)

        // Stats
        $stats = [
            'total_tasks' => $project->tasks()->count(),
            'completed_tasks' => $project->tasks()->whereHas('stage', fn($q) => $q->where('type', 'done'))->count(),
            'overdue_tasks' => $project->tasks()->where('due_date', '<', now())->whereHas('stage', fn($q) => $q->where('type', '!=', 'done'))->count(),
            'members_count' => $members->count(), 
            'contributors_count' => $matrixMembers->unique('id')->count(), 
            
            // New 4-Column Stats
            'expected_hours' => round($totalExpected, 1),
            'invested_hours' => round($totalInvested, 1),
            'pending_hours' => round($totalPending, 1),
            'total_scope_hours' => round($totalScope, 1),
            
            'currency' => $project->currency ?? 'USD',
            'involved_teams_count' => $involvedTeams
        ];
        


        // Recent Activity
        $activity = \App\Models\TaskActivity::whereHas('task', fn($q) => $q->where('project_id', $project->id))
            ->with(['user', 'task'])
            ->latest()
            ->take(10)
            ->get();
            
        // Current Sprint
        $activeSprint = $project->sprints->first();



        // Overview Tab (Standard)
        return Inertia::render('Project/Show', [
            'project' => $project,
            'stats' => $stats,
            'members' => $members,
            'contributors' => $contributors,
            'activity' => $activity,
            'activeSprint' => $activeSprint
        ]);
    }
    
    // --- War Room / Incident Management ---
    public function storeIncident(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:Security Breach,Data Loss,Critical Issue,Performance Degradation',
            'severity' => 'required|in:High,Critical,Severe',
            'description' => 'nullable|string'
        ]);
        
        // Auto-Detect Impact
        $impactGraph = $this->assetService->detectImpact(
            $validated['title'], 
            $validated['description'] ?? '', 
            $validated['type']
        );

        $incident = \App\Models\ProjectIncident::create([
            'project_id' => $project->id,
            'declared_by' => auth()->id(),
            'title' => $validated['title'],
            'type' => $validated['type'],
            'severity' => $validated['severity'],
            'description' => $validated['description'],
            'status' => 'Active',
            'lifecycle_stage' => 'Detection',
            'data_impact_map' => $impactGraph
        ]);
        
        $this->logger->log('WarRoom', 'Declare', "War Declared: {$incident->title} ({$incident->type})", ['project_id' => $project->id]);
        
        return back()->with('success', 'War declared successfully. Protocol initiated.')->setStatusCode(303);
    }
    
    public function updateIncident(Request $request, Project $project, $incidentId)
    {
        $incident = \App\Models\ProjectIncident::findOrFail($incidentId);
        
        $validated = $request->validate([
            'lifecycle_stage' => 'required|string',
            'status' => 'required|in:Active,Contained,Resolved,Post-Mortem',
            'data_impact_map' => 'nullable|array',
            'security_details' => 'nullable|array'
        ]);
        
        $incident->update($validated);
        
        if ($validated['status'] === 'Resolved' && !$incident->resolved_at) {
            $incident->update(['resolved_at' => now()]);
        }
        
        return back()->with('success', 'Incident status updated.')->setStatusCode(303);
    }

    public function executeAction(Request $request, Project $project, $incidentId)
    {
        $action = $request->input('action'); // 'isolate_system', 'rotate_credentials'
        
        $incident = \App\Models\ProjectIncident::findOrFail($incidentId);
        
        // Simulation of Scripts
        switch ($action) {
            case 'isolate_system':
                // Real Logic: $serverService->enableMaintenance($project->server_id);
                // For now, we update project status or log it.
                $this->logger->log('WarRoom', 'Containment', "EXECUTED: System Isolation Protocol initiated for {$project->code}", ['incident_id' => $incident->id]);
                sleep(1); // Simulate work
                break;
                
            case 'rotate_credentials':
                // Real Logic: $vaultService->rotateKeys($project->id);
                $this->logger->log('WarRoom', 'Containment', "EXECUTED: Credential Rotation for DB-04/AWS", ['incident_id' => $incident->id]);
                sleep(1);
                break;
                
            default:
                return back()->with('error', 'Unknown protocol.')->setStatusCode(303);
        }
        
        // Auto-update Incident Log?
        return back()->with('success', "Protocol '{$action}' executed successfully.")->setStatusCode(303);
    }

    public function warRoom(Request $request, \App\Models\Project $project)
    {
        $this->authorize('view', $project);

        // 1. Load Data for War Room
        $project->load(['client', 'sprints' => function($q) {
             $q->where('status', 'active');
        }, 'assignments.assignee']);

        // 2. Resolve Members (Simplified from Show)
        $members = $project->assignments->map(function ($a) {
             if ($a->assignee_type === \App\Models\Employee::class && $a->assignee) {
                 return $a->assignee->user; 
             }
             if ($a->assignee_type === \App\Models\User::class && $a->assignee) {
                 return $a->assignee;
             }
             return null;
        })->filter()->unique('id')->values();
        
        // 3. Team Battle Stations (Online Status)
        $team = $members->map(function($m) use ($project) {
             $lastAct = \App\Models\TaskActivity::where('user_id', $m->id)
                ->latest()
                ->first();
             
             $m->is_online = $lastAct && $lastAct->created_at->diffInMinutes(now()) < 240; // 4 Hours
             $m->current_task = $project->tasks()
                ->whereHas('assignees', fn($q) => $q->where('user_id', $m->id))
                ->whereHas('stage', fn($q) => $q->where('type', 'doing'))
                ->first();
             return $m;
        });

        // 4. Critical Blockers
        $blockers = $project->tasks()
            ->where(function($q) {
                $q->where('priority', 'Critical')
                  ->orWhere('status', 'Blocked')
                  ->orWhereHas('stage', function($s) {
                      $s->where('name', 'like', '%Block%');
                  });
            })
            ->whereHas('stage', function($q) {
                 $q->where('type', '!=', 'done');
            })
            ->with(['assignees', 'stage'])
            ->take(10)
            ->get();
         
        // 5. Real-time Pulse
        $pulse = \App\Models\TaskActivity::whereHas('task', fn($q) => $q->where('project_id', $project->id))
            ->with(['user', 'task'])
            ->latest()
            ->take(50)
            ->get();

        // 6. Active Wars (Incidents)
        $incidents = \App\Models\ProjectIncident::where('project_id', $project->id)
            ->where('status', '!=', 'Post-Mortem')
            ->latest()
            ->get();
        
        $activeSprint = $project->sprints->first();

        return Inertia::render('Project/WarRoom', [
            'project' => $project,
            'blockers' => $blockers,
            'pulse' => $pulse,
            'team' => $team,
            'incidents' => $incidents,
            'activeSprint' => $activeSprint
        ]);
    }

    public function taskList(Request $request, \App\Models\Project $project)
    {
        $this->authorize('view', $project);

        // Ensure Default Stages Logic immediately
        if ($project->stages()->count() === 0) {
            $defaults = [
                ['name' => 'To Do', 'type' => 'todo', 'color' => '#64748b'],
                ['name' => 'In Progress', 'type' => 'doing', 'color' => '#3b82f6'],
                ['name' => 'In Review', 'type' => 'review', 'color' => '#f59e0b'],
                ['name' => 'Done', 'type' => 'done', 'color' => '#10b981'],
            ];
            foreach ($defaults as $i => $d) {
                $project->stages()->create(array_merge($d, ['slug' => \Illuminate\Support\Str::slug($d['name']), 'order' => $i]));
            }
        }

        // Ensure Default Priorities Logic
        if ($project->priorities()->count() === 0) {
            $defaults = [
                ['name' => 'Low', 'color' => '#64748b'],
                ['name' => 'Medium', 'color' => '#3b82f6'],
                ['name' => 'High', 'color' => '#f97316'],
                ['name' => 'Critical', 'color' => '#ef4444']
            ];
            foreach ($defaults as $i => $d) {
                $project->priorities()->create(array_merge($d, ['order' => $i]));
            }
        }

        // Load metadata
        // We load 'assignments.assignee' to get the potential list of filterable users. 
        // In reality, we might want all employees in the system or just project members.
        // Let's assume project members for now.
        $project->load(['stages', 'priorities', 'assignments.assignee']);

        $query = $project->tasks()->with(['assignees', 'creator', 'module', 'stage', 'sprint']);

        // 1. Status Filter
        if ($request->has('status') && !empty($request->status)) {
            // If comma separated string (from URL query string manual entry) or array
            $statuses = is_array($request->status) ? $request->status : explode(',', $request->status);
            $query->whereIn('stage_id', $statuses); // assumes filtering by stage ID, not name
        }

        // 2. Priority Filter
        if ($request->has('priority') && !empty($request->priority)) {
            $priorities = is_array($request->priority) ? $request->priority : explode(',', $request->priority);
            $query->whereIn('priority', $priorities);
        }

        // 3. Assignee Filter
        if ($request->has('assignees') && !empty($request->assignees)) {
            $assignees = is_array($request->assignees) ? $request->assignees : explode(',', $request->assignees);
            $query->whereHas('assignees', function($q) use ($assignees) {
                $q->whereIn('user_id', $assignees); // Assumes we filter by User ID
            });
        }
        
        // 4. Search
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // 5. Backlog Filter
        if ($request->has('is_backlog') && $request->is_backlog !== '') {
            $query->where('is_backlog', (bool) $request->is_backlog);
        }

        // 5. Export
        if ($request->has('export')) {
            $tasks = $query->get();
            $csvFileName = 'tasks_' . $project->code . '_' . date('Y-m-d') . '.csv';
            $headers = [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=$csvFileName",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];

            $callback = function() use($tasks) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['ID', 'Title', 'Status', 'Priority', 'Assignees', 'Due Date', 'Points']);

                foreach ($tasks as $task) {
                    fputcsv($file, [
                        $task->id,
                        $task->title,
                        $task->stage->name ?? '',
                        $task->priority,
                        $task->assignees->pluck('name')->implode(', '),
                        $task->due_date,
                        $task->scrum_points
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        $tasks = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $project->load(['modules' => fn($q) => $q->whereNull('parent_id')->with('childrenRecursive')]);

        return Inertia::render('Project/Task/Index', [
            'project'   => $project,
            'tasks'     => $tasks,
            'filters'   => $request->only(['status', 'priority', 'assignees', 'search', 'is_backlog']),
            'employees' => \App\Models\User::select('id', 'name')->with('employee:id,user_id,avatar')->whereHas('employee')->get()->map(function($u) {
                return ['id' => $u->id, 'name' => $u->name, 'avatar' => $u->employee ? $u->employee->avatar : null];
            }),
            'sprints'   => $project->sprints()->select('id', 'name', 'status')->get(),
            'modules'   => $project->modules,
        ]);
    }

    public function modules(Request $request, \App\Models\Project $project)
    {
        $this->authorize('view', $project);

        // $modules = $project->modules()->get(); 
        return Inertia::render('Project/Module/Index', [
             'project' => $project,
             // 'modules' => $modules
        ]);
    }

    public function files(Request $request, \App\Models\Project $project)
    {
        $this->authorize('view', $project);

        return Inertia::render('Project/File/Index', [
             'project' => $project
        ]);
    }

    public function reports(Request $request, \App\Models\Project $project)
    {
        $this->authorize('view', $project);

        // Load Analytics Data (Reuse PlannerApiController logic or fetch fresh)
        // For now, shell that renders the Reports View
        return Inertia::render('Project/Reports', [
             'project' => $project
        ]);
    }

    public function create()
    {
        $clients = \App\Models\Client::select('id', 'name')->get();
        $projects = \App\Models\Project::select('id', 'name')->orderBy('created_at', 'desc')->get(); // For cloning
        
        return Inertia::render('Project/Wizard/CreateProjectWizard', [
            'clients' => $clients,
            'existingProjects' => $projects
        ]);
    }

    public function store(\App\Http\Requests\ProjectManagement\StoreProjectRequest $request)
    {
        $validated = $request->validated();
        
        try {
            \DB::transaction(function () use ($validated) {
                // 1. Create Project
                $project = Project::create([
                    'client_id' => $validated['client_id'],
                    'name' => $validated['name'],
                    'code' => $validated['code'],
                    'visibility' => $validated['visibility'],
                    'status' => $validated['status'],
                    'start_date' => $validated['dates']['start'] ?? null,
                    'deadline' => $validated['dates']['end'] ?? null,
                    'gamification_settings' => ['multiplier' => 1.0] 
                ]);

                $this->logger->log('project_management', 'create', "Project initialized: {$project->name} ({$project->code})");

                // 2. Create Modules
                if (!empty($validated['modules'])) {
                    $this->createModulesRecursively($project->id, null, $validated['modules']);
                }
            });

            return to_route('projects.dashboard')
                ->with('success', 'Project initialized successfully.')
                ->setStatusCode(303); // MANDATORY: Prevent Double Submit

        } catch (\Exception $e) {
            $this->logger->log('project_management', 'create_error', "Failed to create project: " . $e->getMessage());
            return redirect()->back()->with('error', 'Project creation failed.')->setStatusCode(303);
        }
    }

    public function update(Request $request, Project $project)
    {
        // Fallback for missing ID in payload
        $id = $request->input('id', $project->id);
        if ($project->id !== $id) {
            $project = Project::findOrFail($id);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'status' => 'nullable|in:planning,active,on_hold,completed,archived'
        ]);

        $project->update($validated);
        
        $this->logger->log('project_management', 'update', "Project details updated: {$project->name}", ['project_id' => $project->id]);

        return back()->with('success', 'Project details updated successfully.')->setStatusCode(303);
    }

    private function createModulesRecursively($projectId, $parentId, $modules)
    {
        foreach ($modules as $moduleData) {
            $module = \App\Models\ProjectModule::create([
                'project_id' => $projectId,
                'parent_id' => $parentId,
                'name' => $moduleData['name'],
                'description' => $moduleData['description'] ?? null,
            ]);
            
            // Log deep creation
            // $this->logger->log('project_management', 'module_create', "Module linked: {$module->name}");

            if (!empty($moduleData['children'])) {
                $this->createModulesRecursively($projectId, $module->id, $moduleData['children']);
            }
        }
    }

    /**
     * Quick Planner: Distribute work across team members.
     */
    public function bulkAssign(Request $request, Project $project)
    {
        $validated = $request->validate([
            'tasks' => 'required|array', // List of task titles to create OR existing IDs
            'member_ids' => 'required|array',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_points' => 'nullable|integer|min:0',
            'distribution_mode' => 'required|in:even,capacity', // even split or capacity based (mocked for now)
        ]);

        $tasks = $validated['tasks']; // Assumed to be Titles for new tasks for now based on "assign work faster"
        $members = $validated['member_ids'];
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $totalPoints = $validated['total_points'] ?? 0;
        
        $duration = $startDate->diffInDays($endDate) + 1; // Inclusive
        $pointsPerTask = count($tasks) > 0 ? floor($totalPoints / count($tasks)) : 0;
        
        // Simple Round Robin Assignment
        $memberIndex = 0;
        
        try {
            \DB::transaction(function () use ($project, $tasks, $members, $startDate, $endDate, $pointsPerTask, &$memberIndex) {
                foreach ($tasks as $taskTitle) {
                    // Create Task
                    $assigneeId = $members[$memberIndex % count($members)];
                    
                    $task = $project->tasks()->create([
                        'title' => $taskTitle,
                        'status' => 'To Do',
                        'priority' => 'Medium',
                        'start_date' => $startDate,
                        'due_date' => $endDate, // They all get the full window, or sequential? User said "distributed across", usually means parallel
                        'scrum_points' => $pointsPerTask,
                        'created_by' => auth()->id()
                    ]);

                    // Assign
                    $task->assignees()->sync([$assigneeId => ['project_id' => $project->id]]);

                    $memberIndex++;
                }
            });

            return back()->with('success', 'Work distributed successfully.')->setStatusCode(303);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to distribute work: ' . $e->getMessage());
        }
    }
}
