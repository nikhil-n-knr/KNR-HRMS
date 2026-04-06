<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_meetings', function (Blueprint $table) {
            $table->string('timezone')->default('UTC')->after('end_time');
            $table->unsignedBigInteger('rescheduled_from_id')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('crm_meetings', function (Blueprint $table) {
            $table->dropColumn(['timezone', 'rescheduled_from_id']);
        });
    }
};
