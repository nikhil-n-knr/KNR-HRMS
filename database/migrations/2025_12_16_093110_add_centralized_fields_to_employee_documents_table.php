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
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->string('category')->default('General')->after('title')->index(); // Official, Financial, etc.
            $table->string('document_type')->nullable()->after('category'); // Payslip, Offer Letter, etc.
            
            // System Integration Fields
            $table->boolean('is_system_generated')->default(false)->after('file_size');
            $table->string('source_module')->nullable()->after('is_system_generated'); // Payroll, Recruitment
            $table->string('source_reference')->nullable()->after('source_module'); // Source ID
            
            $table->json('metadata')->nullable()->after('source_reference'); // Flexible context
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->dropColumn([
                'category', 
                'document_type', 
                'is_system_generated', 
                'source_module', 
                'source_reference', 
                'metadata'
            ]);
        });
    }
};
