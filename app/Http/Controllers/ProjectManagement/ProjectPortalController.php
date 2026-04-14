<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProjectPortalController extends Controller
{
    /**
     * Main Portal View (Overview)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Ensure user is linked to a client
        if (!$user->client_id) {
            return redirect()->route('dashboard')->with('error', 'No client association found.');
        }

        $client = Client::with(['projects.modules', 'users'])->findOrFail($user->client_id);
        
        $projects = $client->projects;
        $projectIds = $projects->pluck('id');

        // Load Bugs for the Partial
        $bugs = \App\Models\BugTicket::with(['project', 'stage', 'assignee'])
            ->whereIn('project_id', $projectIds)
            ->where('is_client_visible', true)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Load Documents
        $documents = ProjectDocument::with(['uploader', 'project'])
            ->whereIn('project_id', $projectIds)
            ->where(function($q) {
                $q->where('visibility', 'client_shared')
                  ->orWhere('visibility', 'public');
            })
            ->get();

        // Load Stages for filtering
        $stages = \App\Models\WorkflowStage::whereHas('workflow', function($q) {
            $q->where('name', 'Bug Tracking');
        })->orderBy('stage_order')->get();

        return Inertia::render('Project/BugTracker/ExternalPortal', [
            'client' => $client,
            'projects' => $projects,
            'bugs' => $bugs,
            'documents' => $documents,
            'stages' => $stages,
            'open_critical_count' => \App\Models\BugTicket::whereIn('project_id', $projectIds)
                ->where('severity', 'critical')
                ->where('is_client_visible', true)
                ->count(),
            'stats' => $this->getGlobalStats($client)
        ]);
    }

    /**
     * Upload Document (Requirement / Sign-off)
     */
    public function uploadDocument(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:requirement,sign_off,general,financial',
            'file' => 'required|file|max:10240', // 10MB
            'visibility' => 'required|in:public,internal,management_only,client_shared'
        ]);

        $path = $request->file('file')->store('project-documents/' . $project->id, 'public');

        // Versioning Logic
        $version = 1;
        $parentId = null;
        if ($request->has('parent_id')) {
            $parent = ProjectDocument::find($request->parent_id);
            if ($parent) {
                $version = $parent->version_number + 1;
                $parentId = $parent->parent_id ?? $parent->id;
                // Mark previous 'current' as false
                ProjectDocument::where('id', $parentId)->orWhere('parent_id', $parentId)->update(['is_current' => false]);
            }
        }

        $doc = ProjectDocument::create([
            'project_id' => $project->id,
            'uploader_id' => Auth::id(),
            'uploader_type' => get_class(Auth::user()),
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'file_path' => $path,
            'mime_type' => $request->file('file')->getClientMimeType(),
            'file_size' => $request->file('file')->getSize(),
            'visibility' => $request->visibility,
            'version_number' => $version,
            'parent_id' => $parentId,
            'is_current' => true
        ]);

        event(new \App\Events\DocumentActionEvent($doc, 'upload'));

        return back()->with('success', 'Document uploaded successfully.')->setStatusCode(303);
    }

    /**
     * Digital Sign-off for Requirements
     */
    public function signOffDocument(Request $request, ProjectDocument $document)
    {
        // Ensure user belongs to the client and document is a requirement
        if (Auth::user()->client_id !== $document->project->client_id) {
            abort(403);
        }

        if ($document->category !== 'requirement') {
            return back()->with('error', 'Only requirement documents can be signed off.');
        }

        $document->update([
            'is_signed' => true,
            'signed_at' => now(),
            'signed_by' => Auth::id()
        ]);

        event(new \App\Events\DocumentActionEvent($document, 'sign_off'));

        return back()->with('success', 'Document signed off successfully.')->setStatusCode(303);
    }

    /**
     * Update Manual Progress (Management Only)
     */
    public function updateGovernance(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $oldPercentage = $project->manual_progress_percentage;
        $newData = $request->only([
            'manual_progress_percentage',
            'manual_status_label',
            'project_health_index'
        ]);

        $project->update($newData);

        // Audit Logging
        \App\Models\ProjectStatusLog::create([
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'field_name' => 'manual_progress_percentage',
            'old_value' => $oldPercentage,
            'new_value' => $newData['manual_progress_percentage'],
            'reason' => $request->input('reason', 'Manual Override'),
            'ip_address' => $request->ip()
        ]);

        // Broadcast Real-time
        event(new \App\Events\ProjectProgressUpdated($project, Auth::user(), $request->input('reason')));

        return back()->with('success', 'Project governance updated.')->setStatusCode(303);
    }

    /**
     * Change History for Management
     */
    public function getLogHistory(Project $project)
    {
        $logs = \App\Models\ProjectStatusLog::with('user')
            ->where('project_id', $project->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($logs);
    }

    private function getGlobalStats($client)
    {
        // Aggregate stats for ApexCharts
        return [
            'total_projects' => $client->projects->count(),
            'active_sprints' => 0, // Placeholder
            'health_average' => $client->projects->avg('project_health_index') ?? 100,
            'completion_average' => $client->projects->avg('manual_progress_percentage') ?? 0
        ];
    }
}
