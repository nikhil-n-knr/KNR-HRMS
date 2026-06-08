<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

use App\Http\Resources\JsonResource; // Generic resource or create specific?
// Using generic response for now or Standard API Responser
use Inertia\Inertia;

class DepartmentController extends Controller
{
    protected $logger;

    public function __construct(\App\Services\Infrastructure\LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function index(Request $request)
    {
        // Check perms if needed
        // if (!auth()->user()->hasPermission('org.departments.view')) abort(403);
        
        $section = $request->query('section', 'departments');
        
        $data = [
            'section' => $section,
            'departments' => \Inertia\Inertia::lazy(fn () => Department::where('tenant_id', auth()->user()->tenant_id)->get()),
            'locations' => \Inertia\Inertia::lazy(fn () => \App\Models\Location::where('tenant_id', auth()->user()->tenant_id)->get()),
            'tenants' => \Inertia\Inertia::lazy(fn () => \App\Models\Tenant::withCount('users')->get()),
        ];

        // Eager load the required section for initial page load
        if ($section === 'tenants') {
            $data['tenants'] = \App\Models\Tenant::withCount('users')->get();
        } else if ($section === 'locations') {
            $data['locations'] = \App\Models\Location::where('tenant_id', auth()->user()->tenant_id)->get();
        } else {
            $data['departments'] = Department::where('tenant_id', auth()->user()->tenant_id)->get();
        }

        return Inertia::render('Organization/Hub', $data);
    }

    public function store(Request $request)
    {
        // if (!auth()->user()->hasPermission('org.departments.create')) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|min:2|max:100',
            'code' => 'nullable|string|min:2|max:20|unique:departments,code,NULL,id,tenant_id,' . auth()->user()->tenant_id,
        ]);

        $dept = Department::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $validated['name'],
            'code' => $validated['code'],
        ]);

        $this->logger->log('org', 'create', "Department created: {$dept->name}");

        return redirect()->back()->with('success', 'Department created successfully.');
    }

    public function update(Request $request, Department $department)
    {
        // if (!auth()->user()->hasPermission('org.departments.update')) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|min:2|max:100',
            'code' => 'nullable|string|min:2|max:20|unique:departments,code,' . $department->id . ',id,tenant_id,' . auth()->user()->tenant_id,
        ]);

        $department->update($validated);

        $this->logger->log('org', 'update', "Department updated: {$department->name}");

        return redirect()->back()->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        // if (!auth()->user()->hasPermission('org.departments.delete')) abort(403);

        if ($department->users()->exists()) {
             return redirect()->back()->with('error', 'Cannot delete department with assigned users.');
        }

        $deptName = $department->name;
        $department->delete();

        $this->logger->log('org', 'delete', "Department deleted: {$deptName}");

        return redirect()->back()->with('success', 'Department deleted successfully.');
    }
}
