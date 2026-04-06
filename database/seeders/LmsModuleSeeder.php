<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\AppModule;
use App\Models\AppSubModule;

class LmsModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create/Update Parent Module
        $lmsModule = AppModule::updateOrCreate(
            ['name' => 'LMS'], // Identifying Key
            [
                'key' => 'lms',
                'icon' => 'AcademicCapIcon', 
                'route' => 'hr.lms.index',
                'order' => 60,
                'status' => true
            ]
        );

        $moduleId = $lmsModule->id;

        // 2. Create Sub-Modules (No Icons for SubModules)
        $subModules = [
            [
                'name' => 'Dashboard',
                'key' => 'lms-dashboard',
                'route' => 'hr.lms.analytics.index',
                'order' => 1,
            ],
            [
                'name' => 'Courses',
                'key' => 'lms-courses',
                'route' => 'hr.lms.index',
                'order' => 2,
            ],
            [
                'name' => 'Question Bank',
                'key' => 'lms-questions',
                'route' => 'hr.lms.questions.index',
                'order' => 3,
            ],
            [
                'name' => 'Certificates',
                'key' => 'lms-certificates',
                'route' => 'hr.lms.questions.template',
                'order' => 4,
            ]
        ];

        foreach ($subModules as $sub) {
            AppSubModule::updateOrCreate(
                [
                    'module_id' => $moduleId, // Correct FK
                    'name' => $sub['name']
                ],
                [
                    'key' => $sub['key'],
                    'route' => $sub['route'],
                    'order' => $sub['order'],
                    'status' => true
                ]
            );
        }

        echo "LMS Module and Sub-App Modules Seeded Successfully.\n";
    }
}
