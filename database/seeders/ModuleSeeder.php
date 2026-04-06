<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Define the Modules & Sub-modules (Parsed from Requirements)
        $modules = [
            [
                'name' => 'Dynamic User Management',
                'key' => 'user_management',
                'icon' => 'UsersIcon',
                'sub_modules' => ['User CRUD', 'Role CRUD', 'Permission Management', 'Scope Management', 'Access Review'],
            ],
            [
                'name' => 'Employee Management',
                'key' => 'employee_management',
                'icon' => 'UserGroupIcon',
                'sub_modules' => ['Employee Master', 'Documents', 'Family', 'History'],
            ],
            [
                'name' => 'Onboarding',
                'key' => 'onboarding',
                'icon' => 'ClipboardCheckIcon',
                'sub_modules' => ['Offer Letters', 'Orientation', 'E-Signature'],
            ],
            [
                'name' => 'Payroll Processing',
                'key' => 'payroll',
                'icon' => 'CurrencyDollarIcon',
                'sub_modules' => ['Salary Structure', 'Disbursement', 'TDS', 'Payslips'],
            ],
            [
                'name' => 'Attendance',
                'key' => 'attendance',
                'icon' => 'ClockIcon',
                'sub_modules' => ['Biometric', 'Shifts', 'Overtime', 'Regularization'],
            ],
            [
                'name' => 'Leave Management',
                'key' => 'leave',
                'icon' => 'CalendarIcon',
                'sub_modules' => ['Policy Config', 'Leave Application', 'Balance', 'Holidays'],
            ],
            [
                'name' => 'Appointment Letters',
                'key' => 'appointment_letters',
                'icon' => 'DocumentTextIcon',
                'sub_modules' => ['Templates', 'Generation', 'Delivery'],
            ],
            [
                'name' => 'Social Security',
                'key' => 'social_security',
                'icon' => 'ShieldCheckIcon',
                'sub_modules' => ['EPF', 'ESI', 'Gratuity', 'Welfare Fund'],
            ],
            [
                'name' => 'Grievance Redressal',
                'key' => 'grievance',
                'icon' => 'ChatAlt2Icon',
                'sub_modules' => ['Complaint Portal', 'Committee', 'Resolution'],
            ],
            [
                'name' => 'Safety & Welfare',
                'key' => 'safety',
                'icon' => 'ExclamationCircleIcon',
                'sub_modules' => ['Incident Reporting', 'Committee', 'Welfare Facilities'],
            ],
            [
                'name' => 'Statutory Compliance',
                'key' => 'compliance',
                'icon' => 'ScaleIcon',
                'sub_modules' => ['Returns', 'Calendar', 'Licences'],
            ],
            [
                'name' => 'Performance Mgmt',
                'key' => 'performance',
                'icon' => 'TrendingUpIcon',
                'sub_modules' => ['Goals', 'Reviews', 'Feedback'],
            ],
            [
                'name' => 'Recruitment',
                'key' => 'recruitment',
                'icon' => 'SearchIcon',
                'sub_modules' => ['Job Posting', 'ATS', 'Interviews'],
            ],
            [
                'name' => 'Analytics Dashboard',
                'key' => 'analytics',
                'icon' => 'ChartBarIcon',
                'sub_modules' => ['HR Metrics', 'Compliance Score', 'Attrition'],
            ],
            [
                'name' => 'Mobile App',
                'key' => 'mobile',
                'icon' => 'DeviceMobileIcon',
                'sub_modules' => ['Self Service', 'Payslips', 'Check-In'],
            ],
            [
                'name' => 'Document Management',
                'key' => 'documents',
                'icon' => 'FolderIcon',
                'sub_modules' => ['Repository', 'Version Control', 'Audit'],
            ],
            [
                'name' => 'AI Compliance Bot',
                'key' => 'ai_bot',
                'icon' => 'SparklesIcon',
                'sub_modules' => ['Legal Queries', 'Updates', 'Alerts'],
            ],
            [
                'name' => 'Multi-State Support',
                'key' => 'multi_state',
                'icon' => 'MapIcon',
                'sub_modules' => ['State Rules', 'Licences', 'Wage Variations'],
            ],
            // Core System Modules (Not in CSV but needed)
            [
                'name' => 'Project Management',
                'key' => 'project_management',
                'icon' => 'BriefcaseIcon',
                'sub_modules' => ['Projects', 'Tasks', 'Sprints', 'Timesheets'],
            ],
            [
                'name' => 'Organization',
                'key' => 'org',
                'icon' => 'OfficeBuildingIcon',
                'sub_modules' => ['Departments', 'Locations', 'Tenants'],
            ],
        ];

        DB::beginTransaction();
        try {
            foreach ($modules as $index => $mod) {
                // Check if module exists
                $moduleId = DB::table('app_modules')->where('key', $mod['key'])->value('id');

                if (!$moduleId) {
                    $moduleId = DB::table('app_modules')->insertGetId([
                        'name' => $mod['name'],
                        'key' => $mod['key'],
                        'icon' => $mod['icon'],
                        'order' => $index,
                        'status' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                foreach ($mod['sub_modules'] as $subIndex => $subName) {
                    $subKey = Str::slug($subName, '_'); 
                    
                    if ($mod['key'] === 'user_management') {
                         if ($subName === 'User CRUD') $subKey = 'user';
                         if ($subName === 'Role CRUD') $subKey = 'role';
                         if ($subName === 'Permission Management') $subKey = 'permission';
                         if ($subName === 'Scope Management') $subKey = 'scope';
                    }

                    // Check if sub-module exists
                    $exists = DB::table('app_sub_modules')
                        ->where('module_id', $moduleId)
                        ->where('key', $subKey)
                        ->exists();

                    if (!$exists) {
                        DB::table('app_sub_modules')->insert([
                            'module_id' => $moduleId,
                            'name' => $subName,
                            'key' => $subKey,
                            'route' => null,
                            'order' => $subIndex,
                            'status' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
