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
        // 1. Insert Master Module: Advanced LMS
        $moduleId = DB::table('app_modules')->insertGetId([
            'name'          => 'Advanced LMS',
            'key'           => 'advanced_lms',
            'icon'          => 'AcademicCapIcon',
            'sidebar_group' => 'Strategic Multipliers',
            'order'         => 10,
            'status'        => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // 2. Insert Sub-Modules for Learners and Admins
        DB::table('app_sub_modules')->insert([
            [
                'module_id' => $moduleId,
                'name'      => 'Learning Hub',
                'key'       => 'lms_hub',
                'route'     => 'lms.learn.hub',
                'status'    => true,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'module_id' => $moduleId,
                'name'      => 'Certificates Wallet',
                'key'       => 'lms_certs',
                'route'     => 'lms.learn.certificates',
                'status'    => true,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'module_id' => $moduleId,
                'name'      => 'Course Builder',
                'key'       => 'lms_builder',
                'route'     => 'lms.admin.builder.index',
                'status'    => true,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'module_id' => $moduleId,
                'name'      => 'Command Center',
                'key'       => 'lms_analytics',
                'route'     => 'lms.admin.analytics.command-center',
                'status'    => true,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'module_id' => $moduleId,
                'name'      => 'ERP Integration',
                'key'       => 'lms_erp_sync',
                'route'     => 'lms.admin.erp.index',
                'status'    => true,
                'created_at'=> now(),
                'updated_at'=> now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $moduleId = DB::table('app_modules')->where('key', 'advanced_lms')->value('id');
        if ($moduleId) {
            DB::table('app_sub_modules')->where('module_id', $moduleId)->delete();
            DB::table('app_modules')->where('id', $moduleId)->delete();
        }
    }
};
