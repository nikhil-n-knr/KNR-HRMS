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
        if (!Schema::hasTable('investment_proofs')) {
            Schema::create('investment_proofs', function (Blueprint $table) {
                $table->id();
                
                // Link to Specific Declaration Row (e.g. 80C - LIC)
                $table->foreignId('tax_declaration_id')->constrained()->cascadeOnDelete();
                
                // Redundant for easier querying but safer to keep
                $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
                
                $table->string('file_path');
                $table->string('original_name')->nullable();
                
                $table->decimal('verified_amount', 12, 2)->nullable();
                $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
                $table->text('rejection_reason')->nullable();
                
                $table->foreignId('verified_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_proofs');
    }
};
