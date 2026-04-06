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
        $moduleId = DB::table('app_modules')->where('key', 'advanced_lms')->value('id');

        if ($moduleId) {
            DB::table('app_sub_modules')
                ->where('module_id', $moduleId)
                ->where('key', 'lms_hub')
                ->update(['route' => 'lms.hub.dashboard']);

            DB::table('app_sub_modules')
                ->where('module_id', $moduleId)
                ->where('key', 'lms_certs')
                ->update(['route' => 'lms.hub.certificates']);

            DB::table('app_sub_modules')
                ->where('module_id', $moduleId)
                ->where('key', 'lms_builder')
                ->update(['route' => 'lms.hub.courses']);

            DB::table('app_sub_modules')
                ->where('module_id', $moduleId)
                ->where('key', 'lms_analytics')
                ->update(['route' => 'lms.hub.analytics']);

            DB::table('app_sub_modules')
                ->where('module_id', $moduleId)
                ->where('key', 'lms_erp_sync')
                ->update(['route' => 'lms.hub.erp']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $moduleId = DB::table('app_modules')->where('key', 'advanced_lms')->value('id');

        if ($moduleId) {
            DB::table('app_sub_modules')
                ->where('module_id', $moduleId)
                ->where('key', 'lms_hub')
                ->update(['route' => 'lms.learn.hub']);

            DB::table('app_sub_modules')
                ->where('module_id', $moduleId)
                ->where('key', 'lms_certs')
                ->update(['route' => 'lms.learn.certificates']);

            DB::table('app_sub_modules')
                ->where('module_id', $moduleId)
                ->where('key', 'lms_builder')
                ->update(['route' => 'lms.admin.builder.index']);

            DB::table('app_sub_modules')
                ->where('module_id', $moduleId)
                ->where('key', 'lms_analytics')
                ->update(['route' => 'lms.admin.analytics.command-center']);

            DB::table('app_sub_modules')
                ->where('module_id', $moduleId)
                ->where('key', 'lms_erp_sync')
                ->update(['route' => 'lms.admin.erp.index']);
        }
    }
};
