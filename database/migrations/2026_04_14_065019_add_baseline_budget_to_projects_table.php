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
        Schema::table('projects', function (Blueprint $blueprint) {
            $blueprint->decimal('estimated_hours', 10, 2)->default(0)->after('deadline');
            $blueprint->decimal('original_estimated_hours', 10, 2)->nullable()->after('estimated_hours');
            $blueprint->date('original_planned_deadline')->nullable()->after('deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['original_estimated_hours', 'original_planned_deadline']);
        });
    }
};
