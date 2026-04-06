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
        Schema::table('crm_email_threads', function (Blueprint $table) {
            $table->string('mapping_status')->default('new')->after('external_thread_id');
        });

        Schema::table('crm_email_messages', function (Blueprint $table) {
            $table->boolean('is_acknowledged')->default(false)->after('resend_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_email_threads', function (Blueprint $table) {
            $table->dropColumn('mapping_status');
        });

        Schema::table('crm_email_messages', function (Blueprint $table) {
            $table->dropColumn('is_acknowledged');
        });
    }
};
