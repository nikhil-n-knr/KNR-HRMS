<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'overtime_requests',
            'wfh_requests',
            'shift_swaps',
            'attendance_regularizations',
            'floating_holiday_requests',
            'leave_requests'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'status')) {
                // Change enum to varchar
                DB::statement("ALTER TABLE `{$table}` MODIFY `status` VARCHAR(50) DEFAULT 'Pending'");
            }
        }
    }

    public function down(): void
    {
        // Not easily reversible without losing data if they have 'Cancelled'
    }
};
