<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CleanupEmployeeNavigationSeeder extends Seeder
{
    public function run(): void
    {
        // Disable top-level sub-modules that are now integrated into Employee Profile
        $modulesToHide = ['Family', 'Documents', 'History', 'Education', 'Experience'];

        DB::table('app_sub_modules')
            ->whereIn('name', $modulesToHide)
            ->update(['status' => false]);

        // Clear cache to reflect changes immediately
        Cache::forget('app_active_modules_tree');
    }
}
