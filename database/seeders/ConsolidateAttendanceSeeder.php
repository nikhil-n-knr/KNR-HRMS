<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConsolidateAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $attendanceModule = DB::table('app_modules')->where('key', 'attendance')->first();

        if (!$attendanceModule) {
            $this->command->error("Attendance module not found. Run ModuleSeeder first.");
            return;
        }

        // 1. Add "My Requests"
        $exists = DB::table('app_sub_modules')
            ->where('module_id', $attendanceModule->id)
            ->where('key', 'my_requests')
            ->exists();

        if (!$exists) {
            DB::table('app_sub_modules')->insert([
                'module_id' => $attendanceModule->id,
                'name' => 'My Requests',
                'key' => 'my_requests',
                'route' => 'attendance.requests.index',
                'order' => 5, // After others
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("Added 'My Requests' sub-module.");
        } else {
            // Update route just in case
            DB::table('app_sub_modules')
                ->where('module_id', $attendanceModule->id)
                ->where('key', 'my_requests')
                ->update(['route' => 'attendance.requests.index', 'status' => true]);
        }

        // 2. Hide Redundant Sub-Modules
        // 'swaps' and 'shifts' are in Admin Hub (Shift Management).
        // 'overtime', 'regularization', 'wfh' are NOT in a Hub yet, so must remain visible for Admins.
        // Employees use "My Requests". Admins use these specific links.
        
        $redundantKeys = ['swaps']; // Shifts is the entry point, often mapped to Hub. Let's check route.
        
        // If 'shifts' route is mapped to 'attendance.hub', we should keep it visible as the main Admin link!
        // The ModuleSeeder says 'shifts'. If we hide it, Admin has no link to Hub.
        // So we should UPDATE 'shifts' route to 'attendance.hub'.
        
        DB::table('app_sub_modules')
            ->where('module_id', $attendanceModule->id)
            ->where('key', 'shifts')
            ->update(['route' => 'attendance.hub', 'status' => true]);

        // Hide 'swaps' (in Hub)
        $redundantKeys = ['swaps'];
        
        DB::table('app_sub_modules')
            ->where('module_id', $attendanceModule->id)
            ->whereIn('key', $redundantKeys)
            ->update(['status' => false]);
            
        // Restore others if they were hidden
         DB::table('app_sub_modules')
            ->where('module_id', $attendanceModule->id)
            ->whereIn('key', ['overtime', 'regularization', 'wfh'])
            ->update(['status' => true]);
            
        $this->command->info("Updated 'shifts' to Hub. Hidden: " . implode(', ', $redundantKeys));
    }
}
