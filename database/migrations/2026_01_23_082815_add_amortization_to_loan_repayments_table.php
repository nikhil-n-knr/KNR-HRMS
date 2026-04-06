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
        Schema::table('loan_repayments', function (Blueprint $table) {
            // Amortization Splits
            $table->decimal('principal_component', 15, 2)->default(0);
            $table->decimal('interest_component', 15, 2)->default(0);
            $table->decimal('outstanding_balance', 15, 2)->default(0); // Balance AFTER this payment
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_repayments', function (Blueprint $table) {
            //
        });
    }
};
