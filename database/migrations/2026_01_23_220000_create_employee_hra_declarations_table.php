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
        Schema::create('employee_hra_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('fiscal_year'); // "2025-2026"
            
            $table->decimal('rent_monthly', 12, 2);
            $table->string('landlord_name');
            $table->string('landlord_pan')->nullable();
            $table->text('rented_address');
            $table->boolean('is_metro_city')->default(false);
            
            // Status for administrative oversight
            $table->enum('status', ['Draft', 'Submitted', 'Verified', 'Rejected'])->default('Draft');
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            
            // One declaration per fiscal year per employee
            $table->unique(['employee_id', 'fiscal_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_hra_declarations');
    }
};
