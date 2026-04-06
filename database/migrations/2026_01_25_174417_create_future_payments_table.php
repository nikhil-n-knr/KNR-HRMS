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
        Schema::create('future_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // Retention Bonus, Sign-on Bonus, etc.
            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->string('status')->default('Scheduled'); // Scheduled, Paid, Cancelled
            $table->text('conditions')->nullable(); // JSON or text rules
            $table->string('payment_frequency')->default('One-time'); // One-time, Quarterly
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('future_payments');
    }
};
