<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Role;
use App\Models\Permission;
use App\Services\Auth\UserService;
use App\Services\Auth\PermissionService;
use Illuminate\Support\Facades\DB;

class TestModule0 extends Command
{
    protected $signature = 'test:module0';
    protected $description = 'Test Module 0 Logic';

    public function handle()
    {
        $this->info('Starting Module 0 Test...');

        try {
            DB::beginTransaction();

            // 1. Create Tenant
            $tenant = Tenant::firstOrCreate(
                ['slug' => 'test-tenant'],
                ['name' => 'Test Corp', 'domain' => 'test.com']
            );
            $this->info("1. Tenant Created: {$tenant->name}");

            // 2. Create User (System Admin) acting as creator
            $admin = \App\Models\User::firstOrCreate(
                ['email' => 'admin@test.com'],
                [
                    'name' => 'Admin', 
                    'password' => 'secret', 
                    'tenant_id' => $tenant->id,
                ]
            );
            $this->actingAs($admin); // Mock auth for automated assignments

            // 3. Create Role
            $role = Role::create([
                'tenant_id' => $tenant->id,
                'name' => 'HR Manager',
                'slug' => 'hr_manager',
                'created_by' => $admin->id
            ]);
            $this->info("2. Role Created: {$role->name}");

            // 4. Create Permission
            $perm = Permission::create([
                'module' => 'user_management',
                'submodule' => 'user',
                'action' => 'create',
                'description' => 'Can create users'
            ]);
            $this->info("3. Permission Created: {$perm->module}.{$perm->action}");

            // 5. Attach Permission to Role
            $role->permissions()->attach($perm->id, ['data_scope' => 'tenant']);
            $this->info("4. Permission '{$perm->action}' attached to Role '{$role->name}'");

            // 6. Create Target User via Service
            $userService = new UserService();
            $user = $userService->create([
                'name' => 'John Doe',
                'email' => 'john@test.com',
                'password' => 'password',
                'tenant_id' => $tenant->id,
                'status' => 'active'
            ]);
            $this->info("5. User Created: {$user->name}");

            // 7. Assign Role
            $userService->assignRole($user, $role);
            $this->info("6. Role Assigned to User");

            // 8. Verify Permission
            $permService = new PermissionService();
            $hasPerm = $permService->hasPermission($user, 'user_management.user.create');
            
            if ($hasPerm) {
                $this->info("SUCCESS: User has permission 'user_management.user.create'");
            } else {
                $this->error("FAILURE: User missing permission!");
            }
            
            // Clean up
            DB::rollBack();
            $this->info("Test finished. DB rolled back.");

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            DB::rollBack();
        }
    }
    
    private function actingAs($user) {
        \Illuminate\Support\Facades\Auth::login($user);
    }
}
