<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::withCount('users')->get();
        
        return \Inertia\Inertia::render('Organization/TenantList', [
            'tenants' => $tenants
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:50',
            'domain' => 'nullable|string|max:100',
            'is_active' => 'boolean'
        ]);

        Tenant::create($validated);

        return back()->with('success', 'Tenant registered successfully.');
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:50',
            'domain' => 'nullable|string|max:100',
            'is_active' => 'boolean'
        ]);

        $tenant->update($validated);

        return back()->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant)
    {
        if ($tenant->users()->exists()) {
            return back()->with('error', 'Cannot delete tenant with active users.');
        }

        $tenant->delete();
        
        return back()->with('success', 'Tenant deleted successfully.');
    }
}
