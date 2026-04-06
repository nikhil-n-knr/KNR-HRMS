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
        // 1. Attendance Logs: The "Master Day Record"
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts'); // Linked to Shift Config
            $table->date('date'); // The conceptual work date (e.g., 2024-03-20)
            
            // Status Flags
            $table->string('status')->default('Absent'); // Present, Absent, Half-Day, Late, On-Leave, Holiday
            $table->boolean('is_late')->default(false);
            $table->boolean('is_half_day')->default(false);
            $table->boolean('is_regularized')->default(false); // If adjusted by manager

            // Calculated Metrics (minutes)
            $table->integer('total_work_minutes')->default(0);
            $table->integer('total_break_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);
            $table->integer('late_minutes')->default(0);
            $table->integer('early_leaving_minutes')->default(0);

            $table->timestamps();
            
            // Unique constraint: One log per employee per day
            $table->unique(['employee_id', 'date']);
        });

        // 2. Attendance Sessions: The "Punch Engine" (In/Out pairs)
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_log_id')->constrained('attendance_logs')->cascadeOnDelete();
            
            $table->timestamp('in_time')->nullable();
            $table->timestamp('out_time')->nullable();
            
            $table->ipAddress('in_ip')->nullable();
            $table->ipAddress('out_ip')->nullable();
            
            $table->string('session_type')->default('Work'); // Work, Break
            $table->string('source')->default('Web'); // Web, Biometric, Mobile
            $table->boolean('is_manual_entry')->default(false); // If added via Request

            $table->timestamps();
        });

        // 3. Attendance Imports: Audit log for "Hardware Dumps"
        Schema::create('attendance_imports', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->integer('record_count');
            $table->integer('success_count')->default(0);
            $table->integer('fail_count')->default(0);
            $table->json('errors')->nullable(); // Store validation errors
            $table->enum('status', ['Processing', 'Completed', 'Failed'])->default('Processing');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_imports');
        Schema::dropIfExists('attendance_sessions');
        Schema::dropIfExists('attendance_logs');
    }
};
