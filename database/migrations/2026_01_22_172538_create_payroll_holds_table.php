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
        Schema::create('payroll_holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('reason');
            $table->enum('type', ['Full', 'Partial'])->default('Full'); // Full defaults to 0 net pay
            $table->decimal('amount', 10, 2)->nullable(); // If Partial
            $table->date('hold_until')->nullable(); 
            $table->enum('status', ['Active', 'Released'])->default('Active');
            $table->foreignId('released_in_payroll_id')->nullable()->constrained('payrolls'); // Audit trail
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_holds');
    }
};
