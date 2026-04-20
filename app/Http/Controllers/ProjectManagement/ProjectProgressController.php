<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectProgressController extends Controller
{
    protected $logger;

    public function __construct(\App\Services\Infrastructure\LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function index(Request $request)
    {
        $projects = Project::visibleTo($request->user())
            ->with(['client' => function($q) {
                $q->select('id', 'name');
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'code' => $p->code,
                    'client' => $p->client->name ?? 'N/A',
                    'manual_progress_percentage' => (int)($p->manual_progress_percentage ?? 0),
                    'manual_status_label' => $p->manual_status_label ?? 'Planning',
                    'status' => $p->status,
                ];
            });

        return Inertia::render('Project/Progress/ManualUpdate', [
            'projects' => $projects
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'manual_progress_percentage' => 'required|integer|min:0|max:100',
            'manual_status_label' => 'required|string|max:100',
        ]);

        $oldProgress = $project->manual_progress_percentage;
        $oldStatus = $project->manual_status_label;

        $project->update($validated);

        $this->logger->log(
            'project_management',
            'manual_progress_update',
            "Manual progress updated for project: {$project->name}",
            [
                'project_id' => $project->id,
                'old' => ['progress' => $oldProgress, 'status' => $oldStatus],
                'new' => $validated
            ]
        );

        return back()->with('success', "Progress updated for {$project->name}")->setStatusCode(303);
    }
}
