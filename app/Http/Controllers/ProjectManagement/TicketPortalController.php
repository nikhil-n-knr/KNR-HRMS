<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\BugTicket;
use App\Models\BugAttachment;
use App\Models\BugTicketTransition;
use App\Models\Project;
use App\Models\WorkflowStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketPortalController extends Controller
{
    public function getProjectContext(Request $request)
    {
        $user = Auth::user();
        $projects = Project::with('modules:id,project_id,name')
            ->when($user->hasRole('Client'), function($q) use ($user) {
                return $q->where('client_id', $user->client_id);
            })
            ->get();

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

    public function storeTicket(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'module_id' => 'required|exists:project_modules,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|string',
            'attachments' => 'nullable|array',
            'environment' => 'nullable|array'
        ]);

        return DB::transaction(function() use ($request) {
            $user = Auth::user();
            
            // Initial Stage
            $stage = WorkflowStage::orderBy('stage_order')->first();

            $ticket = BugTicket::create([
                'project_id' => $request->project_id,
                'module_id' => $request->module_id,
                'subject' => $request->subject,
                'description' => $request->description,
                'severity' => $request->severity,
                'priority' => $request->severity === 'critical' ? 'urgent' : 'normal',
                'reporter_id' => $user->id,
                'reporter_type' => get_class($user),
                'workflow_stage_id' => $stage?->id,
                'is_client_visible' => true,
                'environment_metadata' => $request->environment
            ]);

            // Attach Media
            if ($request->has('attachments')) {
                foreach ($request->attachments as $att) {
                    $ticket->media()->create([
                        'file_path' => $att['path'],
                        'file_type' => $att['file_type'],
                        'original_name' => $att['original_name']
                    ]);
                }
            }

            // Initial Transition
            if ($stage) {
                BugTicketTransition::create([
                    'bug_ticket_id' => $ticket->id,
                    'to_stage_id' => $stage->id,
                    'actor_id' => $user->id,
                    'actor_type' => get_class($user)
                ]);
            }

            return response()->json($ticket, 201);
        });
    }

    public function getTicketTimeline(BugTicket $bug)
    {
        $bug->load(['transitions.toStage', 'transitions.fromStage', 'transitions.user']);
        
        // Fetch all potential stages for the animation nodes
        $allStages = WorkflowStage::whereHas('workflow', function($q) {
            $q->where('name', 'Bug Tracking');
        })->orderBy('stage_order')->get();

        return response()->json([
            'ticket' => $bug,
            'transitions' => $bug->transitions,
            'all_stages' => $allStages
        ]);
    }
}
