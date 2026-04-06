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
        // Quote Templates Table (must be created first for foreign key)
        Schema::create('quote_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('header_html')->nullable(); // Header/Logo
            $table->text('footer_html')->nullable(); // Footer/Signature
            $table->text('terms_template')->nullable(); // Default terms
            $table->json('styling')->nullable(); // Colors, fonts, etc.
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('tenant_id');
        });
        
        // Quotes Table
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            
            // Relationships
            $table->foreignId('deal_id')->nullable()->constrained('crm_deals')->onDelete('set null');
            $table->foreignId('account_id')->nullable()->constrained('crm_accounts')->onDelete('set null');
            $table->foreignId('contact_id')->nullable()->constrained('crm_contacts')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // Quote Details
            $table->string('quote_number')->unique(); // Auto-generated: Q-2026-001
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'sent', 'viewed', 'accepted', 'declined', 'expired'])->default('draft');
            
            // Financial
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('tax_rate', 5, 2)->default(0); // Percentage
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            
            // Dates
            $table->date('valid_until');
            $table->date('sent_at')->nullable();
            $table->date('viewed_at')->nullable();
            $table->date('accepted_at')->nullable();
            
            // Terms & Conditions
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('template_id')->nullable()->constrained('quote_templates')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['tenant_id', 'status']);
            $table->index(['deal_id']);
            $table->index(['quote_number']);
        });
        
        // Quote Items Table
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quotes')->onDelete('cascade');
            
            // Item Details
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('total', 15, 2);
            
            // Ordering
            $table->integer('order')->default(0);
            
            $table->timestamps();
            
            $table->index('quote_id');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('quote_templates');
    }
};
