<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceZone;
use App\Models\Location;

class ZoneController extends Controller
{
    public function index()
    {
        return response()->json([
            'zones' => AttendanceZone::with('location')->get(),
            'locations' => Location::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meters' => 'required|integer|min:10',
            'location_id' => 'required|exists:locations,id',
            'is_active' => 'boolean'
        ]);

        AttendanceZone::create($validated);
        return response()->json(['message' => 'Zone created successfully.']);
    }

    public function update(Request $request, AttendanceZone $zone)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'radius_meters' => 'sometimes|integer|min:10',
            'location_id' => 'sometimes|exists:locations,id',
            'is_active' => 'boolean'
        ]);

        $zone->update($validated);
        return response()->json(['message' => 'Zone updated successfully.']);
    }

    public function destroy(AttendanceZone $zone)
    {
        $zone->delete();
        return response()->json(['message' => 'Zone removed.']);
    }
}
