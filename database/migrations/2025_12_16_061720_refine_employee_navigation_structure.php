<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename 'All Employees' to 'Employee Master'
        \Illuminate\Support\Facades\DB::table('app_sub_modules')
            ->where('key', 'employees')
            ->update(['name' => 'Employee Master', 'updated_at' => now()]);

        // 2. Remove 'Add New' (Redundant)
        \Illuminate\Support\Facades\DB::table('app_sub_modules')
            ->where('key', 'employee_create')
            ->delete();

        // Clear Cache
        \Illuminate\Support\Facades\Cache::forget('app_active_modules_tree');
    }

    public function down(): void
    {
        // Revert Name
        \Illuminate\Support\Facades\DB::table('app_sub_modules')
            ->where('key', 'employees')
            ->update(['name' => 'All Employees', 'updated_at' => now()]);

        // Restore 'Add New'
        $moduleId = \Illuminate\Support\Facades\DB::table('app_modules')->where('key', 'employee_management')->value('id');
        if ($moduleId) {
            \Illuminate\Support\Facades\DB::table('app_sub_modules')->updateOrInsert(
                ['key' => 'employee_create'],
                [
                    'module_id' => $moduleId,
                    'name' => 'Add New',
                    'route' => '/employees/create',
                    'order' => 2,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        \Illuminate\Support\Facades\Cache::forget('app_active_modules_tree');
    }
};
