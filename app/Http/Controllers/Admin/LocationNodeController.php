<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LocationNode;
use Inertia\Inertia;

class LocationNodeController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Assets/Locations/Index', [
            'locations' => LocationNode::with('parent')->withCount('assignments')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'node_type' => 'required|in:Branch,Building,Floor,Room,Zone,Locker,Cupboard,Shelf,Bin',
            'parent_id' => 'nullable|exists:location_nodes,id',
            'code' => 'nullable|string|max:100',
            'capacity' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        LocationNode::create($validated);

        return back()->with('success', 'Location created successfully');
    }

    public function update(Request $request, LocationNode $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'node_type' => 'required|in:Branch,Building,Floor,Room,Zone,Locker,Cupboard,Shelf,Bin',
            'parent_id' => 'nullable|exists:location_nodes,id',
            'code' => 'nullable|string|max:100',
            'capacity' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        $location->update($validated);

        return back()->with('success', 'Location updated successfully');
    }

    public function destroy(LocationNode $location)
    {
        if ($location->assignments()->exists()) {
            return back()->with('error', 'Cannot archive a location with active assignments.');
        }

        $location->delete(); // Soft delete

        return back()->with('success', 'Location archived successfully');
    }
}
