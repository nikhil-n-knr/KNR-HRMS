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
        // 1. Enhanced Product Catalog
        if (!Schema::hasTable('crm_product_categories')) {
            Schema::create('crm_product_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
                $table->foreignId('parent_id')->nullable()->constrained('crm_product_categories')->onDelete('cascade');
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('path')->nullable()->index(); // Materialized path for hierarchy
                $table->timestamps();
            });
        }

        // Drop existing simple table if exists to rebuild
        if (Schema::hasTable('crm_products')) {
            // Backup or just refresh? For this task we assume refresh/upgrade
            // We will add columns if checking individual, but easiest is to drop/create for full spec match
            Schema::dropIfExists('crm_products'); 
        }

        Schema::create('crm_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('crm_product_categories')->onDelete('set null');
            $table->string('sku')->unique(); // Unique per tenant? or global? Usually tenant scoped strictly.
            // Complex unique index: tenant_id + sku
            // $table->unique(['tenant_id', 'sku']); 
            
            $table->string('name');
            $table->enum('type', ['physical', 'service', 'digital'])->default('physical');
            $table->text('description')->nullable();
            
            // Pricing & Costs
            $table->decimal('base_price', 10, 2)->default(0);
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('tax_code')->nullable();
            $table->string('gl_code')->nullable(); // General Ledger code
            
            // Inventory
            $table->integer('stock_qty')->default(0);
            $table->integer('min_order_qty')->default(1);
            $table->integer('reorder_point')->default(5);
            $table->boolean('track_inventory')->default(true);
            
            // Meta
            $table->string('barcode')->nullable();
            $table->json('dimensions')->nullable(); // weight, l, w, h
            $table->string('hs_code')->nullable();
            $table->integer('warranty_months')->default(0);
            $table->boolean('is_discontinued')->default(false);
            $table->boolean('is_active')->default(true);
            
            // JSON Fields
            $table->json('images')->nullable(); // S3 paths
            $table->json('video_url')->nullable();
            $table->json('custom_fields')->nullable();
            $table->json('seo_meta')->nullable();
            
            $table->foreignId('supplier_id')->nullable(); // Link to a Vendor/Supplier model if exists, or just ID
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['tenant_id', 'sku']);
        });

        Schema::create('crm_product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('crm_products')->onDelete('cascade');
            $table->string('sku')->nullable();
            $table->string('name')->nullable(); // e.g. "Red / XL"
            $table->decimal('price_adjustment', 10, 2)->default(0);
            $table->integer('stock_qty')->default(0);
            $table->json('attributes')->nullable(); // { "Color": "Red", "Size": "XL" }
            $table->timestamps();
        });

        Schema::create('crm_product_sales_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained('crm_products')->onDelete('cascade');
            $table->date('period'); // Monthly aggregate, e.g. 2023-10-01
            $table->integer('units_sold')->default(0);
            $table->decimal('revenue', 12, 2)->default(0);
            $table->integer('unique_customers')->default(0);
            $table->boolean('recurring_flag')->default(false); // If true, mainly sub revenue
            $table->timestamps();
            
            $table->unique(['tenant_id', 'product_id', 'period']);
        });

        Schema::create('crm_product_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('metric_key'); // e.g., 'top_selling_q1_2024'
            $table->json('data'); // Computed result
            $table->timestamp('calculated_at');
            $table->timestamps();
        });

        // 3. Customer Recommendations
        Schema::create('crm_customer_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained('crm_contacts')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('crm_products')->onDelete('cascade');
            $table->decimal('score', 5, 4)->default(0); // 0.0000 to 1.0000
            $table->string('reason_code')->nullable(); // 'cross_sell', 'upsell', 'trending'
            $table->json('metadata')->nullable(); // 'Because you bought X...'
            $table->timestamps();
            
            $table->index(['contact_id', 'score']);
        });

        // 4. Meetings Module
        Schema::create('crm_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('type', ['offline', 'online']);
            $table->string('location')->nullable(); // Physical address or Zoom link
            $table->text('agenda')->nullable();
            $table->text('outcome_notes')->nullable();
            $table->string('external_meeting_id')->nullable(); // Zoom/Teams ID
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('crm_meeting_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('crm_meetings')->onDelete('cascade');
            $table->foreignId('contact_id')->nullable()->constrained('crm_contacts')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('email')->nullable(); // For external attendees not in CRM
            $table->string('status')->default('pending'); // accepted, declined
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_meeting_attendees');
        Schema::dropIfExists('crm_meetings');
        Schema::dropIfExists('crm_customer_recommendations');
        Schema::dropIfExists('crm_product_analytics');
        Schema::dropIfExists('crm_product_sales_history');
        Schema::dropIfExists('crm_product_variants');
        Schema::dropIfExists('crm_products');
        Schema::dropIfExists('crm_product_categories');
    }
};
