<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProjectModuleController extends Controller
{
    /**
     * Get the module tree for a project.
     */
    /**
     * Get the module tree for a project.
     * Optimize: Load all at once and build tree in memory to avoid N+1 and recursion limits.
     */
    public function index(Project $project)
    {
        // 1. Fetch all modules for this project flat
        $allModules = $project->modules()->orderBy('name')->get();

        // 2. Build Tree in Memory
        $lookup = [];
        $tree = [];

        // Initialize lookup and children container
        foreach ($allModules as $module) {
            $module->setRelation('children', collect([])); // Use Eloquent relation for consistent JSON serialization
            $lookup[$module->id] = $module;
        }

        // Link children to parents with Cycle Detection
        foreach ($allModules as $module) {
            // Check for cycles by walking up the parent chain
            $isCycle = false;
            $current = $module;
            $seen = [$module->id];
            
            // Limit depth check to prevent infinite loops during check itself (e.g. 100 levels)
            $depthCheck = 0;
            while ($current->parent_id && isset($lookup[$current->parent_id]) && $depthCheck < 100) {
                if ($current->parent_id == $module->id || in_array($current->parent_id, $seen)) {
                    $isCycle = true;
                    break;
                }
                $seen[] = $current->parent_id;
                $current = $lookup[$current->parent_id];
                $depthCheck++;
            }

            if ($isCycle) {
                // If cycle detected, treat as root or orphan to prevent crash
                 $tree[] = $module;
                 continue;
            }

            if ($module->parent_id && isset($lookup[$module->parent_id])) {
                $lookup[$module->parent_id]->children->push($module);
            } else {
                // If no parent (or parent not in project scope), it's a root
                $tree[] = $module;
            }
        }

        return response()->json($tree);
    }

    /**
     * Store a new module/sub-module.
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:project_modules,id'
        ]);

        $module = $project->modules()->create([
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Module created successfully.');
    }

    /**
     * Update module.
     */
    public function update(Request $request, Project $project, ProjectModule $module)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:project_modules,id'
        ]);

        // Prevent circular dependency (Module cannot be its own ancestor)
        if ($validated['parent_id'] && $module->id == $validated['parent_id']) {
            return back()->with('error', 'A module cannot be its own parent.');
        }

        $module->update($validated);

        return back()->with('success', 'Module updated successfully.');
    }

    /**
     * Delete module.
     */
    public function destroy(Project $project, ProjectModule $module)
    {
        $module->delete();
        return back()->with('success', 'Module deleted.');
    }

    /**
     * Store bulk modules from JSON tree.
     */
    public function bulkStore(Request $request, Project $project)
    {
        $validated = $request->validate([
            'modules' => 'required|array',
        ]);

        DB::transaction(function () use ($project, $validated) {
            foreach ($validated['modules'] as $node) {
                $this->createModuleRecursive($node, $project->id, null);
            }
        });

        return back()->with('success', 'Modules imported successfully.');
    }

    private function createModuleRecursive($node, $projectId, $parentId)
    {
        $module = ProjectModule::create([
            'project_id' => $projectId,
            'parent_id' => $parentId,
            'name' => $node['name'],
            'description' => $node['description'] ?? null,
        ]);

        if (!empty($node['children'])) {
            foreach ($node['children'] as $child) {
                $this->createModuleRecursive($child, $projectId, $module->id);
            }
        }
    }

    /**
     * Clone structure from another project.
     */
    public function cloneStructure(Request $request, Project $project)
    {
        $validated = $request->validate([
            'source_project_id' => 'required|exists:projects,id|different:id',
        ]);

        $sourceProject = Project::findOrFail($validated['source_project_id']);

        DB::transaction(function () use ($project, $sourceProject) {
            // Get root modules
            $roots = $sourceProject->modules()->whereNull('parent_id')->get();
            foreach ($roots as $root) {
                $this->cloneModuleRecursive($root, $project->id, null);
            }
        });

        return back()->with('success', 'Project structure cloned successfully.');
    }

    /**
     * Helper to clone modules recursively.
     */
    private function cloneModuleRecursive(ProjectModule $source, $targetProjectId, $targetParentId)
    {
        $newModule = ProjectModule::create([
            'project_id' => $targetProjectId,
            'parent_id' => $targetParentId,
            'name' => $source->name,
            'description' => $source->description,
        ]);

        foreach ($source->children as $child) {
            $this->cloneModuleRecursive($child, $targetProjectId, $newModule->id);
        }
    }
}
