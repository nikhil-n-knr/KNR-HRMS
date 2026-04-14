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
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('manual_progress_percentage')->default(0)->after('status');
            $table->string('manual_status_label')->nullable()->after('manual_progress_percentage');
            $table->integer('project_health_index')->default(100)->after('manual_status_label'); // 0-100
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['manual_progress_percentage', 'manual_status_label', 'project_health_index']);
        });
    }
};
