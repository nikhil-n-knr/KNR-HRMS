<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use App\Models\Role;

class UpdateLeavePermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // Module.Submodule.Action
            
            // Leave Types (Admin Config)
            ['module' => 'leave', 'submodule' => 'types', 'action' => 'view_list', 'name' => 'View Leave Types List'],
            ['module' => 'leave', 'submodule' => 'types', 'action' => 'view', 'name' => 'View Leave Type'],
            ['module' => 'leave', 'submodule' => 'types', 'action' => 'create', 'name' => 'Create Leave Type'],
            ['module' => 'leave', 'submodule' => 'types', 'action' => 'edit', 'name' => 'Edit Leave Type'],
            ['module' => 'leave', 'submodule' => 'types', 'action' => 'delete', 'name' => 'Delete Leave Type'],

            // My Dashboard (Employee)
            ['module' => 'leave', 'submodule' => 'my_dashboard', 'action' => 'view', 'name' => 'View My Leave Dashboard'],
            ['module' => 'leave', 'submodule' => 'my_dashboard', 'action' => 'apply', 'name' => 'Apply for Leave'],
            ['module' => 'leave', 'submodule' => 'my_dashboard', 'action' => 'cancel', 'name' => 'Cancel Request'],
        ];

        $permissionIds = [];

        foreach ($permissions as $perm) {
            $p = Permission::firstOrCreate(
                ['module' => $perm['module'], 'submodule' => $perm['submodule'], 'action' => $perm['action']],
                ['name' => $perm['name'], 'guard_name' => 'web']
            );
            $permissionIds[] = $p->id;
        }

        // Attach to Super Admin
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->permissions()->syncWithoutDetaching($permissionIds);
        }
        
        // Attach Basic Employee Perms to "Employee" Role (if exists)
        $employeeRole = Role::where('name', 'Employee')->first();
        if ($employeeRole) {
             $empPerms = Permission::where('module', 'leave')
                ->where('submodule', 'my_dashboard')
                ->pluck('id');
             $employeeRole->permissions()->syncWithoutDetaching($empPerms);
        }
    }
}
