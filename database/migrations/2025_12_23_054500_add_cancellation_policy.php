<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_policies', function (Blueprint $table) {
            $table->integer('cancellation_window_hours')->default(6)->comment('Hours before event to allow cancellation');
            $table->boolean('require_approval_for_cancellation')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('attendance_policies', function (Blueprint $table) {
            $table->dropColumn(['cancellation_window_hours', 'require_approval_for_cancellation']);
        });
    }
};
