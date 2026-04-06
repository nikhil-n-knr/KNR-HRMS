<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\BugTicket;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ClientDashboardController extends Controller
{
    public function index(Request $request)
    {
        $clientUser = Auth::guard('client')->user();
        if (!$clientUser) {
            return redirect()->route('client.login');
        }

        $clientId = $clientUser->client_id;

        // 1. Get Projects mapped specifically to this USER
        $projects = $clientUser->projects()->select('projects.id', 'projects.name')->get();
        $projectIds = $projects->pluck('id');

        // 2. Base Query for Bugs
        $query = BugTicket::with(['project', 'module', 'stage', 'reporter', 'assignee'])
            ->whereIn('project_id', $projectIds)
            ->where('is_client_visible', true);

        // 3. Stats
        $activeBugs = (clone $query)->whereHas('stage', function($q) {
            $q->where('is_final', false);
        })->count();

        $resolvedBugs = (clone $query)->whereHas('stage', function($q) {
            $q->where('is_final', true);
        })->count();

        $criticalCount = (clone $query)->where('severity', 'critical')
            ->whereHas('stage', function($q) {
                $q->where('is_final', false);
            })->count();

        // 4. Action Required (Bugs in Stages with requires_verification = true)
        $actionRequired = (clone $query)->whereHas('stage', function($q) {
            $q->where('requires_verification', true)
              ->where('is_final', false);
        })->get();

        // 5. Bugs By Module (Doughnut Chart Data)
        $bugsByModule = BugTicket::select('project_modules.name as label', DB::raw('count(*) as count'))
            ->join('project_modules', 'bug_tickets.module_id', '=', 'project_modules.id')
            ->whereIn('bug_tickets.project_id', $projectIds)
            ->where('bug_tickets.is_client_visible', true)
            ->groupBy('project_modules.name')
            ->get();

        // 6. Resolution Velocity (Line Graph - Last 7 Days)
        $velocity = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = BugTicket::whereIn('project_id', $projectIds)
                ->where('is_client_visible', true)
                ->whereDate('resolved_at', $date)
                ->count();
            $velocity[] = ['date' => $date, 'count' => $count];
        }

        return Inertia::render('Project/BugTracker/ClientDashboard', [
            'stats' => [
                'active' => $activeBugs,
                'resolved' => $resolvedBugs,
                'critical' => $criticalCount
            ],
            'actionRequired' => $actionRequired,
            'bugsByModule' => $bugsByModule,
            'velocity' => $velocity,
            'projects' => $projects,
            'recentBugs' => (clone $query)->orderBy('updated_at', 'desc')->take(100)->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|in:critical,high,medium,low',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,txt|max:10240',
        ]);

        $clientUser = Auth::guard('client')->user();
        if ($request->project_id) {
            // Security: Verify project belongs to user
            if (!$clientUser->projects()->where('projects.id', $request->project_id)->exists()) {
                abort(403);
            }
        }

        $bug = DB::transaction(function() use ($request, $clientUser) {
            // ... (existing stage/attachment logic)
            $stage = \App\Models\WorkflowStage::whereHas('workflow', function($q) {
                $q->where('entity_type', BugTicket::class);
            })->orderBy('stage_order')->first();

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

            $ticket = BugTicket::create([
                'project_id' => $request->project_id,
                'module_id' => $request->module_id,
                'subject' => $request->subject,
                'description' => $request->description,
                'severity' => $request->severity,
                'priority' => 'normal',
                'reporter_id' => $clientUser->id,
                'reporter_type' => get_class($clientUser),
                'workflow_stage_id' => $stage ? $stage->id : null,
                'is_client_visible' => true,
                'attachments' => $attachmentPaths,
                'steps_to_reproduce' => $request->steps_to_reproduce ?? 'Submitted via Client Portal'
            ]);

            // Notify In-Charge (Stakeholders)
            $stakeholders = \App\Models\WorkAssignment::where('project_id', $ticket->project_id)
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
                 \App\Models\Notification::create([
                    'user_id' => $managerUser->id,
                    'type' => 'client_bug_reported',
                    'title' => '🚨 Client Reported Bug: #' . $ticket->id,
                    'message' => 'A client has reported a new issue in project ' . $ticket->project->name,
                    'action_url' => route('bugs.show', $ticket->id),
                    'ref_id' => $ticket->id,
                    'ref_type' => BugTicket::class
                ]);
            }

            return $ticket;
        });

        return back()->with('success', 'Ticket reported successfully.');
    }

    public function show(BugTicket $bug)
    {
        $this->authorizeAction($bug);

        $bug->load(['project', 'module', 'stage', 'comments' => function($q) {
            $q->where('is_public', true)->with('author');
        }, 'attachments', 'reporter']);

        return Inertia::render('Project/BugTracker/ClientTicketView', [
            'bug' => $bug
        ]);
    }

    public function verifyTicket(Request $request, BugTicket $bug)
    {
        $this->authorizeAction($bug);

        // Security Gap Check: Is this ticket actually waiting for verification?
        if (!$bug->stage || !$bug->stage->requires_verification) {
            return back()->with('error', 'This ticket is not currently pending your verification.');
        }

        $request->validate([
            'status' => 'required|in:verify,reject',
            'note' => 'nullable|string'
        ]);

        if ($request->status === 'verify') {
            $finalStage = \App\Models\WorkflowStage::where('is_final', true)
                ->whereHas('workflow', function($q) {
                    $q->where('entity_type', BugTicket::class);
                })->first();

            $bug->update([
                'workflow_stage_id' => $finalStage?->id,
                'resolved_at' => now()
            ]);

            $bug->comments()->create([
                'user_id' => null, // Or map to client_user if we add morph relation
                'body' => "<strong>Client Verified:</strong> " . ($request->note ?? 'Verified & Closed.')
            ]);
        } else {
            // Re-open: Move back to Triage or "In Development"
            $triageStage = \App\Models\WorkflowStage::where('stage_order', 1)
                ->whereHas('workflow', function($q) {
                    $q->where('entity_type', BugTicket::class);
                })->first();

            $bug->update([
                'workflow_stage_id' => $triageStage?->id,
            ]);

            $bug->comments()->create([
                'user_id' => null,
                'body' => "<strong>Client Rejected:</strong> " . ($request->note ?? 'Issue still persists.')
            ]);
        }

        return redirect()->route('client.dashboard')->with('success', 'Ticket status updated.');
    }

    public function addComment(Request $request, BugTicket $bug)
    {
        $this->authorizeAction($bug);

        $request->validate([
            'body' => 'required|string'
        ]);

        $clientUser = Auth::guard('client')->user();

        $bug->comments()->create([
            'user_id' => null, // Mapping to null since they aren't employees, or we could add author polymorphic
            'body' => $request->body,
            'is_public' => true
        ]);

        return back()->with('success', 'Comment posted.');
    }

    private function authorizeAction(BugTicket $bug)
    {
        $clientUser = Auth::guard('client')->user();
        // Check if the project is mapped to the specific ClientUser
        if (!$clientUser->projects()->where('projects.id', $bug->project_id)->exists()) {
            abort(403);
        }
    }
}
