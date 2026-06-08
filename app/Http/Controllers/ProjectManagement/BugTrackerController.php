<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\BugTicket;
use App\Models\Project;
use App\Models\WorkAssignment;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkflowStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\BugTicketTransition;
use Carbon\Carbon;

use App\Services\Project\BugWorkflowService;
use App\Services\Communication\NotificationService;
use App\Notifications\BugManualReminderNotification;
use App\Notifications\BugAssignedNotification;

class BugTrackerController extends Controller
{
    protected $workflowService;

    public function __construct(BugWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $viewAsClient = $user->hasRole('Client') || $request->query('view') === 'client';
        
        // 1. Client Redirect (Early Exit)
        if ($viewAsClient) {
            $query = BugTicket::with(['project', 'module', 'reporter', 'assignee', 'stage', 'media', 'comments.author'])
                ->where('is_client_visible', true)
                ->orderBy('created_at', 'desc');

            if ($user->hasRole('Client')) {
                $query->whereHas('project', function($q) use ($user) {
                   $q->where('client_id', $user->client_id);
                });
                $projects = Project::where('client_id', $user->client_id)->select('id', 'name')->get();
            } else {
                // Admin/Manager view - show all
                $projects = Project::select('id', 'name')->get();
            }

            $bugs = $query->paginate(50);
            $bugs->withQueryString();
            
            $openCriticalCount = BugTicket::where('is_client_visible', true)
                ->when($user->hasRole('Client'), function($q) use ($user) {
                    return $q->whereHas('project', function($pq) use ($user) {
                        $pq->where('client_id', $user->client_id);
                    });
                })
                ->whereHas('stage', function($q) {
                    $q->where('is_final', false);
                })
                ->where('severity', 'critical')
                ->count();

            $stages = WorkflowStage::whereHas('workflow', function($q) {
                $q->where('name', 'Bug Tracking');
            })->orderBy('stage_order')->get();

            return \Inertia\Inertia::render('Project/BugTracker/ExternalPortal', [
                'bugs' => $bugs,
                'filters' => $request->all(),
                'projects' => $projects,
                'open_critical_count' => $openCriticalCount,
                'stages' => $stages
            ]);
        }

        // 2. Internal Hub Logic
        $tab = $request->input('tab', 'tracker');
        
        $lookup = [
            'modules' => \App\Models\ProjectModule::pluck('name', 'id'),
            'stages' => WorkflowStage::whereHas('workflow', function($q) {
                $q->where('entity_type', BugTicket::class);
            })->pluck('name', 'id'),
            'users' => \App\Models\User::pluck('name', 'id'),
            'priorities' => ['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'],
            'severities' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical']
        ];

        $customViews = \App\Models\BugTicketView::where('user_id', auth()->id())->get()->toArray();
        if (Auth::user()->hasRole(['Manager', 'Admin', 'Super Admin'])) {
            $managerStage = WorkflowStage::where('name', 'like', '%Manager%')->first();
            if ($managerStage) {
                array_unshift($customViews, [
                    'id' => 'system-manager-approval',
                    'name' => 'Need Mgmt Approval',
                    'filters' => ['stages' => [$managerStage->id]]
                ]);
            }
        }

        $data = [
            'tab' => $tab,
            'projects' => Project::with('modules:id,project_id,name')->select('id', 'name')->get(),
            'stages' => WorkflowStage::whereHas('workflow', function($q) {
                $q->where('entity_type', BugTicket::class);
            })->orderBy('stage_order')->get(),
            'custom_views' => $customViews,
            'open_critical_count' => BugTicket::whereHas('stage', function($q) {
                $q->where('is_final', false);
            })->where('severity', 'critical')->count(),
            'counts' => null
        ];

        // 3. Tab-Specific Data Loader
        if ($tab === 'tracker') {
            $query = BugTicket::with(['project', 'module', 'reporter', 'assignee', 'assignees.assignee', 'stage'])
                ->orderBy('priority', 'asc')
                ->orderBy('created_at', 'desc');

            if ($request->project_id) {
                $query->where('project_id', $request->project_id);
            }
            if ($request->module_id) {
                $query->where('module_id', $request->module_id);
            }

            // Advanced Array-based Filters
            if ($request->severity) {
                $severities = is_array($request->severity) ? $request->severity : [$request->severity];
                $query->whereIn('severity', $severities);
            }
            if ($request->priority || $request->priorities) {
                $pris = $request->priorities ?? $request->priority;
                $priorities = is_array($pris) ? $pris : [$pris];
                $query->whereIn('priority', $priorities);
            }
            if ($request->stages) {
                $stages = is_array($request->stages) ? $request->stages : [$request->stages];
                $query->whereIn('workflow_stage_id', $stages);
            }
            if ($request->reporter_ids) {
                $reporterIds = is_array($request->reporter_ids) ? $request->reporter_ids : [$request->reporter_ids];
                $query->whereIn('reporter_id', $reporterIds);
            }
            if ($request->assignee_ids) {
                $assigneeUserIds = is_array($request->assignee_ids) ? $request->assignee_ids : [$request->assignee_ids];
                $employeeIds = \App\Models\Employee::whereIn('user_id', $assigneeUserIds)->pluck('id');
                $query->where(function($q) use ($employeeIds) {
                     $q->whereIn('assignee_id', $employeeIds)
                       ->orWhereHas('assignees', function($aq) use ($employeeIds) {
                           $aq->whereIn('assignee_id', $employeeIds);
                       });
                });
            }

            // Smart View Pre-Filters
            if ($request->view === 'system-manager-approval') {
                $managerStage = WorkflowStage::where('name', 'like', '%Manager%')->first();
                if ($managerStage) {
                    $query->where('workflow_stage_id', $managerStage->id);
                }
            }

            // My Work Filter
            if ($request->boolean('my_work')) {
                $employee = auth()->user()->employee;
                if ($employee) {
                    $query->where(function($q) use ($employee) {
                        $q->where('assignee_id', $employee->id)
                          ->where('assignee_type', get_class($employee))
                          ->orWhereHas('assignees', function($aq) use ($employee) {
                              $aq->where('assignee_id', $employee->id)
                                 ->where('assignee_type', get_class($employee));
                          });
                    });
                } else {
                    $query->where('assignee_id', 0); // Return empty if no employee linked
                }
            }

            // Timeframe Filter
            if ($request->timeframe === '30d') {
                $query->where('created_at', '>=', now()->subDays(30));
            } elseif ($request->timeframe === 'year') {
                $query->whereYear('created_at', now()->year);
            } elseif ($request->timeframe === 'sprint') {
                // Assuming sprint is last 14 days or similar logic
                $query->where('created_at', '>=', now()->subDays(14));
            }

            $totalCount = (clone $query)->count();
            $closedCount = (clone $query)->whereHas('stage', function($q) {
                $q->where('is_final', true);
            })->count();
            $openCount = $totalCount - $closedCount;

            $data['counts'] = [
                'total' => $totalCount,
                'closed' => $closedCount,
                'open' => $openCount
            ];

            $data['bugs'] = $query->paginate(50);
            $data['bugs']->withQueryString();
            $data['filters'] = $request->all();
            $data['lookup'] = $lookup;
        } 
        elseif ($tab === 'dashboard' || $tab === 'intelligence') {
            $data['lookup'] = $lookup;

            if ($tab === 'dashboard') {
                // Analytics Logic
                // 1. Module Hotspots
                $data['hotspots'] = BugTicket::select('module_id', DB::raw('count(*) as total'))
                    ->with('module:id,name')
                    ->groupBy('module_id')
                    ->orderByDesc('total')
                    ->take(10)
                    ->get();

                // 2. Status Breakdown
                $data['status_breakdown'] = BugTicket::select('workflow_stage_id', DB::raw('count(*) as total'))
                    ->with('stage:id,name')
                    ->groupBy('workflow_stage_id')
                    ->get();

                // 3. SLA Breaches (Tickets that have breached their SLA logic)
                $data['sla_breaches'] = BugTicket::with(['module:id,name'])
                    ->where('is_sla_breached', true)
                    ->whereHas('stage', function($q) {
                        $q->where('is_final', false);
                    })
                    ->orderBy('sla_due_at', 'asc') // Sort by oldest breach first
                    ->take(10)
                    ->get();

                // 4. Team Velocity (Last 7 Days)
                $labels = [];
                $created = [];
                $resolved = [];
                
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i)->format('Y-m-d');
                    $labels[] = $date;
                    
                    $created[] = BugTicket::whereDate('created_at', $date)->count();
                    
                    $resolved[] = BugTicket::whereDate('updated_at', $date)
                        ->whereHas('stage', function($q) {
                            $q->where('is_final', true);
                        })->count();
                }
                
                $data['velocity'] = [
                    'labels' => $labels,
                    'created' => $created,
                    'resolved' => $resolved
                ];
                
                // 5. Time to Resolution (Average hours over last 30 days)
                $data['avg_resolution_hours'] = round(BugTicket::whereNotNull('resolved_at')
                    ->where('resolved_at', '>=', now()->subDays(30))
                    ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, resolved_at)')) ?? 0);

                // 6. Developer Leaderboard (Throughput)
                $data['leaderboard'] = BugTicket::select('assignee_id', 'assignee_type', DB::raw('count(*) as total_resolved'))
                    ->with('assignee') // Will load Employee model
                    ->whereNotNull('resolved_at')
                    ->where('resolved_at', '>=', now()->subDays(30))
                    ->groupBy('assignee_id', 'assignee_type')
                    ->orderByDesc('total_resolved')
                    ->take(5)
                    ->get();
            }
        }

        return \Inertia\Inertia::render('Project/BugTracker/Hub', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'module_id' => 'nullable|exists:project_modules,id', // Changed to nullable
            'subject' => 'required|string|max:255',
            'severity' => 'required|in:critical,high,medium,low',
            'priority' => 'required|in:urgent,high,normal,low',
            'assignee_id' => 'nullable|integer', 
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,txt|max:10240', // Max 10MB
        ]);

        $bug = DB::transaction(function() use ($request) {
            $user = Auth::user();
            
            // 1. Determine Reporter
            $reporterId = $user->id;
            $reporterType = User::class;
            if ($user->employee) {
                $reporterId = $user->employee->id;
                $reporterType = Employee::class;
            }

            // 2. Determine Assignee (Auto-In-Charge Logic)
            $assigneeId = $request->assignee_id;
            $assigneeType = Employee::class; // Defaulting to Employee for now
            
            if (!$assigneeId) {
                // Auto-assign to Project Lead logic (simplified)
                $assigneeType = null;
            }

            // 3. Determine Initial Stage
            $stage = WorkflowStage::where('is_final', false)->orderBy('id')->first();

            // Handle Attachments
            $attachmentPaths = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('bugs/' . date('Y/m'), 'public');
                    $attachmentPaths[] = [
                        'path' => $path,
                        'name' => $file->getClientOriginalName(),
                        'mime' => $file->getClientMimeType()
                    ];
                }
            }

            $bug = BugTicket::create([
                'project_id' => $request->project_id,
                'module_id' => $request->module_id, // Can be null
                'subject' => $request->subject,
                'description' => $request->description,
                'severity' => $request->severity,
                'priority' => $request->priority,
                'reporter_id' => $reporterId,
                'reporter_type' => $reporterType,
                'assignee_id' => $assigneeId,
                'assignee_type' => $assigneeType,
                'workflow_stage_id' => $stage ? $stage->id : null,
                'is_client_visible' => false,
                'steps_to_reproduce' => $request->steps_to_reproduce,
                'environment_metadata' => $request->environment_metadata,
                'attachments' => $attachmentPaths
            ]);

            // Lifecycle Hook: Initial Transition (Pizza Tracker)
            if ($stage) {
                BugTicketTransition::create([
                    'bug_ticket_id' => $bug->id,
                    'to_stage_id' => $stage->id,
                    'actor_id' => Auth::id(),
                    'actor_type' => User::class // Internal staff
                ]);
            }

            // Save Forensics Data (Phase 10)
            if ($request->session_recording || $request->browser_metadata) {
                $bug->forensics()->create([
                    'session_recording' => $request->session_recording,
                    'browser_metadata' => $request->browser_metadata,
                    // console_logs can be added if sent from frontend
                ]);
            }

            // 4. Urgent Logic: Auto-Create Work Assignment (if applicable)
            if ($request->priority === 'urgent' && $assigneeId && $assigneeType === Employee::class) {
                 // Optimization: Move Task/Assignment creation to Observer or Service to keep Controller clean
                 // For now, keeping it minimal as per previous code
            }

            $this->logActivity($bug, 'created', 'Created the bug ticket');

            if ($assigneeId && $assigneeType) {
                 // Existing Assignee Notification
                 // ...
            }

            // 5. Notify "In-Charge" (Project Stakeholders)
            $stakeholders = WorkAssignment::where('project_id', $bug->project_id)
                ->where('assignee_type', \App\Models\Employee::class)
                ->get()
                ->map(function($assign) {
                    return $assign->assignee->user ?? \App\Models\User::where('email', $assign->assignee->email)->first();
                })
                ->filter(function($user) {
                    return $user && ($user->hasRole('Admin') || $user->hasRole('Manager'));
                })
                ->unique('id');
            
            foreach ($stakeholders as $managerUser) {
                if ($managerUser->id !== Auth::id()) {
                    $managerUser->notify(new \App\Notifications\BugAssignedNotification($bug)); 
                }
            }

            return $bug;
        });

        if ($request->wantsJson()) {
            return response()->json($bug, 201);
        }

        return redirect()->route('bugs.index', ['tab' => 'tracker'])->with('success', 'Bug ticket created successfully.');
    }

    /**
     * Get employees assigned to the project (for dropdown).
     */
    public function getAvailableAssignees(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|integer',
            'project_ids' => 'nullable|array',
            'project_ids.*' => 'integer',
        ]);

        $projectIds = collect($validated['project_ids'] ?? [])
            ->push($validated['project_id'] ?? null)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        // If no project filter is passed, return assignees from all project assignments
        $assignmentsQuery = WorkAssignment::query();
        if ($projectIds->isNotEmpty()) {
            $assignmentsQuery->whereIn('project_id', $projectIds);
        }

        $employeeAssigneeIds = $assignmentsQuery
            ->where('assignee_type', Employee::class)
            ->distinct() // Optimized
            ->pluck('assignee_id');

        $userAssigneeIds = (clone $assignmentsQuery)
            ->where('assignee_type', \App\Models\User::class)
            ->distinct()
            ->pluck('assignee_id');

        $userMappedEmployeeIds = Employee::query()
            ->whereIn('user_id', $userAssigneeIds)
            ->pluck('id');

        $resolvedEmployeeIds = $employeeAssigneeIds
            ->merge($userMappedEmployeeIds)
            ->filter()
            ->unique()
            ->values();

        // Fallback: if no assignment mapping exists, provide active employee directory
        // so assignment controls are still usable.
        if ($resolvedEmployeeIds->isEmpty()) {
            $resolvedEmployeeIds = Employee::query()
                ->where('status', 'active')
                ->orWhereNull('status')
                ->pluck('id');
        }
            
        $employees = Employee::whereIn('id', $resolvedEmployeeIds)
            ->select('id', 'user_id', 'first_name', 'last_name', 'avatar')
            ->get()
            ->map(function($e) {
                return [
                    'id' => $e->id,
                    'user_id' => $e->user_id,
                    'name' => $e->first_name . ' ' . $e->last_name,
                    'avatar' => $e->avatar
                ];
            });
            
        return response()->json($employees);
    }
    
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:bug_tickets,id',
            'workflow_stage_id' => 'nullable|exists:workflow_stages,id',
            'assignee_id' => 'nullable|exists:employees,id',
            'notify' => 'boolean'
        ]);

        $updateData = [];
        if ($request->workflow_stage_id) $updateData['workflow_stage_id'] = $request->workflow_stage_id;
        if ($request->assignee_id) {
            $updateData['assignee_id'] = $request->assignee_id;
            $updateData['assignee_type'] = \App\Models\Employee::class;
        }

        if (empty($updateData)) return response()->json(['message' => 'No updates provided'], 400);

        $updateData['updated_at'] = now();

        BugTicket::whereIn('id', $request->ids)->update($updateData);

        if ($request->notify) {
            foreach ($request->ids as $id) {
                $bug = BugTicket::find($id);
                $this->logActivity($bug, 'p_bulk', "Bulk updated " . implode(', ', array_keys($updateData)));
                
                // Optional: Create a system comment
                $bug->comments()->create([
                    'user_id' => Auth::id(),
                    'body' => "<strong>System Note:</strong> Bulk update applied. " . ($request->assignee_id ? "Assigned to " . $bug->assignee?->name : ""),
                    'is_public' => false // Internal system note
                ]);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    public function updateStage(Request $request, $id)
    {
        $bug = BugTicket::findOrFail($id);
        $this->authorize('update', $bug);

        $stage = WorkflowStage::findOrFail($request->stage_id);

        // Phase 11: Approval Gate Logic
        $currentStage = $bug->stage;
        if ($currentStage && $currentStage->requires_approval) {
            // Admin/Super Admin override and Mentor Override
            if (!Auth::user()->hasRole(['Admin', 'Super Admin']) && $currentStage->mentor_id !== Auth::id()) {
                // Check if user has the required role to execute this transition
                $approverRoleId = $currentStage->role_id;
                
                if ($approverRoleId && !Auth::user()->hasRole($approverRoleId)) {
                    return back()->withErrors(['message' => "Transition out of '{$currentStage->name}' requires approval from " . ($currentStage->role->name ?? 'authorized personnel') . "."])->setStatusCode(303);
                }
            }
        }

        if ($stage->role_id && !Auth::user()->hasAnyRole(['Admin', 'Super Admin', $stage->role_id])) {
            // log warn but let it pass if transition is from Architect
        }
        
        $fromStageName = $bug->stage->name ?? 'Blank';
        $toStageName = $stage->name;
        
        $this->workflowService->transition($bug, $stage, $request->resolution_note, [
            'assignee_id' => $request->assignee_id
        ]);

        return back()->with('success', 'Stage updated successfully.')->setStatusCode(303);
    }

    public function advancedStageUpdate(Request $request, $id, NotificationService $notificationService)
    {
        $bug = BugTicket::findOrFail($id);
        $this->authorize('update', $bug);

        $request->validate([
            'stage' => 'required|integer|exists:workflow_stages,id',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'nullable',
            'note' => 'nullable|string',
        ]);
        
        $fromStageId = $bug->workflow_stage_id;
        $fromStageName = $bug->stage->name ?? 'Initial';

        $stageId = (int) $request->input('stage');
        $stage = WorkflowStage::findOrFail($stageId);
        $toStageName = $stage->name;
        
        // 1. Transition Rule Check
        if ($bug->stage && !empty($bug->stage->transition_rules)) {
            if (!in_array($stage->id, $bug->stage->transition_rules)) {
                return back()->withErrors(['message' => "Invalid transition. You cannot move from '{$bug->stage->name}' to '{$stage->name}'."]);
            }
        }

        // 1b. Approval Gate Check
        if ($bug->stage && $bug->stage->requires_verification) {
            $isApproved = \App\Models\WorkflowApproval::whereHas('workflowInstance', function($q) use ($bug) {
                $q->where('entity_type', BugTicket::class)->where('entity_id', $bug->id);
            })->where('stage_id', $bug->stage->id)->where('status', 'approved')->exists();

            if (!$isApproved && !Auth::user()->hasRole(['Admin', 'Super Admin']) && $bug->stage->mentor_id !== Auth::id()) {
                return back()->withErrors(['message' => "Stage '{$bug->stage->name}' requires verification approval before moving forward."]);
            }
        }

        // 2. Role Check (Existing)
        if ($stage->role_id && !Auth::user()->hasRole($stage->role_id)) {
            if (!Auth::user()->hasRole(['Admin', 'Super Admin']) && $stage->mentor_id !== Auth::id() && $bug->stage->mentor_id !== Auth::id()) {
                return back()->withErrors(['message' => 'You do not have the required role for this stage.']);
            }
        }
        
        // Use Centralized Workflow Service for core transition
        $this->workflowService->transition($bug, $stage, $request->note, [
            'assignee_ids' => $request->assignee_ids // Note: transition method might need to be aware of multi-assignees if we want
        ]);

        $assignedUsers = collect();

        if ($request->has('assignee_ids') && is_array($request->assignee_ids)) {
            $bug->assignees()->delete(); 
            $firstEmployeeId = null;
            
            foreach ($request->assignee_ids as $rawAssigneeId) {
                $assigneeId = (int) $rawAssigneeId;
                if (!$assigneeId) {
                    continue;
                }

                // Accept both employee IDs (from assignee picker) and legacy user IDs.
                $employee = Employee::find($assigneeId) ?? User::find($assigneeId)?->employee;
                if ($employee) {
                    $bug->assignees()->create([
                        'assignee_id' => $employee->id,
                        'assignee_type' => Employee::class
                    ]);
                    if (!$firstEmployeeId) $firstEmployeeId = $employee->id;

                    if ($employee->user) {
                        $assignedUsers->push($employee->user);
                    }
                }
            }
            
            if ($firstEmployeeId) {
                $bug->update(['assignee_id' => $firstEmployeeId, 'assignee_type' => Employee::class]);
            } else {
                $bug->update(['assignee_id' => null, 'assignee_type' => null]);
            }

            // Include the acting user as well so assignment actions are visible in their Notification Center.
            $assignedUsers->push(Auth::user());

            $assignedUsers = $assignedUsers
                ->filter(fn ($user) => $user)
                ->unique('id')
                ->values();

            if ($assignedUsers->isNotEmpty()) {
                $notificationService->send($assignedUsers, new BugAssignedNotification($bug->fresh('project')));
            }
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('bugs/attachments/' . date('Y/m'), 'public');
                $bug->media()->create([
                    'file_path'     => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'file_name'     => $file->getClientOriginalName(),
                    'file_type'     => $file->getClientMimeType(),
                    'file_size'     => $file->getSize(),
                    'uploaded_by'   => Auth::id()
                ]);
            }
        }

        // 3. Mentor Notification/Logic
        if ($stage->mentor_id) {
            $mentor = User::find($stage->mentor_id);
            if ($mentor) {
                $bug->comments()->create([
                    'user_id' => Auth::id(),
                    'body' => "<strong>Mentor Alert:</strong> This ticket has entered a stage mentored by <strong>{$mentor->name}</strong>.",
                    'is_public' => false
                ]);
            }
        }

        // 4. Approval Flow Initialization
        if ($stage->requires_verification) {
            $instance = \App\Models\WorkflowInstance::firstOrCreate([
                'entity_type' => BugTicket::class,
                'entity_id' => $bug->id,
                'workflow_id' => $stage->workflow_id,
            ], [
                'initiator_id' => Auth::id(),
                'current_stage_id' => $stage->id,
                'status' => 'pending',
                'started_at' => now(),
            ]);

            $approverId = null;
            if ($stage->approver_type === 'specific_user') {
                $approverId = $stage->user_id;
            } elseif ($stage->approver_type === 'manager') {
                if ($bug->assignee instanceof \App\Models\Employee) {
                    $approverId = $bug->assignee->reporting_to;
                } elseif ($bug->assignee instanceof \App\Models\User) {
                    $approverId = $bug->assignee->manager_id;
                } else {
                    $approverId = Auth::user()->manager_id;
                }
            } elseif ($stage->approver_type === 'department_head') {
                $employee = null;
                if ($bug->assignee instanceof \App\Models\Employee) {
                    $employee = $bug->assignee;
                } elseif ($bug->assignee instanceof \App\Models\User) {
                    $employee = $bug->assignee->employee;
                }
                $approverId = $employee?->department?->head_id;
            }

            \App\Models\WorkflowApproval::updateOrCreate([
                'workflow_instance_id' => $instance->id,
                'stage_id' => $stage->id,
            ], [
                'approver_id' => $approverId,
                'status' => 'pending',
            ]);

            $bug->comments()->create([
                'user_id' => Auth::id(),
                'body' => "<strong>Approval Required:</strong> Verification gate has been locked for stage <strong>{$stage->name}</strong>.",
                'is_public' => false
            ]);
        }

        $this->logActivity($bug, 'p_change', "Advanced stage from $fromStageName to {$toStageName}");
        
        // Phase 11: Clear notifications
        $this->clearStageNotifications($bug, $fromStageId);
        $this->notifyStageParticipants($bug, $stage);

        $bug->load(['assignee', 'assignees.assignee']);

        return back()->with('success', 'Ticket stage advanced successfully.')->setStatusCode(303);
    }

    public function approveStage(Request $req, $id)
    {
        $bug = BugTicket::findOrFail($id);
        $approval = \App\Models\WorkflowApproval::whereHas('workflowInstance', function($q) use ($bug) {
            $q->where('entity_type', BugTicket::class)->where('entity_id', $bug->id);
        })->where('stage_id', $bug->workflow_stage_id)->where('status', 'pending')->first();

        if (!$approval) return back()->withErrors(['message' => "No pending approval found for this stage."]);

        // Security: Check if current user is the approver OR has a role that can approve OR is Mentor
        if ($approval->approver_id && $approval->approver_id !== Auth::id() && !Auth::user()->hasRole(['Admin', 'Super Admin']) && $bug->stage?->mentor_id !== Auth::id()) {
            return back()->withErrors(['message' => "You are not authorized to approve this stage."]);
        }

        $approval->update([
            'status' => 'approved',
            'acted_at' => now(),
            'comments' => $req->comment ?? 'Approved via System Hub'
        ]);

        $bug->comments()->create([
            'user_id' => Auth::id(),
            'body' => "<strong>Verification Approved:</strong> work has been signed off for stage <strong>{$bug->stage->name}</strong>. Ticket can now proceed.",
            'is_public' => false
        ]);

        return back()->with('success', 'Stage verification approved.');
    }

    public function rejectStage(Request $req, $id)
    {
        $bug = BugTicket::findOrFail($id);
        $approval = \App\Models\WorkflowApproval::whereHas('workflowInstance', function($q) use ($bug) {
            $q->where('entity_type', BugTicket::class)->where('entity_id', $bug->id);
        })->where('stage_id', $bug->workflow_stage_id)->where('status', 'pending')->first();

        if (!$approval) return back()->withErrors(['message' => "No pending approval found for this stage."]);

        // Security: Mentor or specific approver authorization
        if ($approval->approver_id && $approval->approver_id !== Auth::id() && !Auth::user()->hasRole(['Admin', 'Super Admin']) && $bug->stage?->mentor_id !== Auth::id()) {
            return back()->withErrors(['message' => "You are not authorized to reject this stage validation."]);
        }

        $approval->update([
            'status' => 'rejected',
            'acted_at' => now(),
            'comments' => $req->comment
        ]);

        $bug->comments()->create([
            'user_id' => Auth::id(),
            'body' => "<strong>Verification Rejected:</strong> {$req->comment}",
            'is_public' => false
        ]);

        return back()->with('success', 'Stage verification rejected.');
    }

    public function exportJSON()
    {
        $bugs = BugTicket::with(['project', 'module', 'stage', 'reporter', 'assignee'])->get();
        return response()->json($bugs);
    }

    public function exportPDF(Request $request)
    {
        // Simple PDF export logic (In a real app, use dompdf / snappy)
        // For now, return a view that can be printed or a CSV as fallback if PDF engine not installed.
        // Assuming we have a PDF service or just returning JSON as a placeholder for "High-Control" logic.
        $query = BugTicket::with(['project', 'module', 'stage']);

        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->module_id) {
            $query->where('module_id', $request->module_id);
        }
        if ($request->severity) {
            $severities = is_array($request->severity) ? $request->severity : [$request->severity];
            $query->whereIn('severity', $severities);
        }
        if ($request->priority || $request->priorities) {
            $pris = $request->priorities ?? $request->priority;
            $priorities = is_array($pris) ? $pris : [$pris];
            $query->whereIn('priority', $priorities);
        }
        if ($request->stages) {
            $stages = is_array($request->stages) ? $request->stages : [$request->stages];
            $query->whereIn('workflow_stage_id', $stages);
        }
        if ($request->reporter_ids) {
            $reporterIds = is_array($request->reporter_ids) ? $request->reporter_ids : [$request->reporter_ids];
            $query->whereIn('reporter_id', $reporterIds);
        }
        if ($request->assignee_ids) {
            $assigneeUserIds = is_array($request->assignee_ids) ? $request->assignee_ids : [$request->assignee_ids];
            $employeeIds = \App\Models\Employee::whereIn('user_id', $assigneeUserIds)->pluck('id');
            $query->where(function($q) use ($employeeIds) {
                 $q->whereIn('assignee_id', $employeeIds)
                   ->orWhereHas('assignees', function($aq) use ($employeeIds) {
                       $aq->whereIn('assignee_id', $employeeIds);
                   });
            });
        }
        if ($request->search) {
            $query->where('subject', 'like', "%{$request->search}%");
        }

        $bugs = $query->get();
        
        // Let's at least make it a CSV for now as a "Functional Export"
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=bug_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Subject', 'Project', 'Module', 'Severity', 'Priority', 'Stage', 'Created At'];

        $callback = function() use($bugs, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($bugs as $bug) {
                fputcsv($file, [
                    $bug->id,
                    $bug->subject,
                    $bug->project?->name ?? 'N/A',
                    $bug->module?->name ?? 'N/A',
                    $bug->severity,
                    $bug->priority,
                    $bug->stage?->name ?? 'N/A',
                    $bug->created_at
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function analytics(Request $request)
    {
        $projectId = $request->project_id;

        // 1. Module Hotspots
        $hotspots = BugTicket::select('module_id', DB::raw('count(*) as total'))
            ->when($projectId, function($q) use ($projectId) {
                return $q->where('project_id', $projectId);
            })
            ->with('module:id,name')
            ->groupBy('module_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        // 2. Status Breakdown
        $statusBreakdown = BugTicket::select('workflow_stage_id', DB::raw('count(*) as total'))
            ->when($projectId, function($q) use ($projectId) {
                return $q->where('project_id', $projectId);
            })
            ->with('stage:id,name')
            ->groupBy('workflow_stage_id')
            ->get();

        // 3. SLA Breaches
        $slaBreaches = BugTicket::whereHas('stage', function($q) {
            $q->where('is_final', false);
        })->when($projectId, function($q) use ($projectId) {
            return $q->where('project_id', $projectId);
        })->orderBy('created_at', 'asc')->take(10)->get();

        // 4. Team Velocity
        $labels = [];
        $created = [];
        $resolved = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = $date;
            $created[] = BugTicket::whereDate('created_at', $date)
                ->when($projectId, function($q) use ($projectId) {
                    return $q->where('project_id', $projectId);
                })->count();
            $resolved[] = BugTicket::whereDate('updated_at', $date)
                ->when($projectId, function($q) use ($projectId) {
                    return $q->where('project_id', $projectId);
                })
                ->whereHas('stage', function($q) { $q->where('is_final', true); })->count();
        }

        // 5. Leaderboard
        $leaderboard = BugTicket::whereHas('stage', function($q) { $q->where('is_final', true); })
            ->select('assignee_id', DB::raw('count(*) as total_resolved'))
            ->whereNotNull('assignee_id')
            ->where('assignee_type', \App\Models\User::class)
            ->when($projectId, function($q) use ($projectId) {
                return $q->where('project_id', $projectId);
            })
            ->with('assignee:id,name')
            ->groupBy('assignee_id')
            ->orderByDesc('total_resolved')
            ->take(5)
            ->get();

        // 6. Avg Resolution Time
        $avgResolutionHours = BugTicket::whereNotNull('resolved_at')
            ->when($projectId, function($q) use ($projectId) {
                return $q->where('project_id', $projectId);
            })
            ->select(DB::raw('avg(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours'))
            ->value('avg_hours') ?? 0;

        return \Inertia\Inertia::render('Project/BugTracker/Analytics', [
            'projectId' => $projectId,
            'hotspots' => $hotspots,
            'status_breakdown' => $statusBreakdown,
            'sla_breaches' => $slaBreaches,
            'velocity' => [
                'labels' => $labels,
                'created' => $created,
                'resolved' => $resolved
            ],
            'avg_resolution_hours' => round($avgResolutionHours, 1),
            'leaderboard' => $leaderboard,
            'lookup' => [
                'modules' => \App\Models\ProjectModule::pluck('name', 'id'),
                'stages' => \App\Models\WorkflowStage::whereHas('workflow', function($q) {
                    $q->where('entity_type', BugTicket::class);
                })->pluck('name', 'id'),
                'users' => \App\Models\User::pluck('name', 'id'),
            ]
        ]);
    }

    public function queryAnalytics(Request $request)
    {
        $request->validate([
            'groupBy' => 'required|in:module_id,severity,priority,workflow_stage_id,assignee_id',
            'metric' => 'required|in:count,avg_resolution_time',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date'
        ]);

        $query = BugTicket::query();

        if ($request->date_start) $query->whereDate('created_at', '>=', $request->date_start);
        if ($request->date_end) $query->whereDate('created_at', '<=', $request->date_end);

        $dimension = $request->groupBy;
        $metric = $request->metric;

        if ($metric === 'count') {
            $results = $query->select($dimension, DB::raw('count(*) as value'))
                ->groupBy($dimension)
                ->get();
        } else {
            // Avg Resolution Time (requires resolved_at and created_at)
            $results = $query->whereNotNull('resolved_at')
                ->select($dimension, DB::raw('avg(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as value'))
                ->groupBy($dimension)
                ->get();
        }

        return response()->json($results);
    }

    public function show(BugTicket $bug)
    {
        $this->authorize('view', $bug);
        
        $bug->load([
            'project', 
            'module', 
            'reporter', 
            'assignee', 
            'assignees.assignee',
            'stage', 
            'comments.author', 
            'activities.user',
            'task',
            'forensics', // Phase 10
            // 'pendingApproval' // Currently breaking due to SQL aliasing in polymorphic through
        ]);

        $lastReminder = $bug->activities()
            ->where('activity_type', 'manual_reminder_sent')
            ->latest('created_at')
            ->first();

        $bug->setAttribute('reminder_sent_today', $bug->activities()
            ->where('activity_type', 'manual_reminder_sent')
            ->whereDate('created_at', now()->toDateString())
            ->exists());
        $bug->setAttribute('last_reminded_at', $lastReminder?->created_at);
        
        return response()->json($bug);
    }

    public function sendReminder(Request $request, BugTicket $bug, NotificationService $notificationService)
    {
        $this->authorize('update', $bug);

        $alreadySentToday = $bug->activities()
            ->where('activity_type', 'manual_reminder_sent')
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($alreadySentToday) {
            return response()->json([
                'message' => 'Reminder already sent today for this bug.',
            ], 422);
        }

        $usersToNotify = collect();

        if ($bug->assignee_type === Employee::class && $bug->assignee_id) {
            $primaryEmployee = Employee::with('user')->find($bug->assignee_id);
            if ($primaryEmployee?->user) {
                $usersToNotify->push($primaryEmployee->user);
            }
        }

        if ($bug->assignee_type === User::class && $bug->assignee_id) {
            $primaryUser = User::find($bug->assignee_id);
            if ($primaryUser) {
                $usersToNotify->push($primaryUser);
            }
        }

        if ($bug->relationLoaded('assignees')) {
            $extraAssignees = $bug->assignees;
        } else {
            $extraAssignees = $bug->assignees()->get();
        }

        $extraEmployeeIds = $extraAssignees
            ->where('assignee_type', Employee::class)
            ->pluck('assignee_id')
            ->filter()
            ->unique()
            ->values();

        if ($extraEmployeeIds->isNotEmpty()) {
            $extraUsers = Employee::with('user')
                ->whereIn('id', $extraEmployeeIds)
                ->get()
                ->pluck('user')
                ->filter();

            $usersToNotify = $usersToNotify->merge($extraUsers);
        }

        $usersToNotify = $usersToNotify
            ->filter()
            ->unique('id')
            ->values();

        if ($usersToNotify->isNotEmpty()) {
            $notificationService->send($usersToNotify, new BugManualReminderNotification($bug, Auth::user()));
        }

        $this->logActivity(
            $bug,
            'manual_reminder_sent',
            'Manual reminder sent to assigned members.',
            [
                'sent_by' => Auth::id(),
                'recipients' => $usersToNotify->pluck('id')->all(),
            ]
        );

        return response()->json([
            'message' => 'Reminder sent successfully. You can send another reminder tomorrow.',
            'reminder_sent_today' => true,
        ]);
    }

    public function storeComment(Request $request, BugTicket $bug)
    {
        $request->validate([
            'body' => 'required|string',
            'is_public' => 'boolean'
        ]);
        
        $comment = $bug->comments()->create([
            'user_id' => Auth::id(),
            'body' => $request->body,
            'is_public' => $request->is_public ?? true,
            'attachments' => $request->attachments ?? []
        ]);
        
        // Log Activity
        $this->logActivity($bug, 'commented', 'Added a comment');
        
        return response()->json($comment->load('author'));
    }

    public function pendingApprovals(Request $request)
    {
        $managerStage = WorkflowStage::where('name', 'like', '%Manager%')->first();
        
        $query = BugTicket::with(['project', 'module', 'reporter', 'assignee', 'stage'])
            ->whereHas('stage', function($q) use ($managerStage) {
                if ($managerStage) {
                    $q->where('id', $managerStage->id);
                } else {
                    $q->where('requires_verification', true);
                }
            })->orderBy('created_at', 'desc');

        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        return \Inertia\Inertia::render('Project/Approvals/Index', [
            'tickets' => $query->paginate(50)->withQueryString(),
            'projects' => Project::select('id', 'name')->get(),
            'filters' => $request->all()
        ]);
    }

    public function clientPortal(Request $request)
    {
        $user = Auth::user();
        $query = BugTicket::with(['project', 'module', 'stage'])
            ->where('reporter_id', $user->id)
            ->where('reporter_type', get_class($user))
            ->orderBy('created_at', 'desc');

        return \Inertia\Inertia::render('Project/ClientPortal/Index', [
            'tickets' => $query->paginate(30)->withQueryString(),
            'projects' => Project::where('client_id', $user->client_id)->get(),
            'filters' => $request->all()
        ]);
    }

    /**
     * Phase 11: Stage Transition Utilities
     */
    private function clearStageNotifications(BugTicket $bug, $stageId)
    {
        if (!$stageId) return;
        
        // People previously involved in this stage relative to THIS bug
        // We find their unread database notifications for this bug
        DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->whereNull('read_at')
            ->where('data', 'like', '%"bug_id":' . $bug->id . '%')
            ->update(['read_at' => now()]);
    }

    private function notifyStageParticipants(BugTicket $bug, WorkflowStage $stage)
    {
        // Resolve all people listed in this stage configuration (Workers + Approvers)
        $referenceUser = $bug->assignee && get_class($bug->assignee) === User::class ? $bug->assignee : Auth::user();
        $participants = $stage->resolveAllApprovers($referenceUser); 
        
        foreach ($participants as $participant) {
            if ($participant) {
                $participant->notify(new \App\Notifications\BugAssignedNotification($bug));
            }
        }
    }

    /**
     * Phase 11: Stage Config Method
     */
    public function updateStagePeople(Request $request, WorkflowStage $stage)
    {
        $request->validate([
            'approver_type' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'role_id' => 'nullable|exists:roles,id',
            'team_id' => 'nullable|exists:teams,id',
            'additional_approvers' => 'nullable|array'
        ]);

        $stage->update($request->only(['approver_type', 'user_id', 'role_id', 'team_id', 'additional_approvers', 'requires_approval']));

        return response()->json(['status' => 'ok', 'message' => 'Stage participants updated successfully']);
    }

    /**
     * Phase 11: Bulk Management Methods
     */
    public function downloadSampleExcel()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=bug_import_sample.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Subject', 'Description', 'Severity', 'Priority', 'ProjectName', 'ModuleName'];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['Sample Bug Title', 'Detailed description of the issue', 'medium', 'normal', 'Core App', 'Auth Module']);
            fputcsv($file, ['CRITICAL: Login failing', 'Steps to reproduce...', 'critical', 'urgent', 'Mobile Web', 'UI Engine']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx',
            'project_id' => 'required|exists:projects,id'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), "r");
        $header = fgetcsv($handle, 1000, ",");
        
        $importedCount = 0;
        $failedCount = 0;
        
        $firstStage = WorkflowStage::whereHas('workflow', function($q) {
            $q->where('entity_type', BugTicket::class);
        })->orderBy('stage_order')->first();

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            try {
                // Attempt to link module by name if provided
                $moduleName = $data[5] ?? null;
                $moduleId = null;
                if ($moduleName) {
                    $module = \App\Models\ProjectModule::where('project_id', $request->project_id)
                        ->where('name', 'like', "%$moduleName%")
                        ->first();
                    $moduleId = $module?->id;
                }

                $bug = BugTicket::create([
                    'project_id' => $request->project_id,
                    'module_id' => $moduleId,
                    'subject' => $data[0] ?? 'Untitled Bug',
                    'description' => $data[1] ?? '',
                    'severity' => strtolower($data[2] ?? 'medium'),
                    'priority' => strtolower($data[3] ?? 'normal'),
                    'reporter_id' => Auth::id(),
                    'reporter_type' => User::class,
                    'workflow_stage_id' => $firstStage?->id,
                    'is_client_visible' => false
                ]);

                $this->logActivity($bug, 'imported', 'Imported via Bulk Upload');
                $importedCount++;

            } catch (\Exception $e) {
                $failedCount++;
            }
        }
        fclose($handle);

        return back()->with('success', "Import Complete. $importedCount tickets created. $failedCount failed.");
    }

    private function logActivity(BugTicket $bug, $type, $description, $details = null)
    {
        $bug->activities()->create([
            'actor_id' => Auth::id(),
            'actor_type' => User::class,
            'activity_type' => $type,
            'description' => $description,
            'details' => $details
        ]);
    }
}
