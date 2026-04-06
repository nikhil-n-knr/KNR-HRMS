<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Sample code for backend-only creation of the Super Admin role.
     * Run: php artisan db:seed --class=SuperAdminSeeder
     */
    public function run(): void
    {
        // 1. Ensure Tenant
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'default'],
            ['name' => 'Default Company', 'domain' => 'localhost']
        );
        
        // 2. Sample: Create Super Admin Role if not exists
        // This is protected from API creation, so we do it here.
        $role = Role::firstOrCreate(
            ['slug' => 'super_admin', 'tenant_id' => $tenant->id],
            [
                'name' => 'Super Admin',
                'description' => 'Full System Access (Backend Protected)',
                'is_system' => true,
                // Assuming we have a system user or using ID 1
                'created_by' => 1 
            ]
        );

        $this->command->info("Super Admin Role ID: {$role->id}");
        
        // 3. Sample: Assign to a specific user (e.g., ID 1)
        /*
        $user = User::find(1);
        if ($user && !$user->roles->contains($role->id)) {
            $user->roles()->attach($role->id, [
                'assigned_by' => 1,
                'valid_from' => now(),
                'is_active' => true
            ]);
            $this->command->info("Assigned Super Admin to User ID 1");
        }
        */
    }
}
