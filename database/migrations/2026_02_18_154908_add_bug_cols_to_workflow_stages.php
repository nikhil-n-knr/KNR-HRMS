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
            $table->boolean('is_client_visible')->default(false);
            $table->integer('reminder_hours')->nullable();
            $table->boolean('is_final')->default(false);
            $table->boolean('notify_incharge')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->dropColumn(['is_client_visible', 'reminder_hours', 'is_final', 'notify_incharge']);
        });
    }
};
