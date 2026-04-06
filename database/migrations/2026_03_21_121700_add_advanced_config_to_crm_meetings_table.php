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
        Schema::table('crm_meetings', function (Blueprint $table) {
            if (!Schema::hasColumn('crm_meetings', 'reminders_config')) {
                $table->json('reminders_config')->nullable();
            }
            if (!Schema::hasColumn('crm_meetings', 'buffer_before')) {
                $table->integer('buffer_before')->default(0);
            }
            if (!Schema::hasColumn('crm_meetings', 'buffer_after')) {
                $table->integer('buffer_after')->default(0);
            }
            if (!Schema::hasColumn('crm_meetings', 'recurring_rule')) {
                $table->string('recurring_rule')->nullable();
            }
            if (!Schema::hasColumn('crm_meetings', 'capacity')) {
                $table->integer('capacity')->default(0);
            }
            if (!Schema::hasColumn('crm_meetings', 'booking_link')) {
                $table->string('booking_link')->nullable();
            }
            if (!Schema::hasColumn('crm_meetings', 'sender_account_id')) {
                $table->foreignId('sender_account_id')->nullable()->constrained('crm_email_accounts')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_meetings', function (Blueprint $table) {
            $table->dropColumn(['reminders_config', 'buffer_before', 'buffer_after', 'recurring_rule', 'capacity', 'booking_link', 'sender_account_id']);
        });
    }
};
