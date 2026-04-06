<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Fix the submodule route for Meetings Hub
        DB::table('app_sub_modules')
            ->where('key', 'meetings')
            ->update(['route' => 'crm.meetings.index']);

        // 2. Add sender account preference to individual meetings
        Schema::table('crm_meetings', function (Blueprint $table) {
            $table->unsignedBigInteger('sender_account_id')->nullable()->after('created_by');
            
            // Foreign key to crm_email_accounts if they exist (already checked in model)
            // But let's skip strict constraint to avoid potential sync order issues
        });
    }

    public function down(): void
    {
        DB::table('app_sub_modules')
            ->where('key', 'meetings')
            ->update(['route' => 'crm.hub']);

        Schema::table('crm_meetings', function (Blueprint $table) {
            $table->dropColumn('sender_account_id');
        });
    }
};
