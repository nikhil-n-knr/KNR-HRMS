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
        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->boolean('requires_verification')->default(false);
            $table->integer('auto_close_days')->nullable(); // Helper for auto-closure
            $table->foreignId('assigned_team_id')->nullable()->constrained('teams')->onDelete('set null'); // Auto-assign team
        });

        Schema::table('bug_tickets', function (Blueprint $table) {
            $table->timestamp('system_closed_at')->nullable(); // For auto-closure tracking
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->dropForeign(['assigned_team_id']);
            $table->dropColumn(['requires_verification', 'auto_close_days', 'assigned_team_id']);
        });

        Schema::table('bug_tickets', function (Blueprint $table) {
            $table->dropColumn('system_closed_at');
        });
    }
};
