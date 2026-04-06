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
        Schema::table('bug_tickets', function (Blueprint $table) {
            $table->timestamp('sla_due_at')->nullable();
            $table->boolean('is_sla_breached')->default(false);
            $table->string('custom_view_tags')->nullable(); // For custom saved views matching
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bug_tickets', function (Blueprint $table) {
            $table->dropColumn(['sla_due_at', 'is_sla_breached', 'custom_view_tags']);
        });
    }
};
