<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddBugTrackerSubModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $moduleId = DB::table('app_modules')->where('key', 'project_management')->value('id');

        if (!$moduleId) {
            $this->command->error('Project Management module not found.');
            return;
        }

        $subModules = [
            [
                'module_id' => $moduleId,
                'name' => 'Bug Tracker',
                'key' => 'bugs',
                'route' => 'bugs.index',
                'order' => 10,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => $moduleId,
                'name' => 'Client Portal',
                'key' => 'client_portal',
                'route' => 'bugs.index', // Handled by controller redirect
                'order' => 11,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($subModules as $sub) {
            DB::table('app_sub_modules')->updateOrInsert(
                ['module_id' => $sub['module_id'], 'key' => $sub['key']],
                $sub
            );
        }

        $this->command->info('Bug Tracker submodules added successfully.');
    }
}
