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
            $table->foreignId('email_account_id')->nullable()->after('tenant_id')->constrained('crm_email_accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_email_threads', function (Blueprint $table) {
            $table->dropForeign(['email_account_id']);
            $table->dropColumn('email_account_id');
        });
    }
};
