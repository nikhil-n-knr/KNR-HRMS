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
        // 1. Add TDS Field to Employee Salary
        Schema::table('employee_salaries', function (Blueprint $table) {
            $table->decimal('tds_monthly_deduction', 10, 2)->nullable()->after('breakdown');
        });

        // 2. Audit Logs Table
        Schema::create('tax_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type')->nullable(); // e.g., TaxDeclaration
            $table->unsignedBigInteger('entity_id')->nullable();
            
            $table->string('action'); // Approve, Reject, Update, Recalculate
            $table->foreignId('actor_id')->constrained('users')->onDelete('cascade'); // Who did it
            
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('remarks')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_audit_logs');
        
        Schema::table('employee_salaries', function (Blueprint $table) {
            $table->dropColumn('tds_monthly_deduction');
        });
    }
};
