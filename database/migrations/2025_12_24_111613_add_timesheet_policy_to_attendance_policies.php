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
        Schema::table('attendance_policies', function (Blueprint $table) {
            // Adding timesheet policy configuration (JSON)
            // Keys: daily_min_hours, daily_max_hours, allow_future_days, require_project
            if (!Schema::hasColumn('attendance_policies', 'timesheet_policy')) {
                $table->json('timesheet_policy')->nullable()->after('overtime_policy');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_policies', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_policies', 'timesheet_policy')) {
                $table->dropColumn('timesheet_policy');
            }
        });
    }
};
