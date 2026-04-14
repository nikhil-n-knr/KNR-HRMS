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
            $table->boolean('is_locked')->default(false)->after('status');
            $table->json('plan_lock_recipients')->nullable()->after('is_locked');
        });

        Schema::table('project_tasks', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('status');
            $table->decimal('total_efforts', 12, 2)->default(0)->after('estimated_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['is_locked', 'plan_lock_recipients']);
        });

        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropColumn(['is_locked', 'total_efforts']);
        });
    }
};
