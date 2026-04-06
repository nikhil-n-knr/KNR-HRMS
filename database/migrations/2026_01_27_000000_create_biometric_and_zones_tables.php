<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendance_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('radius_meters')->default(100);
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('biometric_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('serial_number')->unique();
            $table->string('ip_address')->nullable();
            $table->integer('port')->default(4370);
            $table->string('location_name')->nullable(); // Helper
            $table->foreignId('attendance_zone_id')->nullable()->constrained('attendance_zones')->nullOnDelete();
            $table->string('status')->default('offline'); // online, offline, error
            $table->timestamp('last_sync_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('biometric_devices');
        Schema::dropIfExists('attendance_zones');
    }
};
