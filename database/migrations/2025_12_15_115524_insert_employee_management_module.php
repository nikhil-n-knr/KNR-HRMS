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
        DB::table('app_modules')->insert([
            'name' => 'Employee Management',
            'key' => 'employee_management',
            'icon' => 'UserGroupIcon', // Placeholder, frontend maps this or uses default
            'order' => 2,
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('app_modules')->where('key', 'employee_management')->delete();
    }
};
