<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandingUpdateSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Rename CMS Module
        DB::table('app_modules')
            ->where('key', 'cms')
            ->update(['name' => 'KNR Office CMS']);

        // 2. Rename CMS Hub Sub-module (if it exists)
        DB::table('app_sub_modules')
            ->where('key', 'cms_hub')
            ->update(['name' => 'KNR Office CMS Hub']);

        // 3. Rename Attendance Hub
        // Based on ConsolidateAttendanceSeeder, the 'shifts' sub-module is mapped to 'attendance.hub'
        DB::table('app_sub_modules')
            ->where('module_id', function($query) {
                $query->select('id')->from('app_modules')->where('key', 'attendance')->first();
            })
            ->where('key', 'shifts')
            ->update(['name' => 'KNR Attendance Hub']);

        // 4. Rename CRM to KNR Office CRM (for consistency)
        DB::table('app_modules')
            ->where('key', 'crm')
            ->update(['name' => 'KNR Office CRM']);
            
        DB::table('app_sub_modules')
            ->where('key', 'crm_hub')
            ->update(['name' => 'KNR Office CRM Hub']);

        $this->command->info('Branding update seeded successfully!');
    }
}
