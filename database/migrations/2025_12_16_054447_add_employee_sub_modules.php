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
        $moduleId = \Illuminate\Support\Facades\DB::table('app_modules')->where('key', 'employee_management')->value('id');

        if ($moduleId) {
            \Illuminate\Support\Facades\DB::table('app_sub_modules')->insert([
                [
                    'module_id' => $moduleId,
                    'name' => 'All Employees',
                    'key' => 'employees', // Key for permission
                    'route' => '/employees',
                    'order' => 1,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'module_id' => $moduleId,
                    'name' => 'Add New',
                    'key' => 'employee_create',
                    'route' => '/employees/create',
                    'order' => 2,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
            
            // Clear cache
             \Illuminate\Support\Facades\Cache::forget('app_active_modules_tree');
        }
    }

    public function down(): void
    {
        $moduleId = \Illuminate\Support\Facades\DB::table('app_modules')->where('key', 'employee_management')->value('id');
        if ($moduleId) {
            \Illuminate\Support\Facades\DB::table('app_sub_modules')
                ->where('module_id', $moduleId)
                ->whereIn('key', ['employees', 'employee_create'])
                ->delete();
                
             \Illuminate\Support\Facades\Cache::forget('app_active_modules_tree');
        }
    }
};
