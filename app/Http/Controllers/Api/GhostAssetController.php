<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;
use Illuminate\Support\Facades\Log;

class GhostAssetController extends Controller
{
    /**
     * Receive Pulse from Agent Script.
     * Payload: { serial_number, ip_address, hostname, username }
     */
    public function pulse(Request $request)
    {
        $data = $request->validate([
            'serial_number' => 'required|string',
            'ip_address' => 'required|ip',
            'hostname' => 'nullable|string',
            'username' => 'nullable|string'
        ]);

        $asset = Asset::where('serial_number', $data['serial_number'])->first();
        
        if (!$asset) {
            return response()->json(['status' => 'unknown_asset'], 404);
        }

        // Logic: Check Ghost Movement
        // Defined Safe Subnets (Mocked for now, strictly should be in DB config)
        $safeSubnets = ['192.168.', '10.0.', '127.0.']; 
        $isSafe = false;
        foreach ($safeSubnets as $subnet) {
            if (str_starts_with($data['ip_address'], $subnet)) {
                $isSafe = true;
                break;
            }
        }

        if (!$isSafe) {
            // Flag as Ghost / Violation
            // We could add a 'Ghost' status or just log a warning activity
            Log::warning("Ghost Asset Detected: {$asset->name} ({$asset->serial_number}) at {$data['ip_address']}");
            
            // Optional: Create a "Security Alert" in maintenance logs or dedicated table
            // For MVP, just return a flag
            return response()->json([
                'status' => 'violation', 
                'message' => 'Asset outside authorized network perimeter.'
            ]);
        }

        // Update Last Seen
        // $asset->update(['last_seen_at' => now(), 'last_ip' => $data['ip_address']]);

        return response()->json(['status' => 'ok', 'message' => 'Asset secured.']);
    }
}
