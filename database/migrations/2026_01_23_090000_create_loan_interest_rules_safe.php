<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('loan_interest_rules')) {
            Schema::create('loan_interest_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('loan_product_id')->constrained()->cascadeOnDelete();
                
                // Criteria
                $table->decimal('min_amount', 15, 2)->default(0);
                $table->decimal('max_amount', 15, 2)->nullable();
                $table->integer('min_tenure_months')->default(0);
                $table->integer('max_tenure_months')->nullable();
                
                // Result
                $table->decimal('interest_rate', 5, 2); 
                
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_interest_rules');
    }
};
