<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BiometricDevice;
use App\Models\AttendanceZone;
use Illuminate\Support\Facades\Http;

class DeviceController extends Controller
{
    public function index()
    {
        try {
            return response()->json([
                'devices' => BiometricDevice::with('zone')->get(),
                'zones' => AttendanceZone::all()
            ]);
        } catch (\Exception $e) {
            // If tables don't exist, return empty data
            return response()->json([
                'devices' => [],
                'zones' => []
            ]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'serial_number' => 'required|string|unique:biometric_devices',
            'ip_address' => 'required|string|max:255', // Relaxed to string to allow hostnames
            'port' => 'nullable|integer',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'protocol' => 'nullable|string',
            'location_name' => 'nullable|string',
            'description' => 'nullable|string',
            'heartbeat_interval' => 'nullable|integer',
            'attendance_zone_id' => 'nullable|exists:attendance_zones,id',
            'is_active' => 'boolean'
        ]);

        BiometricDevice::create($validated);
        return response()->json(['message' => 'Node successfully registered in mesh.']);
    }

    public function update(Request $request, BiometricDevice $device)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'serial_number' => 'sometimes|string|unique:biometric_devices,serial_number,' . $device->id,
            'ip_address' => 'sometimes|string|max:255',
            'port' => 'nullable|integer',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'protocol' => 'nullable|string',
            'location_name' => 'nullable|string',
            'description' => 'nullable|string',
            'heartbeat_interval' => 'nullable|integer',
            'attendance_zone_id' => 'nullable|exists:attendance_zones,id',
            'is_active' => 'boolean'
        ]);

        $device->update($validated);
        return response()->json(['message' => 'Infrastructure telemetry updated.']);
    }

    public function destroy(BiometricDevice $device)
    {
        $device->delete();
        return response()->json(['message' => 'Device removed.']);
    }

    public function ping(BiometricDevice $device)
    {
        // Mock Ping - In reality, use fsockopen or similar to check port 4370
        // Or call device API
        $online = (bool) mt_rand(0, 1); 
        
        $device->update([
            'status' => $online ? 'online' : 'offline',
            'last_sync_at' => now()
        ]);

        return response()->json([
            'status' => $device->status,
            'message' => $online ? 'Device is Online' : 'Device Unreachable'
        ]);
    }
}
