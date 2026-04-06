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
        // 1. Leave Types (Configuration)
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(1); // Multi-tenancy support
            $table->string('name'); // Annual, Sick, Casual
            $table->string('code')->unique(); // AL, SL, CL
            $table->string('color')->default('#10b981'); // UI Badge Color
            $table->integer('days_allowed_per_year')->default(0);
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_approval')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Leave Balances (Per Employee Per Year)
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->integer('year'); // 2024, 2025
            $table->float('total_days')->default(0); // Allocated
            $table->float('used_days')->default(0);  // Consumed
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('cascade');
            
            // Prevent duplicate balance records for same type/year
            $table->unique(['employee_id', 'leave_type_id', 'year']);
        });

        // 3. Leave Requests
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('employee_id'); // Who requested
            $table->unsignedBigInteger('leave_type_id');
            
            $table->date('start_date');
            $table->date('end_date');
            $table->float('total_days'); // Calculated duration
            
            $table->text('reason')->nullable();
            
            // Status: pending, approved, rejected, cancelled
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            
            $table->unsignedBigInteger('approver_id')->nullable(); // User ID (Manager)
            $table->text('approver_comment')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_management_tables');
    }
};
