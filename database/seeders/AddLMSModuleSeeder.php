<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\AppModule;
use App\Models\AppSubModule;

class AddLMSModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Check if LMS module already exists
        $lmsModule = AppModule::where('key', 'lms')->first();
        
        if (!$lmsModule) {
            // Create LMS Module
            $lmsModule = AppModule::create([
                'name' => 'Learning & Development',
                'key' => 'lms',
                'icon' => 'AcademicCapIcon',
                'route' => null,
                'order' => 50,
                'status' => true
            ]);
            
            $this->command->info('✅ LMS Module created');
        } else {
            // Ensure it's active
            $lmsModule->update(['status' => true]);
            $this->command->info('✅ LMS Module already exists - ensured it is active');
        }
        
        // Define submodules
        $submodules = [
            [
                'name' => 'My Courses',
                'key' => 'my_courses',
                'route' => 'lms.my-courses',
                'order' => 1
            ],
            [
                'name' => 'Course Catalog',
                'key' => 'courses',
                'route' => 'hr.lms.index',
                'order' => 2
            ],
            [
                'name' => 'Analytics',
                'key' => 'analytics',
                'route' => 'hr.lms.analytics.index',
                'order' => 3
            ],
            [
                'name' => 'Question Bank',
                'key' => 'questions',
                'route' => 'hr.lms.questions.index',
                'order' => 4
            ]
        ];
        
        foreach ($submodules as $submodule) {
            AppSubModule::updateOrCreate(
                [
                    'module_id' => $lmsModule->id,
                    'key' => $submodule['key']
                ],
                [
                    'name' => $submodule['name'],
                    'route' => $submodule['route'],
                    'order' => $submodule['order'],
                    'status' => true
                ]
            );
        }
        
        $this->command->info('✅ LMS Submodules created/updated: ' . count($submodules));
        $this->command->info('LMS Module is now visible in navigation for Admin/HR users');
    }
}
