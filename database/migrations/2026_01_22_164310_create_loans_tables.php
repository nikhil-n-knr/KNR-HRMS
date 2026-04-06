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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('loan_type')->default('Salary Advance'); // Salary Advance, Personal Loan
            $table->decimal('principal_amount', 10, 2);
            $table->decimal('interest_rate', 5, 2)->default(0); // 0 for Advance
            $table->integer('tenure_months');
            $table->decimal('monthly_installment', 10, 2);
            $table->string('reason')->nullable();
            
            $table->string('status')->default('Pending'); // Pending, Approved, Rejected, Active, Closed
            $table->string('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
            
            $table->date('disbursement_date')->nullable();
            $table->timestamps();
        });

        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->date('scheduled_date'); // The month this EMI is due
            $table->decimal('amount', 10, 2);
            
            $table->string('status')->default('Pending'); // Pending, Paid, Skipped
            $table->foreignId('payroll_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_repayments');
        Schema::dropIfExists('loans');
    }
};
