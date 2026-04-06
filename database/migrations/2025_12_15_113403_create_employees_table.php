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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // For public referencing if needed
            $table->unsignedBigInteger('tenant_id')->default(1); // Multi-tenancy support
            
            // Link to User Login (Nullable because profile might be created before login)
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            
            // Core Identity
            $table->string('employee_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable(); // Work email
            $table->string('phone')->nullable();
            
            // Organization
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('designation')->nullable();
            $table->foreignId('reporting_to')->nullable()->constrained('users')->nullOnDelete(); // Manager (User ID)
            
            // Status & Dates
            $table->date('joining_date')->nullable();
            $table->enum('status', ['active', 'probation', 'notice_period', 'terminated', 'resigned', 'on_leave'])->default('active');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'intern'])->default('full_time');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for faster searching
            $table->index(['first_name', 'last_name']);
            $table->index('joining_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
