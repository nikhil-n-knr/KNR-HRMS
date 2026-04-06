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
        $moduleId = DB::table('app_modules')->where('key', 'hr_payroll')->value('id');

        if ($moduleId) {
            $subModules = [
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action needed for safety
    }
};
