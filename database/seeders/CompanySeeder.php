<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Team;
use App\Models\Role;
use App\Models\Department;
use App\Models\Location;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Keep existing users/roles created by DatabaseSeeder if necessary
        // Or just let it run. But truncation is aggressive.
        // For now, let's just use the first tenant ID.
        $tenant = \App\Models\Tenant::first();
        $tenantId = $tenant ? $tenant->id : 1;

        // 1. Create Locations
        $hq = Location::create(['name' => 'Corporate HQ', 'code' => 'HQ', 'city' => 'New York', 'tenant_id' => $tenantId]);
        $remote = Location::create(['name' => 'Remote', 'code' => 'REM', 'city' => 'Remote', 'tenant_id' => $tenantId]);
        $locations = collect([$hq, $remote]);

        // 2. Create Admin User
        User::unguard();
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('secret'),
                'employee_id' => 'EMP001',
                'tenant_id' => $tenantId,
                'location_id' => $hq->id
            ]
        );
        User::reguard();

        // Create Admin Employee Profile
        \App\Models\Employee::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'uuid' => Str::uuid(),
                'user_id' => $adminUser->id,
                'tenant_id' => $tenantId,
                'employee_code' => 'EMP001',
                'first_name' => 'System',
                'last_name' => 'Admin',
                'designation' => 'Super Administrator',
                'joining_date' => now(),
                'status' => 'active',
                'location_id' => $hq->id
            ]
        );

        // 3. Create Roles
        $roles = [
            'admin' => 'Super Admin',
            'hr' => 'HR Manager',
            'manager' => 'Team Manager',
            'employee' => 'Employee',
            'team_lead' => 'Team Lead'
        ];

        $roleModels = [];
        foreach ($roles as $slug => $name) {
            $roleModels[$slug] = Role::firstOrCreate(
                ['slug' => $slug, 'tenant_id' => $tenantId],
                [
                    'name' => $name, 
                    'is_system' => true, 
                    'created_by' => $adminUser->id
                ]
            );
        }
        
        // Give standard admin the super role if it exists 
        if (isset($roleModels['admin'])) {
            $adminUser->roles()->syncWithoutDetaching([
                $roleModels['admin']->id => [
                    'assigned_by' => $adminUser->id, 
                    'is_active' => true, 
                    'valid_from' => now()
                ]
            ]);
        }

        // 4. Create Departments (and Teams)
        $deptConfigs = [
            'Human Resources' => ['code' => 'HR', 'manager_role' => 'hr'],
            'Sales & Marketing' => ['code' => 'SLS', 'manager_role' => 'manager'],
            'Engineering' => ['code' => 'ENG', 'manager_role' => 'manager'],
        ];

        $deptModels = [];
        $teamModels = []; 
        
        $rootTeam = Team::firstOrCreate(['name' => 'Organization'], ['manager_id' => $adminUser->id]);

        foreach ($deptConfigs as $name => $conf) {
            $d = Department::firstOrCreate(
                ['code' => $conf['code'], 'tenant_id' => $tenantId],
                ['name' => $name, 'parent_id' => null]
            );
            $deptModels[$name] = $d;

            $t = Team::firstOrCreate(
                ['name' => $name],
                ['parent_team_id' => $rootTeam->id]
            );
            $teamModels[$name] = $t;
        }

        // 5. Create 50 Employees
        $this->command->info('Creating 50 employees...');
        
        for ($i = 1; $i <= 50; $i++) {
            $paddedId = str_pad($i + 100, 3, '0', STR_PAD_LEFT);
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();
            $email = strtolower("$firstName.$lastName@company.com");
            
            // Random Assignments
            $deptName = fake()->randomElement(array_keys($deptConfigs));
            $dept = $deptModels[$deptName];
            $loc = $locations->random();

            $user = User::firstOrCreate(
                ['employee_id' => "EMP{$paddedId}", 'tenant_id' => $tenantId],
                [
                    'name' => "$firstName $lastName",
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'department_id' => $dept->id,
                    'location_id' => $loc->id,
                    'team_id' => $teamModels[$deptName]->id
                ]
            );
            
            \App\Models\Employee::firstOrCreate(
                ['employee_code' => "EMP{$paddedId}", 'tenant_id' => $tenantId],
                [
                    'uuid' => Str::uuid(),
                    'user_id' => $user->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'department_id' => $dept->id,
                    'location_id' => $loc->id,
                    'designation' => fake()->jobTitle(),
                    'joining_date' => fake()->date(),
                    'status' => 'active'
                ]
            );

            // Assign standard role safely
            $user->roles()->syncWithoutDetaching([
                $roleModels['employee']->id => [
                    'assigned_by' => $adminUser->id,
                    'is_active' => true,
                    'valid_from' => now()
                ]
            ]);
            
            // Assign Manager Role appropriately safely
            if ($i % 5 === 0) {
                $managerRoleSlug = $deptConfigs[$deptName]['manager_role']; 
                $user->roles()->syncWithoutDetaching([
                    $roleModels[$managerRoleSlug]->id => [
                         'assigned_by' => $adminUser->id,
                         'is_active' => true,
                         'valid_from' => now()
                    ]
                ]);
                
                // Make Head of Dept/Team
                $dept->update(['head_id' => $user->id]);
                $teamModels[$deptName]->update(['manager_id' => $user->id]);
            }
        }

        $this->command->info('Company Seeding Complete.');
    }
}
