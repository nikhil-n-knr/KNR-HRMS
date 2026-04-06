<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update Existing Modules with Sidebar Groups
        DB::table('app_modules')->where('key', 'performance')->update(['sidebar_group' => 'HR Administration']);

        // 2. Insert Missing Top-Level Modules
        $modules = [
            [
                'name' => 'Security & Identity',
                'key' => 'security_identity',
                'icon' => 'ShieldCheckIcon',
                'sidebar_group' => 'Operations',
                'order' => 100,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Separation',
                'key' => 'separation',
                'icon' => 'LogoutIcon',
                'sidebar_group' => 'HR Administration',
                'order' => 110,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'My Finances',
                'key' => 'my_finances',
                'icon' => 'CurrencyRupeeIcon',
                'sidebar_group' => 'My Workspace',
                'order' => 120,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'My Career',
                'key' => 'my_career',
                'icon' => 'BriefcaseIcon',
                'sidebar_group' => 'My Workspace',
                'order' => 130,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CRM Platform',
                'key' => 'crm',
                'icon' => 'UserGroupIcon',
                'sidebar_group' => 'Platform',
                'order' => 140,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'System Admin',
                'key' => 'system_admin',
                'icon' => 'CogIcon',
                'sidebar_group' => 'Platform',
                'order' => 150,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'DevOps Center',
                'key' => 'devops_link',
                'icon' => 'CommandLineIcon',
                'sidebar_group' => 'Platform',
                'order' => 160,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($modules as $mod) {
            DB::table('app_modules')->updateOrInsert(['key' => $mod['key']], $mod);
        }

        // 3. Insert Missing Sub-Modules
        $secModule = DB::table('app_modules')->where('key', 'security_identity')->first();
        $sepModule = DB::table('app_modules')->where('key', 'separation')->first();
        $finModule = DB::table('app_modules')->where('key', 'my_finances')->first();
        $carModule = DB::table('app_modules')->where('key', 'my_career')->first();
        $crmModule = DB::table('app_modules')->where('key', 'crm')->first();
        $sysModule = DB::table('app_modules')->where('key', 'system_admin')->first();
        $devModule = DB::table('app_modules')->where('key', 'devops_link')->first();

        $subModules = [
            // Security
            ['module_id' => $secModule->id, 'name' => 'ID Card Studio', 'key' => 'identity_cards', 'route' => 'identity.index', 'order' => 1],
            
            // Separation
            ['module_id' => $sepModule->id, 'name' => 'Clearance Dashboard', 'key' => 'clearances', 'route' => 'admin.clearances.index', 'order' => 1],
            ['module_id' => $sepModule->id, 'name' => 'My Clearance', 'key' => 'my_clearance', 'route' => 'employee.clearances.index', 'order' => 2],
            
            // My Finances
            ['module_id' => $finModule->id, 'name' => 'My Payslips', 'key' => 'my_payslips', 'route' => 'employee.payslips.index', 'order' => 1],
            ['module_id' => $finModule->id, 'name' => 'My Claims', 'key' => 'my_expenses', 'route' => 'employee.expenses.index', 'order' => 2],
            ['module_id' => $finModule->id, 'name' => 'My Loans', 'key' => 'my_loans', 'route' => 'employee.loans.index', 'order' => 3],
            ['module_id' => $finModule->id, 'name' => 'IT Declarations', 'key' => 'my_tax', 'route' => 'employee.tax.index', 'order' => 4],
            
            // My Career
            ['module_id' => $carModule->id, 'name' => 'Referrals', 'key' => 'referrals', 'route' => 'employee.referrals.index', 'order' => 1],
            
            // CRM
            ['module_id' => $crmModule->id, 'name' => 'CRM Dashboard', 'key' => 'crm_hub', 'route' => 'crm.hub', 'order' => 1],
            ['module_id' => $crmModule->id, 'name' => 'Leads', 'key' => 'leads', 'route' => 'crm.hub', 'order' => 2],
            ['module_id' => $crmModule->id, 'name' => 'Contacts', 'key' => 'contacts', 'route' => 'crm.hub', 'order' => 3],
            ['module_id' => $crmModule->id, 'name' => 'Deals & Pipeline', 'key' => 'deals', 'route' => 'crm.hub', 'order' => 4],
            ['module_id' => $crmModule->id, 'name' => 'Marketing', 'key' => 'marketing', 'route' => 'crm.hub', 'order' => 5],
            ['module_id' => $crmModule->id, 'name' => 'Support', 'key' => 'support', 'route' => 'crm.hub', 'order' => 6],
            
            // System Admin
            ['module_id' => $sysModule->id, 'name' => 'Module Manager', 'key' => 'modules', 'route' => 'admin.modules.index', 'order' => 1],
            
            // DevOps
            ['module_id' => $devModule->id, 'name' => 'Overview (Stats)', 'key' => 'overview', 'route' => 'admin.devops.dashboard', 'order' => 1],
            ['module_id' => $devModule->id, 'name' => 'Providers & Config', 'key' => 'config', 'route' => 'admin.devops.providers.index', 'order' => 2],
        ];

        foreach ($subModules as $sub) {
            DB::table('app_sub_modules')->updateOrInsert(
                ['module_id' => $sub['module_id'], 'key' => $sub['key']],
                array_merge($sub, ['status' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    public function down(): void
    {
        // No simple down for data migrations but we could delete them by keys
    }
};
