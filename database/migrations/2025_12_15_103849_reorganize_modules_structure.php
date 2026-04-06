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
        // 1. Get IDs
        $userMgmtId = DB::table('app_modules')->where('key', 'user_management')->value('id');
        $orgModId = DB::table('app_modules')->where('key', 'org')->value('id');

        if (!$userMgmtId || !$orgModId) {
            // Safety check, maybe log warning but basic seed should exist
            return;
        }

        // 2. Move Departments & Locations to User Management
        DB::table('app_sub_modules')
            ->whereIn('key', ['departments', 'locations'])
            ->update(['module_id' => $userMgmtId]);

        // 3. Disable Scope Management (Redundant)
        DB::table('app_sub_modules')
            ->where('key', 'scope')
            ->update(['status' => false]);
            
        // 4. Disable Organization Module (Now Empty)
        DB::table('app_modules')
            ->where('id', $orgModId)
            ->update(['status' => false]);
            
        // Clear cache
        \Illuminate\Support\Facades\Cache::forget('app_active_modules_tree');
    }

    public function down(): void
    {
        $userMgmtId = DB::table('app_modules')->where('key', 'user_management')->value('id');
        $orgModId = DB::table('app_modules')->where('key', 'org')->value('id');

        if (!$orgModId) return;

        // 1. Re-enable Organization Module
        DB::table('app_modules')
            ->where('id', $orgModId)
            ->update(['status' => true]);

        // 2. Re-enable Scope Management
        DB::table('app_sub_modules')
            ->where('key', 'scope')
            ->update(['status' => true]);

        // 3. Move Departments & Locations back to Organization
        DB::table('app_sub_modules')
            ->whereIn('key', ['departments', 'locations'])
            ->update(['module_id' => $orgModId]);
            
        \Illuminate\Support\Facades\Cache::forget('app_active_modules_tree');
    }
};
