<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateModuleRoutesSeeder extends Seeder
{
    public function run(): void
    {
        $routes = [
            // User Management
            'user_management' => [
                'user' => 'admin.users.index',
                'role' => 'admin.roles.index',
                'permission' => 'admin.roles.matrix',
                'access_review' => 'admin.users.access-review',
            ],
            // Project Management
            'project_management' => [
                'projects' => 'projects.dashboard',
                'scrum_board' => 'projects.board', // Placeholder if needed, or redirect
                'tasks' => 'projects.my-tasks',
                'sprints' => 'planner.index', // Mapping Sprints to Planner for now as it seems to be the main view
            ],
            // Recruitment
            'recruitment' => [
                'job_posting' => 'talent.jobs.index',
                'ats' => 'talent.candidates.index',
                'interviews' => 'talent.interviews.index', 
                // 'onboarding' -> 'talent.offers.create' // Onboarding usually starts with offer
            ],
            // Attendance
            'attendance' => [
                // 'dashboard' => 'attendance.dashboard', // Might be employee view
                'shifts' => 'attendance.shifts.list',
                'overtime' => 'attendance.overtime.my-requests', // Employee view default? Or admin? Let's use Admin for module config usually
                'regularization' => 'attendance.regularization',
            ],
            // Leave
            'leave' => [
                'leave_application' => 'employee.leave.index',
                'policy_config' => 'admin.policies.index', // Need to check if this route exists
            ],
            // Employee Management
            'employee_management' => [
                'employee_master' => 'admin.employees.index',
            ],
             // Organization
            'org' => [
                'departments' => 'admin.departments.index',
                'locations' => 'admin.locations.index',
            ],
        ];

        foreach ($routes as $moduleKey => $subModules) {
            // Find Module ID
            $moduleId = DB::table('app_modules')->where('key', $moduleKey)->value('id');

            if ($moduleId) {
                foreach ($subModules as $subKey => $route) {
                    DB::table('app_sub_modules')
                        ->where('module_id', $moduleId)
                        ->where('key', $subKey)
                        ->update(['route' => $route, 'updated_at' => now()]);
                }
            }
        }
        
        // Manual Fixes for specific keys that might differ
        // e.g. 'leave' module key might be 'leave_management' in some seeders?
        // Checking ModuleSeeder: 'leave' key is correct. 'employee_management' is correct.

        $this->command->info('Module routes updated successfully.');
    }
}
