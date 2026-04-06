<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

use App\Traits\ApiResponser;

use Inertia\Inertia;

class LocationController extends Controller
{
    protected $logger;

    public function __construct(\App\Services\Infrastructure\LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function index()
    {
        // Permission check (can rely on middleware or keep here)
        // if (!auth()->user()->hasPermission('org.locations.view')) abort(403); 
        
        $locs = Location::where('tenant_id', auth()->user()->tenant_id)->get();
        
        return Inertia::render('Organization/LocationList', [
            'locations' => $locs
        ]);
    }

    public function store(Request $request)
    {
        // if (!auth()->user()->hasPermission('org.locations.create')) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|min:2|max:100',
            'code' => 'nullable|string|min:2|max:20',
            'city' => 'required|string|min:2|max:50',
            'address' => 'nullable|string|max:500',
        ]);

        $loc = Location::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $validated['name'],
            'code' => $validated['code'],
            'city' => $validated['city'],
            'address' => $validated['address'] ?? null,
        ]);

        $this->logger->log('org', 'create', "Location created: {$loc->name}");

        return redirect()->back()->with('success', 'Location created successfully.');
    }

    public function update(Request $request, Location $location)
    {
        // if (!auth()->user()->hasPermission('org.locations.update')) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|min:2|max:100',
            'code' => 'nullable|string|min:2|max:20',
            'city' => 'required|string|min:2|max:50',
            'address' => 'nullable|string|max:500',
        ]);

        $location->update($validated);

        $this->logger->log('org', 'update', "Location updated: {$location->name}");

        return redirect()->back()->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        // if (!auth()->user()->hasPermission('org.locations.delete')) abort(403);

        if ($location->users()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete location with assigned users.');
        }

        $locName = $location->name;
        $location->delete();

        $this->logger->log('org', 'delete', "Location deleted: {$locName}");

        return redirect()->back()->with('success', 'Location deleted successfully.');
    }
}
