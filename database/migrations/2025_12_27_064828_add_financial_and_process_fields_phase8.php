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
        // 1. Project Financials
        Schema::table('projects', function (Blueprint $table) {
            $table->string('billing_type')->default('fixed'); // fixed, hourly, retainer
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
        });

        // 2. Invoices (The "Receipt")
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('project_id')->constrained();
            $table->date('due_date');
            $table->decimal('total', 10, 2);
            $table->string('status')->default('draft'); // draft, sent, paid, cancelled
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });

        // 3. Task Financials & Safety Nets
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->boolean('is_billable')->default(true);
            $table->timestamp('billed_at')->nullable(); // Locks task from editing if set
            $table->foreignId('invoice_id')->nullable()->constrained('invoices');
            
            // Safety Net: Optimistic Locking
            $table->integer('version')->default(1);
        });

        // 4. Invoice Line Items (Snapshot)
        Schema::create('invoice_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('project_tasks')->nullOnDelete(); 
            
            // Snapshot Data (Avoids history corruption if Task name changes)
            $table->string('description'); 
            $table->decimal('quantity', 8, 2); // Hours
            $table->decimal('rate', 10, 2); // Hourly Rate at time of invoice
            $table->decimal('amount', 10, 2); // Total
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_line_items');
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn(['is_billable', 'billed_at', 'invoice_id', 'version']);
        });
        Schema::dropIfExists('invoices');
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['billing_type', 'hourly_rate', 'currency']);
        });
    }
};
