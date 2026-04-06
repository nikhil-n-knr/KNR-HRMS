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
        // 1. Tax Sections (Master Data - e.g. 80C, HRA, Medical)
        Schema::create('tax_sections', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Life Insurance Premium", "HRA"
            $table->string('section_code'); // "80C", "10(13A)"
            $table->decimal('max_deduction', 12, 2)->nullable(); // 1,50,000 for 80C
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Employee Regime Selection
        Schema::create('employee_tax_regimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('fiscal_year'); // "2025-2026"
            $table->enum('regime', ['Old', 'New'])->default('New'); 
            $table->timestamp('locked_at')->nullable(); // If locked, cannot change
            $table->timestamps();
            $table->unique(['employee_id', 'fiscal_year']);
        });

        // 3. Investment Declarations
        Schema::create('tax_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_section_id')->constrained('tax_sections');
            $table->string('fiscal_year'); // "2025-2026"
            
            $table->decimal('declared_amount', 12, 2);
            $table->decimal('verified_amount', 12, 2)->nullable();
            
            $table->enum('status', ['Draft', 'Submitted', 'Verified', 'Rejected'])->default('Draft');
            $table->text('remarks')->nullable();
            
            $table->timestamps();
        });

        // 4. Proofs
        Schema::create('investment_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_declaration_id')->constrained('tax_declarations')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_proofs');
        Schema::dropIfExists('tax_declarations');
        Schema::dropIfExists('employee_tax_regimes');
        Schema::dropIfExists('tax_sections');
    }
};
