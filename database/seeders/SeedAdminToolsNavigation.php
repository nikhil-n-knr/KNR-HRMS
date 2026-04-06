<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SeedAdminToolsNavigation extends Seeder
{
    public function run()
    {
        // 1. Find or Create 'System Intelligence' Module
        $exists = DB::table('app_modules')->where('key', 'system_intelligence')->first();
        
        if ($exists) {
            $moduleId = $exists->id;
        } else {
            $moduleId = DB::table('app_modules')->insertGetId([
                'name' => 'System Intelligence',
                'key' => 'system_intelligence',
                'icon' => 'CpuChipIcon', // Requires HeroIcons
                'status' => true,
                'order' => 99, 
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // 2. Add Sub-Modules
        $subModules = [
            [
                'name' => 'Workflow Studio',
                'key' => 'workflow_studio',
                'route' => 'admin.workflows.index',
                'order' => 1
            ],
            [
                'name' => 'Teams Architecture',
                'key' => 'teams_structure', 
                'route' => 'admin.teams.index',
                'order' => 2
            ],
            [
                'name' => 'Gamification Rules',
                'key' => 'gamification_rules',
                'route' => 'admin.gamification.index',
                'order' => 3
            ],
            [
                'name' => 'AI Watchdog Logs',
                'key' => 'ai_logs',
                'route' => 'admin.ai-logs.index',
                'order' => 4
            ],
            [
                'name' => 'Visual Planner',
                'key' => 'visual_planner',
                'route' => 'admin.planner.index',
                'order' => 5
            ]
        ];

        foreach ($subModules as $sub) {
            DB::table('app_sub_modules')->updateOrInsert(
                ['module_id' => $moduleId, 'key' => $sub['key']],
                [
                    'name' => $sub['name'],
                    'route' => $sub['route'],
                    'order' => $sub['order'],
                    'status' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            );
        }
        
        // 3. Just in case, grant to Admin role via Pivot if implicit logic isn't enough
        // (Skipped to avoid guessing pivot table name, usually 'role_module_permissions' or similar)

        $this->command->info('Admin Tools Navigation Seeded (Schema Corrected)!');
    }
}
