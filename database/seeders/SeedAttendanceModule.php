<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Permission;
use App\Models\Role;

class SeedAttendanceModule extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure 'Attendance' Module exists
        $attendanceModule = DB::table('app_modules')->where('key', 'attendance')->first();
        
        if (!$attendanceModule) {
            $moduleId = DB::table('app_modules')->insertGetId([
                'name' => 'Attendance',
                'key' => 'attendance',
                'icon' => 'ClockIcon', 
                'order' => 4, // Column is 'order', not 'order_index'
                'status' => true, // Column is 'status', not 'is_active'
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $moduleId = $attendanceModule->id;
        }

        // 2. Define Sub-Modules
        $subModules = [
            [
                'name' => 'My Dashboard',
                'key' => 'my_attendance',
                'route' => '/attendance', // Column 'route'
                'order' => 1, // Column 'order'
                'roles' => ['Employee', 'Manager', 'Admin']
            ],
            [
                'name' => 'Timesheets',
                'key' => 'timesheets',
                'route' => '/attendance/timesheets',
                'order' => 2,
                'roles' => ['Employee']
            ],
            [
                'name' => 'My Holidays (RH)',
                'key' => 'my_holidays',
                'route' => '/attendance/floating-holidays',
                'order' => 3,
                'roles' => ['Employee', 'Manager']
            ],
            [
                'name' => 'Shift Swaps',
                'key' => 'shift_swaps',
                'route' => '/attendance/swaps',
                'order' => 4,
                'roles' => ['Employee', 'Manager']
            ],
            [
                'name' => 'Team Approvals',
                'key' => 'team_approvals',
                'route' => '/manager/approvals',
                'order' => 5,
                'roles' => ['Manager', 'Admin']
            ],
            [
                'name' => 'Attendance Roster',
                'key' => 'roster',
                'route' => '/admin/attendance/roster',
                'order' => 6,
                'roles' => ['Admin', 'Manager']
            ],
            [
                'key' => 'monitoring',
                'name' => 'Live Monitor',
                'route' => 'admin.attendance.monitoring',
                'icon' => 'Activity',
                'order' => 7,
                'roles' => ['Admin', 'Manager']
            ],
            [
                'key' => 'regularization',
                'name' => 'Regularization',
                'route' => 'admin.attendance.regularization',
                'icon' => 'CheckSquare',
                'order' => 8,
                'roles' => ['Admin', 'Manager']
            ],
            [
                'key' => 'overtime',
                'name' => 'Overtime Requests',
                'route' => 'admin.attendance.overtime.index',
                'icon' => 'Clock',
                'order' => 9,
                'roles' => ['Admin', 'Manager']
            ],
            [
                'key' => 'wfh',
                'name' => 'WFH Requests',
                'route' => 'admin.attendance.wfh.index',
                'icon' => 'Home',
                'order' => 10,
                'roles' => ['Admin', 'Manager']
            ],
            [
                'key' => 'admin_floating',
                'name' => 'Floating Requests',
                'route' => 'admin.attendance.floating-holidays',
                'icon' => 'Calendar',
                'order' => 11,
                'roles' => ['Admin', 'Manager']
            ],
            [
                'key' => 'admin_swaps',
                'name' => 'Swap Requests',
                'route' => 'admin.attendance.swaps',
                'icon' => 'RefreshCcw',
                'order' => 12,
                'roles' => ['Admin', 'Manager']
            ],
            [
                'key' => 'analytics',
                'name' => 'Analytics',
                'route' => 'admin.attendance.analytics',
                'icon' => 'BarChart2',
                'order' => 13,
                'roles' => ['Admin', 'Manager']
            ],
            [
                'name' => 'Timesheet Policy',
                'key' => 'policy_builder',
                'route' => '/admin/attendance/policies',
                'order' => 14,
                'roles' => ['Admin', 'Super Admin']
            ],
            [
                'name' => 'Workflow Builder',
                'key' => 'workflow_builder',
                'route' => '/admin/attendance/workflows',
                'order' => 15,
                'roles' => ['Admin', 'Super Admin']
            ],
            [
                'name' => 'Gamification Rules',
                'key' => 'gamification_rules',
                'route' => '/admin/attendance/gamification',
                'order' => 16,
                'roles' => ['Admin', 'Super Admin']
            ],
            [
                'name' => 'AI Analysis Logs',
                'key' => 'ai_logs',
                'route' => '/admin/attendance/ai-logs',
                'order' => 17,
                'roles' => ['Admin', 'Super Admin']
            ]
        ];

        // cleanup old bloated keys
        DB::table('app_sub_modules')
            ->where('module_id', $moduleId)
            ->whereIn('key', ['biometric', 'shifts', 'overtime', 'regularization'])
            ->delete();

        foreach ($subModules as $sub) {
            // Create/Update SubModule
            DB::table('app_sub_modules')->updateOrInsert(
                ['module_id' => $moduleId, 'key' => $sub['key']],
                [
                    'name' => $sub['name'],
                    'route' => $sub['route'],
                    'order' => $sub['order'],
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Create Permissions for this SubModule
            // Permission keys: attendance.my_attendance.view
            $perm = Permission::firstOrCreate([
                'module' => 'attendance',
                'submodule' => $sub['key'],
                'action' => 'view'
            ]);

            // Assign to Roles
            foreach ($sub['roles'] as $roleName) {
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $role->permissions()->syncWithoutDetaching([$perm->id]);
                }
            }
        }

        // 3. Special Permissions (Actions)
        $extraPerms = [
            ['role' => 'Manager', 'action' => 'approve_requests', 'submodule' => 'team_approvals'],
            ['role' => 'Admin', 'action' => 'manage_policy', 'submodule' => 'policy_builder'],
            ['role' => 'Admin', 'action' => 'manage_workflows', 'submodule' => 'workflow_builder'],
            ['role' => 'Admin', 'action' => 'manage_gamification', 'submodule' => 'gamification_rules'],
            ['role' => 'Admin', 'action' => 'view_logs', 'submodule' => 'ai_logs'],
        ];

        foreach ($extraPerms as $ep) {
            $p = Permission::firstOrCreate([
                'module' => 'attendance',
                'submodule' => $ep['submodule'],
                'action' => $ep['action']
            ]);
            
            $r = Role::where('name', $ep['role'])->first();
            if ($r) {
                $r->permissions()->syncWithoutDetaching([$p->id]);
            }
        }

        // 4. Clear Cache
        Cache::forget('app_active_modules_tree');
        $this->command->info('Attendance Module & Permissions Seeded!');
    }
}
