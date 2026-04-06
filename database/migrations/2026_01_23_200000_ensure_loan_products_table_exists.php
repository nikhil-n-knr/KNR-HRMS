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
        if (!Schema::hasTable('loan_products')) {
            Schema::create('loan_products', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique(); // e.g., 'Personal Loan', 'Emergency'
                $table->text('description')->nullable();
                
                // Configuration
                $table->string('interest_type')->default('Flat'); // Flat, Reducing
                $table->decimal('max_amount_limit', 15, 2)->default(100000); 
                $table->integer('max_tenure_months')->default(24);
                $table->decimal('eligibility_multiplier', 5, 2)->default(1.00); // 1.0x CTC
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop it automatically to avoid accidental data loss during rollback of this specific fix
    }
};
