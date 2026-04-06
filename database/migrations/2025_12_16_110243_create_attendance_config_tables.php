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
        // 1. Shifts: Rules for work timing
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., General, Morning
            $table->time('start_time');
            $table->time('end_time');
            $table->json('work_days'); // ["Mon", "Tue"]
            $table->integer('grace_late_entry')->default(15); // Minutes
            $table->integer('grace_early_exit')->default(10); // Minutes
            $table->json('break_policy')->nullable(); // { "duration": 60, "paid": false }
            $table->json('ip_restrictions')->nullable(); // ["192.168.1.1"]
            $table->boolean('is_default')->default(false);
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Attendance Policies: High-level logic (OT, Deductions)
        Schema::create('attendance_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Standard, Strict
            $table->integer('late_mark_threshold')->default(3); // 3 lates = 1 deduction
            $table->json('deduction_rule')->nullable(); // { "deduct_leave": 0.5, "type": "CL" }
            $table->json('overtime_rule')->nullable(); // { "rate": 1.0, "min_minutes": 30 }
            $table->boolean('sandwich_rule_enabled')->default(false);
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // 3. Holidays: Context Calendar
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name');
            $table->enum('type', ['Fixed', 'Restricted', 'AdHoc'])->default('Fixed');
            $table->json('applies_to_locations')->nullable(); // ["All"] or [1, 2]
            $table->boolean('is_recurring')->default(true);
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // 4. Shift Rotations: Moving patterns
        Schema::create('shift_rotations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('pattern'); // ["Shift A", "Shift B", "Off"]
            $table->enum('frequency', ['Weekly', 'Bi-Weekly', 'Monthly']);
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // 5. API Keys: For Hardware
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('device_name');
            $table->string('api_token', 64)->unique();
            $table->ipAddress('allowed_ip')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('shift_rotations');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('attendance_policies');
        Schema::dropIfExists('shifts');
    }
};
