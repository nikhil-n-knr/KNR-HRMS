<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\Infrastructure\LoggerService;

class ProjectController extends Controller
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function index(Request $request)
    {
        $query = Project::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
        }

        $projects = $query->with('manager')->orderBy('created_at', 'desc')->paginate(10);
        $managers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Manager', 'Admin']);
        })->get(['id', 'name']);

        if ($request->wantsJson()) {
            return response()->json($projects);
        }

        return Inertia::render('Admin/Projects/Index', [
            'projects' => $projects,
            'managers' => $managers
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:projects,code',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:Active,On Hold,Completed,Archived',
            'manager_id' => 'nullable|exists:users,id',
            'is_locked' => 'nullable|boolean',
            'plan_lock_recipients' => 'nullable|array',
            'plan_lock_recipients.*' => 'exists:users,id'
        ]);

        $project = Project::create($validated);

        $this->logger->log('project', 'create', "Created project: {$project->name}", auth()->id());

        return back()->with('success', 'Project created successfully.')
            ->setStatusCode(303);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'nullable|string|max:50|unique:projects,code,' . $project->id,
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'sometimes|in:Active,On Hold,Completed,Archived',
            'manager_id' => 'nullable|exists:users,id',
            'is_locked' => 'nullable|boolean',
            'plan_lock_recipients' => 'nullable|array',
            'plan_lock_recipients.*' => 'exists:users,id'
        ]);
        
        // Handle is_locked explicitly if coming from axios/toggle
        if ($request->has('is_locked'))  $project->is_locked = $request->is_locked;
        if ($request->has('plan_lock_recipients')) $project->plan_lock_recipients = $request->plan_lock_recipients;

        $project->update($validated);
        
        $this->logger->log('project', 'update', "Updated project: {$project->name}", auth()->id());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'project' => $project->fresh()]);
        }

        return back()->with('success', 'Project updated successfully.')
            ->setStatusCode(303);
    }

    public function destroy(Project $project)
    {
        $project->delete();
        $this->logger->log('project', 'delete', "Deleted project: {$project->name}", auth()->id());
        return back()->with('success', 'Project deleted.');
    }
}
