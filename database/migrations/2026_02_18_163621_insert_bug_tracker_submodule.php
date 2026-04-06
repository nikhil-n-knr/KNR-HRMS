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
        // 1. Get Project Management Module ID
        $pmModule = \Illuminate\Support\Facades\DB::table('app_modules')->where('key', 'project_management')->first();

        if ($pmModule) {
            // 2. Insert Bug Tracker Sub-Module
            \Illuminate\Support\Facades\DB::table('app_sub_modules')->insertOrIgnore([
                'module_id' => $pmModule->id,
                'key' => 'bugs',
                'name' => 'Bug Tracker',
                'route' => 'bugs.index',
                // 'icon' => 'BugAntIcon', // Column does not exist
                'status' => true,
                'order' => 99, // Place it at the end
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('app_sub_modules')->where('key', 'bugs')->delete();
    }
};
