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
        // 1. Visitors Table (The Person)
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('photo_path')->nullable(); // Selfie
            $table->string('company')->nullable();
            $table->foreignId('host_id')->nullable()->constrained('users'); // Employee they are meeting
            $table->string('purpose')->nullable();
            $table->boolean('is_blacklisted')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Events Table (For Bulk Visits)
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('location')->nullable();
            $table->foreignId('organizer_id')->constrained('users');
            $table->integer('guest_limit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('access_areas')->nullable(); // ['Cafeteria', 'Ground Floor']
            $table->timestamps();
        });

        // 3. Visitor Passes / Log (The Visit Instance)
        Schema::create('visitor_passes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained('visitors')->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('events');
            $table->string('pass_code')->unique(); // QR Data
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->string('status')->default('Pre-Registered'); // Pre-Registered, Checked-In, Checked-Out, Overstayed
            $table->string('nda_path')->nullable(); // PDF Path
            $table->json('meta_data')->nullable(); // Wifi Code, Parking Spot, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_passes');
        Schema::dropIfExists('events');
        Schema::dropIfExists('visitors');
    }
};
