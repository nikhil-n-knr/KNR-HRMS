<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define Modules matching ModuleSeeder + extra granular permissions
        $modules = [
            // 1. User Management
            'user_management' => [
                'user' => ['view', 'view_list', 'create', 'update', 'delete', 'impersonate'],
                'role' => ['view', 'view_list', 'create', 'update', 'delete', 'assign'],
                'permission' => ['view', 'assign'],
                'scope' => ['view', 'update'],
                'access_review' => ['view', 'approve'],
            ],

            // 2. Employee Management
            'employee_management' => [
                'employee_master' => ['view', 'view_list', 'create', 'update', 'delete'],
                'documents' => ['view', 'upload', 'verify', 'delete'],
                'family' => ['view', 'update'],
                'history' => ['view'],
            ],

            // 3. Organization
            'org' => [
                'departments' => ['view', 'create', 'update', 'delete'],
                'locations' => ['view', 'create', 'update', 'delete'],
                'tenants' => ['view', 'update'], // Multi-tenant settings
            ],

            // 4. Project Management
            'project_management' => [
                'projects' => ['view', 'view_list', 'create', 'update', 'delete', 'archive'],
                'tasks' => ['view', 'create', 'update', 'delete', 'assign'],
                'sprints' => ['view', 'create', 'manage'],
                'timesheets' => ['view', 'log', 'approve'],
                'clients' => ['view', 'create', 'update', 'delete'],
                'planner' => ['view', 'manage'],
                'reports' => ['view', 'export'],
            ],

            // 5. Recruitment (Talent)
            'recruitment' => [
                'job_posting' => ['view', 'create', 'update', 'publish', 'delete'],
                'ats' => ['view', 'view_list', 'move_candidate', 'reject', 'offer'],
                'interviews' => ['view', 'schedule', 'feedback'],
                'screening' => ['view', 'manage_templates'],
            ],

            // 6. Attendance
            'attendance' => [
                'dashboard' => ['view'],
                'biometric' => ['view', 'import'],
                'shifts' => ['view', 'create', 'update', 'assign'],
                'overtime' => ['view', 'request', 'approve'],
                'regularization' => ['view', 'request', 'approve'],
                'roster' => ['view', 'manage'],
                'reports' => ['view', 'export'],
            ],

            // 7. Leave Management
            'leave' => [
                'policy_config' => ['view', 'create', 'update', 'delete'],
                'leave_application' => ['view', 'apply', 'approve', 'reject'],
                'balance' => ['view', 'adjust'],
                'holidays' => ['view', 'create', 'update', 'delete'],
            ],

            // 8. Payroll
            'payroll' => [
                'salary_structure' => ['view', 'create', 'update', 'assign'],
                'disbursement' => ['view', 'process'],
                'tds' => ['view', 'manage'],
                'payslips' => ['view', 'generate', 'publish'],
            ],

            // 9. Performance
            'performance' => [
                'goals' => ['view', 'create', 'update', 'track'],
                'reviews' => ['view', 'conduct', 'finalize'],
                'feedback' => ['view', 'give'],
            ],

            // 10. Assets
            'assets' => [
                'inventory' => ['view', 'create', 'update', 'delete'],
                'assignment' => ['view', 'assign', 'return'],
            ],

            // 11. Finance
            'finance' => [
                'invoices' => ['view', 'create', 'send'],
                'expenses' => ['view', 'create', 'approve'],
            ],

            // 12. Documents (Global)
            'documents' => [
                'repository' => ['view', 'upload', 'delete'],
                'audit' => ['view'],
            ],

            // 13. System / Admin
            'admin' => [
                'global_settings' => ['view', 'update'],
                'audit_logs' => ['view'],
                'workflows' => ['view', 'manage'],
                'ai_logs' => ['view'],
            ],

            // 14. Support / Grievance
            'grievance' => [
                'complaints' => ['view', 'submit', 'resolve'],
                'committee' => ['view', 'manage'],
            ],
        ];

        foreach ($modules as $moduleKey => $subModules) {
            foreach ($subModules as $subKey => $actions) {
                foreach ($actions as $action) {
                    
                    // Insert if not exists
                    DB::table('permissions')->updateOrInsert(
                        [
                            'module' => $moduleKey,
                            'submodule' => $subKey,
                            'action' => $action
                        ],
                        [
                            'description' => "Allows {$action} on {$subKey} in {$moduleKey}",
                            'created_at' => now(), 
                            'updated_at' => now()
                        ] 
                    );
                }
            }
        }

        // Clear Cache to force reload
        Cache::forget('app_active_modules_tree');

        $this->command->info('Permissions seeded successfully for all modules.');
    }
}
