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
        // 1. Insert Parent Module
        $moduleId = DB::table('app_modules')->insertGetId([
            'name' => 'Assets & Store',
            'key' => 'assets_module',
            'icon' => 'CubeIcon', // Optional, matches schema
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Insert Sub Modules
        DB::table('app_sub_modules')->insert([
            [
                'module_id' => $moduleId,
                'name' => 'Fixed Assets',
                'key' => 'assets',
                'route' => 'admin.assets.index',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => $moduleId,
                'name' => 'Inventory / Store',
                'key' => 'inventory',
                'route' => 'admin.inventory.index',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => $moduleId,
                'name' => 'Physical Docs',
                'key' => 'phy_docs',
                'route' => 'admin.physical-documents.index',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function down(): void
    {
        $moduleId = DB::table('app_modules')->where('key', 'assets_module')->value('id');
        if ($moduleId) {
            DB::table('app_sub_modules')->where('module_id', $moduleId)->delete();
            DB::table('app_modules')->where('id', $moduleId)->delete();
        }
    }
};
