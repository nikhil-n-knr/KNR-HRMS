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
        Schema::table('git_module_mappings', function (Blueprint $table) {
            // Make existing project_module_id nullable if we want hybrid support
            $table->foreignId('project_module_id')->nullable()->change();
            
            // Add App Sub Module
            $table->foreignId('app_sub_module_id')->nullable()->constrained('app_sub_modules')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('git_module_mappings', function (Blueprint $table) {
            $table->dropForeign(['app_sub_module_id']);
            $table->dropColumn('app_sub_module_id');
            $table->foreignId('project_module_id')->nullable(false)->change();
        });
    }
};
