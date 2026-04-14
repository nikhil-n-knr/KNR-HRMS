<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\BugTicket;
use App\Models\KnowledgeArticle;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\WorkflowStage;
use App\Models\ClientEnvironmentPreset;
use App\Models\User;
use App\Models\Employee;
use App\Models\WorkAssignment;
use App\Models\BugTicketTransition;
use App\Models\VaultCategory;
use App\Models\VaultArticle;
use App\Notifications\BugAssignedNotification;
use App\Notifications\BugStageChangedNotification;
use App\Notifications\BugRatingReceivedNotification;
use Inertia\Inertia;

class ClientPortalController extends Controller
{
    public function dashboard()
    {
        $user = Auth::guard('client')->user();

        // Action Center: Awaiting Verification
        $awaitingVerification = BugTicket::whereIn('project_id', $user->projects->pluck('id'))
            ->whereHas('stage', function($q) {
                $q->where('requires_verification', true)
                  ->where('is_final', false);
            })
            ->with([
                'stage',
                'module',
                'assignee',
                'media',
                'comments.author',
                'project',
                'transitions.fromStage',
                'transitions.toStage'
            ])
            ->latest('updated_at')
            ->get();

        // Dynamic Pulse Projects
        $projects = Project::where('client_id', $user->client_id)
            ->with(['client', 'documents' => function($q) {
                $q->where('visibility', 'client_shared')->latest();
            }])->get();

        // Real-Time Intelligence Calculations
        $activeProjectIds = $projects->pluck('id');
        
        $avgHealth = $projects->avg('project_health_index') ?? 100;
        
        $totalRequiredDocs = ProjectDocument::whereIn('project_id', $activeProjectIds)
            ->where('category', 'requirement')
            ->count();
        $signedDocs = ProjectDocument::whereIn('project_id', $activeProjectIds)
            ->where('category', 'requirement')
            ->where('is_signed', true)
            ->count();
            
        $complianceRate = $totalRequiredDocs > 0 ? ($signedDocs / $totalRequiredDocs) * 100 : 100;

        // Recent Signal Feed
        $recentBugs = BugTicket::whereIn('project_id', $activeProjectIds)
            ->with([
                'stage',
                'module',
                'project',
                'comments.author',
                'assignee',
                'transitions.fromStage',
                'transitions.toStage'
            ])
            ->latest()
            ->limit(10)
            ->get();

        // Stats: Signal Mass by Module
        $bugsByModule = BugTicket::whereIn('project_id', $activeProjectIds)
            ->select('module_id', DB::raw('count(*) as total'))
            ->groupBy('module_id')
            ->with('module:id,name')
            ->get();

        $all_stages = WorkflowStage::whereHas('workflow', function($q) {
            $q->where('name', 'Bug Tracking');
        })->orderBy('stage_order')->get();

        $kbArticles = KnowledgeArticle::where('visibility', 'client_shared')
            ->where('is_published', true)
            ->latest()
            ->get();

        return Inertia::render('ClientPortal/Dashboard', [
            'client' => $user->client,
            'projects' => $projects,
            'documents' => ProjectDocument::whereIn('project_id', $activeProjectIds)->where('visibility', 'client_shared')->get(),
            'stats' => [
                'by_module' => $bugsByModule,
                'avg_health' => round($avgHealth),
                'compliance_rate' => round($complianceRate),
                'active_signals' => $recentBugs->count()
            ],
            'awaiting_verification' => $awaitingVerification,
            'recent_bugs' => $recentBugs,
            'kb_articles' => $kbArticles,
            'all_stages' => $all_stages,
            'notifications' => $user->unreadNotifications
        ]);
    }

    public function getProjectContext()
    {
        $user = Auth::guard('client')->user();
        $projects = $user->projects()->with('modules:id,project_id,name')->get();

        return response()->json([
            'projects' => $projects,
            'severities' => [
                ['id' => 'critical', 'name' => 'Blocker', 'desc' => 'I cannot work'],
                ['id' => 'high', 'name' => 'Major', 'desc' => 'It\'s causing big problems'],
                ['id' => 'medium', 'name' => 'Minor', 'desc' => 'It\'s annoying but I have a workaround'],
                ['id' => 'low', 'name' => 'Tweak', 'desc' => 'Low priority improvement']
            ]
        ]);
    }

    public function uploadMedia(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480' // 20MB
        ]);

        $file = $request->file('file');
        $path = $file->store('bugs/attachments/' . date('Y/m'), 'public');

        return response()->json([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_type' => $this->getFileType($file),
            'url' => asset('storage/' . $path)
        ]);
    }

    private function getFileType($file)
    {
        $mime = $file->getClientMimeType();
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        if ($mime === 'application/pdf') return 'pdf';
        return 'other';
    }

    public function createTicket()
    {
        $user = Auth::guard('client')->user();
        $projects = $user->projects()->with('modules')->get();

        return Inertia::render('ClientPortal/TicketCreator', [
            'projects' => $projects
        ]);
    }

    public function storeTicket(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'module_id' => 'nullable|exists:project_modules,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|in:critical,high,medium,low',
            'attachments' => 'nullable|array',
            'environment' => 'nullable|array'
        ]);

        $user = Auth::guard('client')->user();

        // Prevent unauthorized project submission
        if (!$user->projects->contains($request->project_id)) {
            abort(403, 'Unauthorized');
        }

        return DB::transaction(function() use ($request, $user) {
            // Initial Stage
            $stage = WorkflowStage::whereHas('workflow', function($q) {
                $q->where('name', 'Bug Tracking');
            })->orderBy('stage_order')->first();

            $bug = BugTicket::create([
                'project_id' => $request->project_id,
                'module_id' => $request->module_id,
                'subject' => $request->subject,
                'description' => $request->description,
                'severity' => $request->severity,
                'priority' => $request->severity === 'critical' ? 'urgent' : 'normal',
                'reporter_id' => $user->id,
                'reporter_type' => get_class($user),
                'workflow_stage_id' => $stage?->id ?? 1,
                'is_client_visible' => true,
                'environment_metadata' => $request->environment
            ]);

            // Attach Media
            if ($request->has('attachments')) {
                foreach ($request->attachments as $att) {
                    $bug->media()->create([
                        'file_path' => $att['path'] ?? $att['file_path'], // Check for varying keys
                        'file_type' => $att['file_type'],
                        'original_name' => $att['original_name']
                    ]);
                }
            }

            // Initial Transition (Pizza Tracker)
            if ($stage) {
                BugTicketTransition::create([
                    'bug_ticket_id' => $bug->id,
                    'to_stage_id' => $stage->id,
                    'actor_id' => $user->id,
                    'actor_type' => get_class($user)
                ]);
            }

            $this->logActivity($bug, 'created', 'Signal broadcasted from client terminal.');

            // Notify "In-Charge" (Project Stakeholders)
            $stakeholders = WorkAssignment::where('project_id', $bug->project_id)
                ->where('assignee_type', Employee::class)
                ->get()
                ->map(function($assign) {
                    return $assign->assignee->user ?? User::where('email', $assign->assignee->email)->first();
                })
                ->filter(function($user) {
                    return $user && ($user->hasRole('Admin') || $user->hasRole('Manager'));
                })
                ->unique('id');
            
            foreach ($stakeholders as $managerUser) {
                $managerUser->notify(new BugAssignedNotification($bug));
            }

            return redirect()->route('portal.dashboard')->with('success', 'Ticket created successfully.');
        });
    }

    public function getTicketTimeline(BugTicket $bug)
    {
        $bug->load(['transitions.toStage', 'transitions.fromStage']);
        
        $allStages = WorkflowStage::whereHas('workflow', function($q) {
            $q->where('name', 'Bug Tracking');
        })->orderBy('stage_order')->get();

        return response()->json([
            'ticket' => $bug,
            'transitions' => $bug->transitions,
            'all_stages' => $allStages
        ]);
    }

    public function verifyTicket(Request $request, BugTicket $ticket)
    {
        // Check access
        $user = Auth::guard('client')->user();
        if (!$user->projects->contains($ticket->project_id)) abort(403);

        $action = $request->input('action'); // 'approve', 'reject'
        $oldStageName = $ticket->stage->name ?? 'Unknown';

        return DB::transaction(function() use ($ticket, $action, $user, $oldStageName) {
            if ($action === 'approve') {
                // Move to Final Closed Stage
                $finalStage = WorkflowStage::where('is_final', true)
                    ->whereHas('workflow', function($q) { $q->where('name', 'Bug Tracking'); })
                    ->first();
                
                if ($finalStage) {
                    $ticket->update([
                        'workflow_stage_id' => $finalStage->id,
                        'is_client_visible' => true,
                        'resolved_at' => now(), // Phase 10 Metrics
                        'system_closed_at' => now()
                    ]);

                    BugTicketTransition::create([
                        'bug_ticket_id' => $ticket->id,
                        'from_stage_id' => $ticket->getOriginal('workflow_stage_id'),
                        'to_stage_id' => $finalStage->id,
                        'actor_id' => $user->id,
                        'actor_type' => get_class($user)
                    ]);

                    $this->logActivity($ticket, 'verified', 'Client confirmed resolution integrity.');
                }
            } elseif ($action === 'reject') {
                // Reopen logic: Move back to Triage or the first stage
                $reopenStage = WorkflowStage::where('name', 'Triage')
                    ->whereHas('workflow', function($q) { $q->where('name', 'Bug Tracking'); })
                    ->first();
                
                if (!$reopenStage) {
                    $reopenStage = WorkflowStage::whereHas('workflow', function($q) { $q->where('name', 'Bug Tracking'); })
                        ->orderBy('stage_order')
                        ->first();
                }

                if ($reopenStage) {
                    $ticket->update(['workflow_stage_id' => $reopenStage->id]);

                    BugTicketTransition::create([
                        'bug_ticket_id' => $ticket->id,
                        'from_stage_id' => $ticket->getOriginal('workflow_stage_id'),
                        'to_stage_id' => $reopenStage->id,
                        'actor_id' => $user->id,
                        'actor_type' => get_class($user)
                    ]);

                    $this->logActivity($ticket, 'reopened', 'Client rejected resolution. Integrity check failed.');
                }
            }

            // Notify Assignee of the verdict
            $assigneeUser = $ticket->assignee->user ?? null;
            if ($assigneeUser) {
                $assigneeUser->notify(new BugStageChangedNotification($ticket, $oldStageName, $ticket->stage->name));
            }

            return back();
        });
    }

    public function storeRating(Request $request, BugTicket $ticket)
    {
        $user = Auth::guard('client')->user();
        if (!$user->projects->contains($ticket->project_id)) abort(403);

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string'
        ]);

        $ticket->update([
            'rating' => $request->rating,
            'rating_feedback' => $request->feedback
        ]);

        $this->logActivity($ticket, 'rated', "Satisfactory rating of {$request->rating}/5 logged by client.");

        // Notify "In-Charge" (Project Stakeholders) about the feedback
        $stakeholders = WorkAssignment::where('project_id', $ticket->project_id)
            ->where('assignee_type', Employee::class)
            ->get()
            ->map(function($assign) {
                return $assign->assignee->user ?? User::where('email', $assign->assignee->email)->first();
            })
            ->filter(function($user) {
                return $user && ($user->hasRole('Admin') || $user->hasRole('Manager'));
            })
            ->unique('id');
        
        foreach ($stakeholders as $managerUser) {
            $managerUser->notify(new BugRatingReceivedNotification($ticket));
        }

        return back()->with('success', 'Thank you for your feedback!');
    }

    public function getLivePulse()
    {
        $user = Auth::guard('client')->user();
        $projectIds = $user->projects->pluck('id');

        // Combined activity feed for the ticker
        $activity = BugTicket::whereIn('project_id', $projectIds)
            ->with(['stage', 'module'])
            ->latest('updated_at')
            ->limit(15)
            ->get()
            ->map(function($bug) {
                return [
                    'id' => $bug->id,
                    'label' => $bug->subject,
                    'status' => $bug->stage->name ?? 'Update',
                    'time' => $bug->updated_at->diffForHumans()
                ];
            });

        return response()->json($activity);
    }

    public function getDevicePresets()
    {
        $user = Auth::guard('client')->user();
        $presets = \App\Models\ClientEnvironmentPreset::where('client_id', $user->id)
            ->where('client_type', get_class($user))
            ->get();

        return response()->json($presets);
    }

    public function storeDevicePreset(Request $request)
    {
        $user = Auth::guard('client')->user();
        
        $request->validate([
            'device_name' => 'required|string|max:100',
            'metadata' => 'required|array'
        ]);

        $preset = \App\Models\ClientEnvironmentPreset::create([
            'client_id' => $user->id,
            'client_type' => get_class($user),
            'device_name' => $request->device_name,
            'metadata' => $request->metadata
        ]);

        return response()->json($preset);
    }

    public function knowledgeVault()
    {
        $user = Auth::guard('client')->user();
        $projectIds = $user->projects->pluck('id');

        $categories = VaultCategory::whereIn('project_id', $projectIds)
            ->with(['articles' => function($q) {
                $q->where('is_client_visible', true)->orderBy('order');
            }])
            ->orderBy('order')
            ->get();

        $featuredArticles = VaultArticle::whereHas('category', function($q) use ($projectIds) {
                $q->whereIn('project_id', $projectIds);
            })
            ->where('is_featured', true)
            ->where('is_client_visible', true)
            ->limit(3)
            ->get();

        return response()->json([
            'categories' => $categories,
            'featured' => $featuredArticles
        ]);
    }

    public function getArticle($slug)
    {
        $user = Auth::guard('client')->user();
        $projectIds = $user->projects->pluck('id');

        $article = VaultArticle::where('slug', $slug)
            ->where('is_client_visible', true)
            ->whereHas('category', function($q) use ($projectIds) {
                $q->whereIn('project_id', $projectIds);
            })
            ->with('category.project')
            ->firstOrFail();

        return response()->json($article);
    }

    public function deleteDevicePreset($id)
    {
        $user = Auth::guard('client')->user();
        $preset = \App\Models\ClientEnvironmentPreset::where('id', $id)
            ->where('client_id', $user->id)
            ->where('client_type', get_class($user))
            ->firstOrFail();

        $preset->delete();

        return response()->json(['message' => 'Preset deleted successfully']);
    }

    public function uploadDocument(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:requirement,sign_off,general,financial',
            'file' => 'required|file|max:20480', // 20MB
            'visibility' => 'required|in:public,internal,management_only,client_shared'
        ]);

        $user = Auth::guard('client')->user();
        if (!$user->projects->contains($project->id)) {
            abort(403, 'Unauthorized project access.');
        }

        $path = $request->file('file')->store('project-documents/' . $project->id, 'public');

        $doc = ProjectDocument::create([
            'project_id' => $project->id,
            'uploader_id' => $user->id,
            'uploader_type' => get_class($user),
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'file_path' => $path,
            'mime_type' => $request->file('file')->getClientMimeType(),
            'file_size' => $request->file('file')->getSize(),
            'visibility' => $request->visibility,
            'version_number' => 1,
            'is_current' => true
        ]);

        $this->logActivity(BugTicket::where('project_id', $project->id)->first() ?? new BugTicket(), 'upload', 'Stakeholder uploaded a new governance artifact.');

        return back()->with('success', 'Document uploaded successfully.')->setStatusCode(303);
    }

    public function signOffDocument(Request $request, ProjectDocument $document)
    {
        $user = Auth::guard('client')->user();
        if (!$user->projects->contains($document->project_id)) {
            abort(403);
        }

        if ($document->category !== 'requirement') {
            return back()->with('error', 'Only requirement documents can be signed off.');
        }

        $document->update([
            'is_signed' => true,
            'signed_at' => now(),
            'signed_by' => $user->id // Note: This stores ClientUser ID. We might need polymorphic signedBy too, but user didn't ask yet.
        ]);

        return back()->with('success', 'Document signed off successfully.')->setStatusCode(303);
    }

    public function storeComment(Request $request, BugTicket $bug)
    {
        $request->validate([
            'body' => 'required|string',
            'is_public' => 'boolean'
        ]);

        $user = Auth::guard('client')->user();

        // Check if the signal belongs to the client's project mass
        if (!$user->projects->contains($bug->project_id)) {
            abort(403, 'Unauthorized signal access.');
        }

        $comment = $bug->comments()->create([
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'body' => $request->body,
            'is_public' => true,
            'attachments' => $request->attachments ?? []
        ]);

        $this->logActivity($bug, 'commented', 'Stakeholder broadcasted a new signal.');

        return response()->json($comment->load('author'));
    }

    private function logActivity(BugTicket $bug, $type, $description, $details = null)
    {
        $bug->activities()->create([
            'actor_id' => Auth::guard('client')->id(),
            'actor_type' => get_class(Auth::guard('client')->user()),
            'activity_type' => $type,
            'description' => $description,
            'details' => $details
        ]);
    }
}
