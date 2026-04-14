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
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->decimal('in_lat', 10, 8)->nullable()->after('in_ip');
            $table->decimal('in_long', 11, 8)->nullable()->after('in_lat');
            $table->decimal('out_lat', 10, 8)->nullable()->after('out_ip');
            $table->decimal('out_long', 11, 8)->nullable()->after('out_lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropColumn(['in_lat', 'in_long', 'out_lat', 'out_long']);
        });
    }
};
