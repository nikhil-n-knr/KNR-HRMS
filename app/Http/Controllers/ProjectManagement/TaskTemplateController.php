<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\TaskTemplate;
use Illuminate\Http\Request;

class TaskTemplateController extends Controller
{
    public function index(Project $project)
    {
        return \Inertia\Inertia::render('Project/Template/Index', [
            'project' => $project,
            'templates' => $project->taskTemplates()->with('creator')->get()
        ]);
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|string',
            'scrum_points' => 'nullable|integer',
            'checklists' => 'nullable|array' // array of strings
        ]);

        $template = $project->taskTemplates()->create([
            ...$validated,
            'created_by' => auth()->id()
        ]);

        return response()->json($template);
    }
}
