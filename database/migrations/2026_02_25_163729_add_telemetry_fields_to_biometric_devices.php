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
        Schema::table('biometric_devices', function (Blueprint $table) {
            // Already has: name, serial_number, ip_address, port, location_name, attendance_zone_id, status, last_sync_at, is_active
            // Adding extra telemetry and mapping fields
            if (!Schema::hasColumn('biometric_devices', 'username')) {
                $table->string('username')->nullable()->after('port');
            }
            if (!Schema::hasColumn('biometric_devices', 'password')) {
                $table->string('password')->nullable()->after('username');
            }
            if (!Schema::hasColumn('biometric_devices', 'protocol')) {
                $table->string('protocol')->default('TCP')->after('password');
            }
            if (!Schema::hasColumn('biometric_devices', 'description')) {
                $table->text('description')->nullable()->after('protocol');
            }
            if (!Schema::hasColumn('biometric_devices', 'heartbeat_interval')) {
                $table->integer('heartbeat_interval')->default(60)->after('description'); // seconds
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biometric_devices', function (Blueprint $table) {
            $table->dropColumn(['username', 'password', 'protocol', 'description', 'heartbeat_interval']);
        });
    }
};
