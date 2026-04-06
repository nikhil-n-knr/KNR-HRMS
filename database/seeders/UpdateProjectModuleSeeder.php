<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateProjectModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Check if already exists to avoid duplication
        if (DB::table('app_modules')->where('key', 'project_management')->exists()) {
            $this->command->info('Project Management module already exists.');
            return;
        }

        DB::beginTransaction();
        try {
            // Insert Parent Module
            $moduleId = DB::table('app_modules')->insertGetId([
                'name' => 'Project Management',
                'key' => 'project_management',
                'icon' => 'BriefcaseIcon', // Assuming BriefcaseIcon exists in frontend map
                'order' => 15, // Place after others
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $subModules = [
                ['name' => 'Projects', 'key' => 'projects', 'route' => 'admin.projects.index'],
                ['name' => 'Scrum Board', 'key' => 'scrum_board', 'route' => 'admin.projects.board'], // Will need logical default board? Or list boards?
                ['name' => 'Tasks', 'key' => 'tasks', 'route' => null], // Maybe all tasks list?
            ];

            foreach ($subModules as $index => $sub) {
                DB::table('app_sub_modules')->insert([
                    'module_id' => $moduleId,
                    'name' => $sub['name'],
                    'key' => $sub['key'],
                    'route' => $sub['route'],
                    'order' => $index,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            $this->command->info('Project Management module added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Failed to add module: ' . $e->getMessage());
        }
    }
}
