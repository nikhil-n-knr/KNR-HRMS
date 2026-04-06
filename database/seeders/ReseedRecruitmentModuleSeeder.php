<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReseedRecruitmentModuleSeeder extends Seeder
{
    public function run()
    {
        // 1. Cleanup: Remove any existing variants to avoid duplicates
        DB::table('app_modules')->whereIn('key', ['talent', 'recruitment', 'onboarding', 'appointment_letter'])->delete();
        
        // 2. Insert Main Module: "Recruitment"
        $moduleId = DB::table('app_modules')->insertGetId([
            'name' => 'Recruitment',
            'key' => 'recruitment',
            'icon' => 'UserGroupIcon', // Ensure Frontend has this
            'route' => 'talent.hub',    // Matches web.php:114
            'order' => 5, // Position in sidebar
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Insert Sub Modules
        $subModules = [
            [
                'module_id' => $moduleId,
                'name' => 'Talent',      // Formerly Jobs
                'key' => 'talent_main',  // Matches NavigationController check
                'route' => 'talent.jobs.index', 
                'order' => 1,
                'status' => true
            ],
            [
                'module_id' => $moduleId,
                'name' => 'Candidates',
                'key' => 'candidates',
                'route' => 'talent.candidates.index',
                'order' => 2,
                'status' => true
            ],
            [
                'module_id' => $moduleId,
                'name' => 'Offer Letters',
                'key' => 'offers',
                'route' => 'talent.offers.create',
                'order' => 3,
                'status' => true
            ]
        ];

        foreach ($subModules as $sub) {
            DB::table('app_sub_modules')->insert(array_merge($sub, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        $this->command->info('Recruitment Module Force-Reset Complete.');
    }
}
