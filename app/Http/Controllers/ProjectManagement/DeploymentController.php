<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\BugTicket;
use App\Models\ProjectManagement\DeploymentRound;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeploymentController extends Controller
{
    public function index(Request $request)
    {
        $projectId = $request->project_id;
        
        $rounds = DeploymentRound::when($projectId, function($q) use ($projectId) {
                return $q->where('project_id', $projectId);
            })
            ->withCount('tickets')
            ->orderBy('created_at', 'desc')
            ->get();

        if (request()->wantsJson()) {
            return response()->json($rounds);
        }

        return \Inertia\Inertia::render('Project/Deployments/Pulse', [
            'rounds' => $rounds,
            'projects' => \App\Models\Project::select('id', 'name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'version' => 'required|string',
            'notes' => 'nullable|string',
            'ticket_ids' => 'nullable|array',
            'ticket_ids.*' => 'exists:bug_tickets,id'
        ]);

        $round = DeploymentRound::create([
            'project_id' => $validated['project_id'],
            'version' => $validated['version'],
            'notes' => $validated['notes'],
            'status' => 'planning'
        ]);

        if (!empty($validated['ticket_ids'])) {
            BugTicket::whereIn('id', $validated['ticket_ids'])->update(['deployment_round_id' => $round->id]);
        }

        return response()->json($round->loadCount('tickets'));
    }

    public function updateStatus(Request $request, DeploymentRound $round)
    {
        $validated = $request->validate([
            'status' => 'required|in:planning,staging,production'
        ]);

        $data = ['status' => $validated['status']];
        if ($validated['status'] === 'production') {
            $data['deployed_at'] = now();
        }

        $round->update($data);

        return response()->json($round);
    }
}
