<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Tenant
        $tenant = \App\Models\Tenant::firstOrCreate(
            ['slug' => 'default'],
            ['name' => 'Default Company', 'domain' => 'localhost']
        );

        // 2. Create Admin User (Created FIRST to be the 'created_by' for roles)
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'System Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('secret'),
                'tenant_id' => $tenant->id,
                'status' => 'active',
            ]
        );

        // 3. Create Super Admin Role
        $role = \App\Models\Role::firstOrCreate(
            ['slug' => 'super_admin', 'tenant_id' => $tenant->id],
            [
                'name' => 'Super Admin',
                'description' => 'Full System Access',
                'is_system' => true,
                'created_by' => $user->id 
            ]
        );
        
        // 4. Assign Role safely
        $user->roles()->syncWithoutDetaching([
            $role->id => [
                'assigned_by' => $user->id,
                'valid_from' => now(),
                'is_active' => true
            ]
        ]);
        
        // 5. Add Sample Permissions (Optional for Matrix Demo)
        $perms = [
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'leave.view', 'leave.create', 'leave.approve'
        ];
        
        foreach ($perms as $p) {
            $parts = explode('.', $p);
            $perm = \App\Models\Permission::firstOrCreate(
                ['module' => 'user_management', 'submodule' => $parts[0], 'action' => $parts[1]],
                ['description' => "Can {$parts[1]} {$parts[0]}"]
            );
            
            // Attach safely without detaching
            $role->permissions()->syncWithoutDetaching([
                $perm->id => ['data_scope' => 'tenant']
            ]);
        }

        // 6. Run Core Seeders to Restore Modules and Data
        // $this->call(UsersTableSeeder::class);
        $this->call([
            DocumentTemplateSeeder::class,
            ModuleSeeder::class,
            CompanySeeder::class,
            JobCategorySeeder::class,
            SkillSeeder::class,
            JobSeeder::class,
            ScreeningTemplateSeeder::class,
            SeedAttendanceModule::class,
            SeedScrumDemoData::class,
            CleanupNavigationSeeder::class,
            SmartModuleSeeder::class,
            VisitorModuleSeeder::class,
            CardTemplateSeeder::class,
            TaxSeeder::class,
            TaxSectionSeeder::class,
            DevOpsSeeder::class,
            AddLMSModuleSeeder::class,
            CRMModuleSeeder::class,
            IndianSalaryStructureSeeder::class,
            StrategicAnalyticsSeeder::class,
            ComplianceRulesSeeder::class,
            PayrollGoldenDataSeeder::class,
            LmsGoldenDataSeeder::class,
            CRMAchievementSeeder::class,
            CRMMarketingFeaturesSeeder::class,
        ]);
    }
}
