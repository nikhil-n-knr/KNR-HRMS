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
        // 1. Rename 'Talent' Module to 'Recruitment'
        DB::table('app_modules')
            ->where('key', 'talent')
            ->update([
                'name' => 'Recruitment',
                'key'  => 'recruitment', // revert key to recruitment standard
                'icon' => 'UserGroupIcon'
            ]);

        // 2. Rename 'Jobs' Submodule to 'Talent'
        // First find the module id
        $mod = DB::table('app_modules')->where('key', 'recruitment')->first();
        
        if ($mod) {
            DB::table('app_sub_modules')
                ->where('module_id', $mod->id)
                ->where('key', 'jobs')
                ->update([
                    'name' => 'Talent',
                    'key'  => 'talent_main'
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One way
    }
};
