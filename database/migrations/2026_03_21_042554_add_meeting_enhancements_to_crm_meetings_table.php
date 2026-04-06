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
            if (!Schema::hasColumn('crm_meetings', 'integration_provider')) {
                $table->string('integration_provider')->nullable();
                $table->json('reminders_config')->nullable();
                $table->integer('email_template_id')->nullable();
                $table->boolean('availability_check')->default(1);
                $table->integer('buffer_before')->default(15);
                $table->integer('buffer_after')->default(15);
                $table->string('recurring_rule')->nullable();
                $table->integer('capacity')->default(1);
                $table->string('booking_link', 500)->nullable();
                
                // Usually modifying an ENUM is hard in sqlite/mysql without dbal, so we drop and recreate if applicable, or just add a string field for "extended_status" or modify standard column. We'll use string for status override or keep it simple.
                // $table->enum('status', ['...'])->change(); // Requires dbal. Instead, we'll avoid it or handle at Application level.
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_meetings', function (Blueprint $table) {
            if (Schema::hasColumn('crm_meetings', 'integration_provider')) {
                $table->dropColumn([
                    'integration_provider',
                    'reminders_config',
                    'email_template_id',
                    'availability_check',
                    'buffer_before',
                    'buffer_after',
                    'recurring_rule',
                    'capacity',
                    'booking_link'
                ]);
            }
        });
    }
};
