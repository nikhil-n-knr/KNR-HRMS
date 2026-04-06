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
        // 1. Personal Details (Bio, Address, Identity)
        Schema::create('employee_personal_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            
            // Bio
            $table->date('dob')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('nationality')->nullable();
            $table->string('blood_group')->nullable(); // e.g. O+, A-
            $table->string('religion')->nullable(); 
            
            // Address (Current)
            $table->text('current_address')->nullable();
            $table->string('current_city')->nullable();
            $table->string('current_state')->nullable();
            $table->string('current_zip')->nullable();
            $table->string('current_country')->default('India');
            
            // Address (Permanent) - Can be same as current
            $table->boolean('is_permanent_same')->default(false);
            $table->text('permanent_address')->nullable();
            $table->string('permanent_city')->nullable();
            $table->string('permanent_state')->nullable();
            $table->string('permanent_zip')->nullable();
            $table->string('permanent_country')->default('India');

            // Passport / ID
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('national_id_number')->nullable(); // Aadhar/SSN

            $table->timestamps();
        });

        // 2. Health Records (Comprehensive A-Z)
        Schema::create('employee_health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            
            $table->string('blood_group')->nullable(); // Redundant but useful for quick access
            $table->float('height_cm')->nullable();
            $table->float('weight_kg')->nullable();
            $table->text('allergies')->nullable(); // List of allergies
            $table->text('chronic_conditions')->nullable(); // Diabetes, BP, etc.
            
            // Checkup Tracking
            $table->date('last_checkup_date')->nullable();
            $table->date('next_checkup_due')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('hospital_name')->nullable();
            
            // Insurance
            $table->string('insurance_provider')->nullable();
            $table->string('policy_number')->nullable();
            $table->date('policy_expiry')->nullable();
            $table->mediumText('coverage_details')->nullable();
            
            // Emergency
            $table->boolean('disability_status')->default(false);
            $table->string('disability_details')->nullable();

            $table->timestamps();
        });

        // 3. Family / Dependents
        Schema::create('employee_families', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            
            $table->string('name');
            $table->enum('relationship', ['spouse', 'child', 'father', 'mother', 'sibling', 'other']);
            $table->date('dob')->nullable();
            $table->string('occupation')->nullable();
            $table->string('phone')->nullable();
            
            $table->boolean('is_dependent')->default(false); // Health insurance coverage often depends on this
            $table->boolean('is_emergency_contact')->default(false);
            
            $table->timestamps();
        });

        // 4. Bank Details (Payroll)
        Schema::create('employee_bank_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            
            $table->string('bank_name');
            $table->string('branch_name')->nullable();
            $table->string('account_holder_name');
            $table->string('account_number');
            $table->string('ifsc_code')->nullable(); // International/Swift code support if needed
            $table->string('bic_code')->nullable();
            
            $table->enum('account_type', ['savings', 'current', 'salary'])->default('savings');
            $table->boolean('is_primary')->default(true);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_bank_details');
        Schema::dropIfExists('employee_families');
        Schema::dropIfExists('employee_health_records');
        Schema::dropIfExists('employee_personal_details');
    }
};
