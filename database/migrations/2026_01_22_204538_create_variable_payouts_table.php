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
        Schema::create('variable_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            
            $table->decimal('amount', 12, 2);
            $table->string('type'); // "Performance Bonus", "Sales Commission", "Referral Bonus"
            $table->text('remarks')->nullable();
            
            // Payout Schedule
            $table->integer('pay_month');
            $table->integer('pay_year');
            
            $table->enum('status', ['Pending', 'Approved', 'Paid', 'Rejected'])->default('Pending');
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls'); // Linked when paid
            
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variable_payouts');
    }
};
