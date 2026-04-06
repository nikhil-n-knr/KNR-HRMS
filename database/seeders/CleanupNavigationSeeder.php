<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanupNavigationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $keysToRemove = [
            'roster',
            'attendance_roster',
            'overtime',
            'wfh',
            'work_from_home',
            'shift_swaps',
            'swaps',
            'floating_holidays',
            'holidays'
        ];

        // Delete from sub_modules
        $deleted = DB::table('app_sub_modules')->whereIn('key', $keysToRemove)->delete();

        $this->command->info("Deleted {$deleted} redundant navigation items.");
    }
}
