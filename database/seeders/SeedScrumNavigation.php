<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeedScrumNavigation extends Seeder
{
    public function run()
    {
        // 1. Create the Main Module: "Project Management"
        // Check if exists first to avoid duplicates
        $module = DB::table('app_modules')->where('key', 'project_management')->first();

        if ($module) {
            $moduleId = $module->id;
            // Update existing if needed
            DB::table('app_modules')->where('id', $moduleId)->update([
                'name' => 'Project Management',
                'icon' => 'BriefcaseIcon',
                'status' => true,
                'order' => 3,
                'updated_at' => now(),
            ]);
        } else {
            $moduleId = DB::table('app_modules')->insertGetId([
                'key' => 'project_management',
                'name' => 'Project Management',
                'icon' => 'BriefcaseIcon',
                'status' => true,
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Define Sub-Modules
        $subModules = [
            [
                'key' => 'clients',
                'name' => 'Clients',
                'route' => '/projects/clients', 
                'icon' => 'UserGroupIcon',
                'access_gate' => 'project_management.clients.view'
            ],
            [
                'key' => 'projects_dashboard',
                'name' => 'Projects & Boards',
                'route' => '/projects/dashboard',
                'icon' => 'TemplateIcon',
                'access_gate' => 'project_management.projects.view'
            ],
            [
                'key' => 'planner',
                'name' => 'Resource Planner',
                'route' => '/projects/planner',
                'icon' => 'CalendarIcon',
                'access_gate' => 'project_management.planner.view'
            ],
            [
                'key' => 'tasks',
                'name' => 'My Tasks',
                'route' => '/projects/my-tasks',
                'icon' => 'ClipboardCheckIcon',
                'access_gate' => 'project_management.tasks.view'
            ]
        ];

        // 3. Insert Sub-Modules
        foreach ($subModules as $index => $sub) {
            DB::table('app_sub_modules')->updateOrInsert(
                ['module_id' => $moduleId, 'key' => $sub['key']],
                [
                    'name' => $sub['name'],
                    'route' => $sub['route'],
                    // 'icon' removed - not in schema for sub_modules
                    'status' => true,
                    'order' => $index + 1,
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('Project Management module seeded successfully.');
    }
}
