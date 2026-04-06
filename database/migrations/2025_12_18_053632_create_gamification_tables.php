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
        // 1. Point Rules (The Config)
        Schema::create('point_rules', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique(); // e.g. 'clock_in_early', 'timesheet_submit_ontime'
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('points')->default(0); // Can be negative
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Employee Points Ledger (The Bank)
        Schema::create('employee_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('point_rule_id')->nullable()->constrained(); // Nullable for ad-hoc awards
            
            $table->integer('points_awarded');
            $table->string('event_reference')->nullable(); // Polymorphic Ref ID (e.g., AttendanceLog:55)
            $table->text('reason')->nullable();
            
            $table->timestamps();
            
            // Running Balance Snapshot mainly for Leaderboards performance
            // In a real high-scale system we might use a separate aggregated table, 
            // but for this scale, summing on the fly or a cached balance on Employee model is fine.
        });

        // 3. Badges (The Achievements)
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // e.g., 'early-bird-streak'
            $table->string('icon')->nullable(); // path or icon name
            $table->text('criteria_description');
            $table->integer('points_bonus')->default(0); // Bonus Points for getting the badge
            $table->timestamps();
        });

        // 4. Employee Badges (The Collection)
        Schema::create('employee_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('awarded_at');
            $table->timestamps();
            
            $table->unique(['employee_id', 'badge_id']); // Can only earn once? Or maybe tiered?
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('employee_points');
        Schema::dropIfExists('point_rules');
    }
};
