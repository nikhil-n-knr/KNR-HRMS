<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectStage;
use Illuminate\Http\Request;

class ProjectStageController extends Controller
{
    public function index(Project $project)
    {
        return $project->stages;
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:backlog,todo,doing,review,done',
            'color' => 'required|string',
            'order' => 'integer',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:employees,id'
        ]);

        $stage = $project->stages()->create([
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
            'type' => $validated['type'],
            'color' => $validated['color'],
            'order' => $validated['order'] ?? 0
        ]);

        if ($request->has('assignees')) {
            $stage->assignees()->sync($request->input('assignees', []));
        }

        return redirect()->back()->with('success', 'Stage created successfully.')->setStatusCode(303);
    }

    public function update(Request $request, Project $project, ProjectStage $stage)
    {
        if ($stage->project_id !== $project->id) abort(404);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'type' => 'in:backlog,todo,doing,review,done',
            'color' => 'string',
            'order' => 'integer',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:employees,id'
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        $stage->update($validated);

        if ($request->has('assignees')) {
            $stage->assignees()->sync($request->input('assignees', []));
        }
        
        return redirect()->back()->with('success', 'Stage updated successfully.')->setStatusCode(303);
    }

    public function destroy(Project $project, ProjectStage $stage)
    {
        if ($stage->project_id !== $project->id) abort(404);
        
        $taskCount = $stage->tasks()->count();
        if ($taskCount > 0) {
            // Find a fallback stage (Backlog or To Do)
            $fallbackStage = $project->stages()
                ->where('id', '!=', $stage->id)
                ->whereIn('type', ['backlog', 'todo'])
                ->orderBy('order')
                ->first();

            if (!$fallbackStage) {
                return redirect()->back()->withErrors(['stage' => 'Cannot delete stage with tasks. No fallback stage found.']);
            }

            // Move tasks
            $stage->tasks()->update(['stage_id' => $fallbackStage->id]);
        }
        
        $stage->delete();
        
        return redirect()->back()->with('success', 'Stage deleted successfully. Tasks moved to ' . ($fallbackStage->name ?? 'Backlog') . '.')->setStatusCode(303);
    }
    
    public function reorder(Request $request, Project $project)
    {
        $request->validate([
            'stages' => 'required|array',
            'stages.*.id' => 'required|exists:project_stages,id',
            'stages.*.order' => 'required|integer'
        ]);

        foreach ($request->stages as $item) {
            ProjectStage::where('id', $item['id'])
                        ->where('project_id', $project->id)
                        ->update(['order' => $item['order']]);
        }
        
        return redirect()->back()->setStatusCode(303);
    }
}
