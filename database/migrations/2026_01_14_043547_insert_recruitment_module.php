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
        $moduleId = DB::table('app_modules')->insertGetId([
            'name' => 'Talent', // As per navigation controller logic
            'key' => 'talent',
            'icon' => 'UserGroupIcon', // Assuming Icon library name
            'route' => 'talent.hub',
            'order' => 5, // Adjust as needed
            'status' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('app_sub_modules')->insert([
            ['module_id' => $moduleId, 'name' => 'Jobs', 'key' => 'jobs', 'route' => 'talent.jobs.index', 'order' => 1, 'status' => true],
            ['module_id' => $moduleId, 'name' => 'Candidates', 'key' => 'candidates', 'route' => null, 'order' => 2, 'status' => true],
            ['module_id' => $moduleId, 'name' => 'Interviews', 'key' => 'interviews', 'route' => null, 'order' => 3, 'status' => true],
            ['module_id' => $moduleId, 'name' => 'Offers', 'key' => 'offers', 'route' => null, 'order' => 4, 'status' => true],
            ['module_id' => $moduleId, 'name' => 'Onboarding', 'key' => 'onboarding', 'route' => null, 'order' => 5, 'status' => true],
        ]);
    }

    public function down(): void
    {
        // Remove if needed
        // DB::table('app_modules')->where('key', 'talent')->delete();
    }
};
