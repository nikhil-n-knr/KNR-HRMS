<?php

namespace App\Services\Attendance;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\Attendance\AttendanceRegistryService;

class BiometricService
{
    protected $registry;

    public function __construct(AttendanceRegistryService $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Handle a punch from a biometric device.
     *
     * @param string $deviceId
     * @param string $userId
     * @param string $timestamp Y-m-d H:i:s
     * @param float|null $latitude
     * @param float|null $longitude
     * @return array
     */
    public function handlePunch($deviceId, $userId, $timestamp, $latitude = null, $longitude = null)
    {
        // 1. Deduplication using Cache (1 minute window)
        $cacheKey = "bio_punch_{$userId}_{$timestamp}";
        
        if (Cache::has($cacheKey)) {
            Log::info("Biometric Duplicate Ignored: User {$userId} at {$timestamp}");
            return ['status' => 'duplicate', 'message' => 'Punch already recorded'];
        }

        // Cache for 60 seconds
        Cache::put($cacheKey, true, 60);

        // 2. Find Employee
        // Assuming Device UserID matches our Employee ID or Bio ID column. 
        // For MVP, assuming match on 'id' or 'employee_code'.
        $employee = Employee::where('id', $userId)->orWhere('employee_code', $userId)->first();

        if (!$employee) {
            Log::warning("Biometric Unknown User: {$userId}");
            return ['status' => 'error', 'message' => 'User not found'];
        }

        // 3. Location Check (Optional but recommended)
        // In a real device, we might map DeviceSerial -> LocationID.
        // For now, let's assume valid location or log warning.
        
        $punchTime = Carbon::parse($timestamp);
        
        // 4. Delegate to Registry to Log Attendance
        // We determine In vs Out based on existing logs for the day
        try {
            $this->registry->logPunch($employee, $punchTime, 'Biometric', $latitude, $longitude);
            
            return ['status' => 'success', 'message' => 'Punch accepted'];
        } catch (\Exception $e) {
            Log::error("Biometric Registry Error: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
