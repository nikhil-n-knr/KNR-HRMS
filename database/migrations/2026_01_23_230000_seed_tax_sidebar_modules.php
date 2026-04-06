<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create or Get 'HR & Payroll' Module
        $moduleId = DB::table('app_modules')->where('key', 'hr_payroll')->value('id');

        if (!$moduleId) {
            $moduleId = DB::table('app_modules')->insertGetId([
                'name' => 'HR & Payroll',
                'key' => 'hr_payroll',
                'icon' => 'BanknotesIcon',
                // 'description' => 'Payroll, Expenses, Loans & Tax Management', // Column does not exist
                'status' => true,
                'order' => 5, // Adjust as needed
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Define Submodules
        $subModules = [
            [
                'key' => 'payroll',
                'name' => 'Payroll Processing',
                'route' => 'hr.payroll.index',
                'order' => 1
            ],
            [
                'key' => 'expenses',
                'name' => 'Expenses & Claims',
                'route' => 'hr.expenses.dashboard',
                'order' => 2
            ],
            [
                'key' => 'loans',
                'name' => 'Loans & Advances',
                'route' => 'hr.loans.index',
                'order' => 3
            ],
            // New Tax Modules
            [
                'key' => 'tax_config',
                'name' => 'Tax Configuration',
                'route' => 'hr.tax.configuration.index',
                'order' => 4
            ],
            [
                'key' => 'tax_proofs',
                'name' => 'Proof Verification',
                'route' => 'hr.tax.proofs.index',
                'order' => 5
            ],
            [
                'key' => 'tax_declarations',
                'name' => 'Employee Declarations',
                'route' => 'hr.tax.declarations.index',
                'order' => 6
            ],
            [
                'key' => 'compliance',
                'name' => 'Statutory Compliance',
                'route' => 'hr.compliance.index',
                'order' => 7
            ],
            // Admin / Config Modules
            [
                'key' => 'bulk_salary',
                'name' => 'Bulk Salary Management',
                'route' => 'hr.payroll.bulk',
                'order' => 8
            ],
            [
                'key' => 'salary_structures',
                'name' => 'Salary Settings',
                'route' => 'admin.salary-structures.index',
                'order' => 9
            ],
             [
                'key' => 'expense_config',
                'name' => 'Expense Categories',
                'route' => 'admin.expense-categories.index',
                'order' => 10
            ],
            [
                'key' => 'workflows',
                'name' => 'Workflow Builder',
                'route' => 'admin.workflows.index',
                'order' => 11
            ]
        ];

        foreach ($subModules as $sub) {
            DB::table('app_sub_modules')->updateOrInsert(
                ['module_id' => $moduleId, 'key' => $sub['key']],
                [
                    'name' => $sub['name'],
                    'route' => $sub['route'],
                    'status' => true,
                    'order' => $sub['order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We generally don't delete modules on rollback to avoid data loss on constrained tables, 
        // but for a seeder migration, we can disable them.
        $moduleId = DB::table('app_modules')->where('key', 'hr_payroll')->value('id');
        if ($moduleId) {
            DB::table('app_sub_modules')->where('module_id', $moduleId)->update(['status' => false]);
            DB::table('app_modules')->where('id', $moduleId)->update(['status' => false]);
        }
    }
};
