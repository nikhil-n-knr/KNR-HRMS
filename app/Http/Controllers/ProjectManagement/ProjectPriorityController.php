<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectPriority;
use Illuminate\Http\Request;

class ProjectPriorityController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string',
            'order' => 'integer'
        ]);

        $project->priorities()->create([
            'name' => $validated['name'],
            'color' => $validated['color'],
            'order' => $validated['order'] ?? $project->priorities()->count()
        ]);

        return redirect()->back()->with('success', 'Priority created successfully.')->setStatusCode(303);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, ProjectPriority $priority)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'color' => 'sometimes|required|string',
            'order' => 'integer'
        ]);

        $priority->update($validated);

        return redirect()->back()->with('success', 'Priority updated successfully.')->setStatusCode(303);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, ProjectPriority $priority)
    {
        // Optional: Check if used by tasks? 
        // For now, allow delete. Tasks with this string will preserve the string but lose the color mapping.
        $priority->delete();

        return redirect()->back()->with('success', 'Priority deleted successfully.')->setStatusCode(303);
    }
}
