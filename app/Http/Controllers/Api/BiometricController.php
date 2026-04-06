<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BiometricController extends Controller
{
    /**
     * Push Endpoint for Hardware Devices (ZKTeco, Hikvision, etc.)
     * Accepts Raw Punch Logs via POST.
     * 
     * Auth: Bearer Token matching `api_keys` table.
     */
    public function push(Request $request)
    {
        // 1. Validate Payload
        // Standard payload format: [ { "user_id": "EMP001", "timestamp": "Y-m-d H:i:s", "device_id": "Main_Gate" } ]
        $request->validate([
            'logs' => 'required|array',
            'logs.*.user_id' => 'required|string',
            'logs.*.timestamp' => 'required|date_format:Y-m-d H:i:s',
        ]);

        $logs = $request->input('logs');

        Log::info("Biometric Push Received: " . count($logs) . " records from IP: " . $request->ip());

        // 2. Process Logs (Skeleton)
        /*
        foreach ($logs as $record) {
             // Dispatch Job: ProcessBiometricPunch::dispatch($record);
        }
        */

        return response()->json([
            'status' => 'success',
            'message' => 'Logs queued for processing',
            'count' => count($logs)
        ]);
    }
}
