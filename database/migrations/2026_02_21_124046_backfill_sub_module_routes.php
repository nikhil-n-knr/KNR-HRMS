<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill missing route values for existing sub-modules.
     * These routes were previously resolved by hard-coded logic in NavigationController.
     */
    public function up(): void
    {
        $updates = [
            // Dynamic User Management
            'user_management' => [
                'user'          => 'admin.users.index',
                'role'          => 'admin.roles.index',
                'permission'    => 'admin.roles.matrix',
                'scope'         => 'admin.roles.matrix',
                'access_review' => 'admin.users.access-review',
            ],

            // Employee Management
            'employee_management' => [
                'employees'      => 'admin.employees.index',
                'employee_master' => 'admin.employees.index',
                'documents'      => 'admin.employees.index',
                'family'         => 'admin.employees.index',
                'history'        => 'admin.employees.index',
            ],

            // Project Management
            'project_management' => [
                'projects'          => 'projects.index',
                'tasks'             => 'projects.my-tasks',
                'sprints'           => 'projects.dashboard',
                'timesheets'        => 'attendance.timesheets',
                'client_hub'        => 'clients.index',
                'devops'            => 'admin.devops.dashboard',
                'bugs'              => 'bugs.index',
                'client_portal'     => 'bugs.index',
                'bug_intelligence'  => 'bugs.analytics',
                'pulse'             => 'bugs.index',
                'pending_approvals' => 'bugs.index',
                'workflow_architect' => 'workflow-architect.index',
            ],

            // Recruitment
            'recruitment' => [
                'talent_main'  => 'talent.jobs.index',
                'job_posting'  => 'talent.jobs.index',
                'candidates'   => 'talent.candidates.index',
                'offers'       => 'talent.offers.create',
                'ats'          => 'talent.hub',
                'interviews'   => 'talent.hub',
                'onboarding'   => 'talent.hub',
            ],

            // Attendance
            'attendance' => [
                'timesheets'       => 'attendance.timesheets',
                'my_attendance'    => 'attendance.timesheets',
                'my_holidays'      => 'employee.attendance.floating-holidays',
                'floating_holidays' => 'employee.attendance.floating-holidays',
                'swaps'            => 'attendance.swaps',
                'admin_floating'   => 'admin.attendance.holidays',
                'admin_swaps'      => 'admin.attendance.swaps',
                'monitoring'       => 'admin.attendance.monitoring',
                'roster'           => 'admin.attendance.roster',
                'regularization'   => 'admin.attendance.regularization',
                'overtime'         => 'admin.attendance.overtime.index',
                'wfh'              => 'admin.attendance.wfh.index',
                'shifts'           => 'admin.attendance.shifts.list',
                'analytics'        => 'admin.attendance.analytics',
                'gamification'     => 'admin.attendance.gamification',
                'gamification_rules' => 'admin.attendance.gamification',
                'policy_builder'   => 'admin.attendance.policies',
                'policies'         => 'admin.attendance.policies',
                'workflow_builder' => 'admin.attendance.workflows',
                'team_approvals'   => 'admin.attendance.regularization',
            ],

            // Leave Management
            'leave' => [
                'leave_application' => 'employee.leave.index',
                'balance'           => 'employee.leave.index',
                'policy_config'     => 'admin.leave.index',
            ],

            // Performance Mgmt
            'performance' => [
                'goals'   => 'performance.goals.index',
                'reviews' => 'performance.cycles.index',
                'feedback' => 'performance.goals.index',
            ],

            // Payroll Processing
            'payroll' => [
                'payslips'        => 'hr.payroll.index',
                'salary_structure' => 'admin.salary-structures.index',
                'tds'             => 'hr.payroll.tax-settings',
                'disbursement'    => 'hr.payroll.disbursement',
            ],

            // Document Management
            'documents' => [
                'repository'      => 'admin.documents.index',
                'audit'           => 'admin.documents.index',
                'version_control' => 'admin.documents.index',
            ],

            // Onboarding
            'onboarding' => [
                'offer_letters' => 'talent.offers.create',
                'orientation'   => 'talent.hub',
                'e_signature'   => 'talent.hub',
            ],

            // Statutory Compliance  
            'compliance' => [
                'returns'   => 'hr.compliance.index',
                'calendar'  => 'hr.compliance.index',
                'licences'  => 'hr.compliance.index',
            ],

            // Organization
            'org' => [
                'departments' => 'admin.departments.index',
                'locations'   => 'admin.locations.index',
                'tenants'     => 'admin.departments.index',
            ],
        ];

        foreach ($updates as $moduleKey => $subRoutes) {
            $module = DB::table('app_modules')->where('key', $moduleKey)->first();
            if (!$module) continue;

            foreach ($subRoutes as $subKey => $route) {
                DB::table('app_sub_modules')
                    ->where('module_id', $module->id)
                    ->where('key', $subKey)
                    ->whereNull('route')
                    ->orWhere(function($q) use ($module, $subKey) {
                        $q->where('module_id', $module->id)
                          ->where('key', $subKey)
                          ->where('route', '');
                    })
                    ->update(['route' => $route, 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        // No easy rollback for route backfill
    }
};
