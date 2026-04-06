<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SprintLog;

class SprintController extends Controller
{
    public function index(Project $project)
    {
        return $project->sprints()->withCount('tasks')->get();
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'goal' => 'nullable|string'
        ]);

        $project->sprints()->create($validated);
        
        return redirect()->back()->with('success', 'Sprint created successfully.')->setStatusCode(303);
    }

    public function show(Project $project, Sprint $sprint)
    {
        return $sprint->load('tasks.assignees');
    }

    public function update(Request $request, Project $project, Sprint $sprint)
    {
        $validated = $request->validate([
            'name' => 'string',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
            'goal' => 'nullable|string',
            'status' => 'in:planned,active,completed'
        ]);

        $sprint->update($validated);
        
        return redirect()->back()->with('success', 'Sprint updated successfully.')->setStatusCode(303);
    }

    public function start(Request $request, Project $project, Sprint $sprint)
    {
        // Close any other active sprints?
        // User requested ability to have multiple active sprints. Pausing logic removed.
        // $project->sprints()->where('status', 'active')->update(['status' => 'planned']);

        $sprint->update(['status' => 'active']);

        // Log Start
        $sprint->logs()->create([
            'user_id' => auth()->id(),
            'action' => 'start',
            'details' => ['started_at' => now()]
        ]);
        
        return redirect()->back()->with('success', 'Sprint started!')->setStatusCode(303);
    }

    public function complete(Request $request, Project $project, Sprint $sprint)
    {
        $request->validate([
            'move_incomplete_to_sprint_id' => 'nullable|exists:sprints,id',
            'move_incomplete_to_backlog' => 'boolean'
        ]);

        DB::transaction(function () use ($sprint, $request) {
            $sprint->update(['status' => 'completed']);
            
            // Logic: Find incomplete tasks
            // We assume "Done" type stages are completed.
            $incompleteStages = $sprint->project->stages()->whereIn('type', ['backlog', 'todo', 'doing', 'review'])->pluck('id');
            $completedStages = $sprint->project->stages()->where('type', 'done')->pluck('id');

            $totalPoints = $sprint->tasks()->sum('scrum_points');
            $completedPoints = $sprint->tasks()->whereIn('stage_id', $completedStages)->sum('scrum_points');

            $incompleteTasks = $sprint->tasks()->whereIn('stage_id', $incompleteStages)->get();

            // Log Completion Stats
            $sprint->logs()->create([
                'user_id' => auth()->id(),
                'action' => 'complete',
                'details' => [
                    'completed_at' => now(),
                    'total_points' => $totalPoints,
                    'completed_points' => $completedPoints,
                    'incomplete_count' => $incompleteTasks->count()
                ]
            ]);

            if ($request->move_incomplete_to_sprint_id) {
                foreach ($incompleteTasks as $task) {
                    $task->update(['sprint_id' => $request->move_incomplete_to_sprint_id]);
                }
            } elseif ($request->move_incomplete_to_backlog) {
                foreach ($incompleteTasks as $task) {
                    $task->update(['sprint_id' => null]);
                }
            }
        });

        return redirect()->back()->with('success', 'Sprint completed.')->setStatusCode(303);
    }

    public function destroy(Project $project, Sprint $sprint)
    {
        // Explicitly unassign tasks to Backlog
        $sprint->tasks()->update(['sprint_id' => null]);
        
        $sprint->delete();
        return redirect()->back()->with('success', 'Sprint deleted.')->setStatusCode(303);
    }
}
