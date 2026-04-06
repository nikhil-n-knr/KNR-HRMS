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
        // 1. Enhance Attendance Policies for Granular Control
        Schema::table('attendance_policies', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_policies', 'rules')) {
                $table->json('rules')->nullable()->after('name'); 
            }
            if (!Schema::hasColumn('attendance_policies', 'wfh_policy')) {
                $table->json('wfh_policy')->nullable()->after('rules'); 
            }
            // Robust Overtime Policy Handling
            if (Schema::hasColumn('attendance_policies', 'overtime_rule') && !Schema::hasColumn('attendance_policies', 'overtime_policy')) {
                 // Try rename if 'overtime_rule' exists and 'overtime_policy' does not
                 \Illuminate\Support\Facades\DB::statement("ALTER TABLE attendance_policies CHANGE overtime_rule overtime_policy LONGTEXT");
            } elseif (!Schema::hasColumn('attendance_policies', 'overtime_policy')) {
                 // Only add if it doesn't exist
                 $table->json('overtime_policy')->nullable()->after('wfh_policy');
            }
            if (!Schema::hasColumn('attendance_policies', 'priority')) {
                $table->integer('priority')->default(0)->after('sandwich_rule_enabled'); // Higher = More important
            }
        });

        // 2. Link Policies to Organizational Units (Hierarchy)
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'attendance_policy_id')) {
                $table->foreignId('attendance_policy_id')->nullable()->after('department_id')->constrained('attendance_policies')->nullOnDelete();
            }
        });

        Schema::table('departments', function (Blueprint $table) {
            if (!Schema::hasColumn('departments', 'attendance_policy_id')) {
                $table->foreignId('attendance_policy_id')->nullable()->after('name')->constrained('attendance_policies')->nullOnDelete();
            }
        });

        // 3. Enhance Gamification Rules
        Schema::table('point_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('point_rules', 'event_category')) {
                $table->string('event_category')->default('core')->after('event_key');
            }
            if (!Schema::hasColumn('point_rules', 'condition_logic')) {
                $table->text('condition_logic')->nullable()->after('points');
            }
        });

        // 4. Create Streaks Table
        Schema::create('employee_streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('streak_type'); // e.g. 'perfect_attendance', 'early_bird'
            $table->integer('current_streak')->default(0);
            $table->integer('max_streak')->default(0);
            $table->timestamp('last_incremented_at')->nullable();
            $table->timestamps();
            
            $table->unique(['employee_id', 'streak_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_streaks');

        Schema::table('point_rules', function (Blueprint $table) {
            $table->dropColumn(['event_category', 'condition_logic']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['attendance_policy_id']);
            $table->dropColumn('attendance_policy_id');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['attendance_policy_id']);
            $table->dropColumn('attendance_policy_id');
        });

        Schema::table('attendance_policies', function (Blueprint $table) {
            $table->dropColumn(['rules', 'wfh_policy', 'priority']);
            if (Schema::hasColumn('attendance_policies', 'overtime_policy')) {
                 $table->renameColumn('overtime_policy', 'overtime_rule');
            }
        });
    }
};

