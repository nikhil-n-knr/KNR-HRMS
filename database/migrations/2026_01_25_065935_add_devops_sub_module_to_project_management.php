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
        // Check if module ID 22 exists, if not, find by name or fallback
        $pmModule = DB::table('app_modules')->where('name', 'Project Management')->first();
        
        if (!$pmModule) {
            // Create Project Management module if missing (e.g. testing env)
            $moduleId = DB::table('app_modules')->insertGetId([
                'name' => 'Project Management',
                'key' => 'project_management',
                'icon' => 'BriefcaseIcon',
                'order' => 3, 
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
             $moduleId = $pmModule->id;
        }

        // Check if already exists to avoid duplication
        $exists = DB::table('app_sub_modules')
            ->where('module_id', $moduleId)
            ->where('key', 'devops')
            ->exists();

        if (!$exists) {
            DB::table('app_sub_modules')->insert([
                'module_id' => $moduleId,
                'name' => 'Code & Repos',
                'key' => 'devops',
                // Linking to 'devops.providers.index' which is the Admin Config Hub
                // Or if we want a global dashboard, we might change this later.
                'route' => 'devops.providers.index', 
                'order' => 4, // After Timesheets (3)
                'status' => 1,
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
        $pmModule = DB::table('app_modules')->where('name', 'Project Management')->first();
        $moduleId = $pmModule ? $pmModule->id : 22;

        DB::table('app_sub_modules')
            ->where('module_id', $moduleId)
            ->where('key', 'devops')
            ->delete();
    }
};
