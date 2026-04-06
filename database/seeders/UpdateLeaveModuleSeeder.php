<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateLeaveModuleSeeder extends Seeder
{
    public function run()
    {
        // 1. Find or Create Leave Management Module
        $module = DB::table('app_modules')->where('key', 'leave')->first();

        if (!$module) {
            $moduleId = DB::table('app_modules')->insertGetId([
                'name' => 'Leave Management',
                'key' => 'leave',
                'icon' => 'CalendarIcon',
                'order' => 5,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $moduleId = $module->id;
        }

        // 2. Cleanup old bloated keys
        DB::table('app_sub_modules')
            ->where('module_id', $moduleId)
            ->whereIn('key', ['policy_config', 'leave_application', 'balance', 'holidays'])
            ->delete();

        // 3. Define Submodules and Routes
        $subModules = [
            [
                'name' => 'Leave Settings',
                'key' => 'types', // permission: leave.types.view
                'route' => 'LeaveTypeConfig', 
                'order' => 1
            ],
            [
                'name' => 'My Leaves',
                'key' => 'my_dashboard', // permission: leave.my_dashboard.view (or open)
                'route' => 'MyLeaveDashboard',
                'order' => 2
            ],
            // Manager Approval (Future Phase)
            [
                'name' => 'Manager Approvals',
                'key' => 'approvals',
                'route' => 'LeaveRequestsList',
                'order' => 3
            ]
        ];

        foreach ($subModules as $sub) {
            DB::table('app_sub_modules')->updateOrInsert(
                [
                    'module_id' => $moduleId, 
                    'key' => $sub['key']
                ],
                [
                    'name' => $sub['name'],
                    'route' => $sub['route'],
                    'order' => $sub['order'],
                    'status' => true,
                    'updated_at' => now()
                ]
            );
        }
        
        // 3. Clear Cache
        \Illuminate\Support\Facades\Cache::forget('app_active_modules_tree');
    }
}
