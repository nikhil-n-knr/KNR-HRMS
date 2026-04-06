<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Product Categories (Nested - 5 levels)
        Schema::create('cms_product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->constrained('cms_sites')->onDelete('cascade');
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('name');
            $table->string('slug');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('cms_product_categories')->onDelete('set null');
            $table->unique(['site_id', 'slug']);
        });

        // 2. CMS Products (Syncs with crm_products)
        Schema::create('cms_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->constrained('cms_sites')->onDelete('cascade');
            $table->unsignedBigInteger('crm_product_id')->nullable()->index(); // FK to crm_products
            $table->foreignId('category_id')->nullable()->constrained('cms_product_categories')->onDelete('set null');

            $table->string('name');
            $table->string('slug')->index();
            $table->longText('description')->nullable();
            $table->string('short_description')->nullable();

            // Pricing
            $table->decimal('price', 12, 2);
            $table->decimal('mrp', 12, 2)->nullable();
            $table->decimal('discount_pct', 5, 2)->default(0);
            $table->string('tax_class')->default('GST_18'); // GST category

            // Identity
            $table->string('sku')->nullable()->index();
            $table->string('barcode')->nullable();

            // Physical
            $table->decimal('weight', 8, 2)->nullable();
            $table->json('dimensions')->nullable(); // {length, width, height, unit}

            // Inventory
            $table->integer('stock_qty')->default(0);
            $table->boolean('track_inventory')->default(true);
            $table->integer('low_stock_threshold')->default(5);
            $table->boolean('allow_backorder')->default(false);

            // Media
            $table->json('images')->nullable(); // [{url, alt, is_primary}]

            // Variants
            $table->json('variants')->nullable(); // [{name, options: [{sku, price, stock, image}]}]
            $table->json('variant_attributes')->nullable(); // [{name:'Size', values:['S','M','L']}]

            // SEO
            $table->json('seo')->nullable(); // {title, meta_description, og_image, schema}

            // CRM connections
            $table->json('related_products')->nullable(); // [id, id, ...]
            $table->boolean('featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();
            $table->unique(['site_id', 'slug']);
        });

        // 3. Orders
        Schema::create('cms_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->constrained('cms_sites')->onDelete('cascade');
            $table->string('order_number')->unique();

            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone')->nullable();

            $table->json('items'); // snapshot of products at purchase time
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('shipping', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->string('coupon_code')->nullable();
            $table->decimal('coupon_discount', 12, 2)->default(0);

            $table->enum('status', [
                'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'
            ])->default('pending');

            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable(); // razorpay, cod, wallet
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('razorpay_signature')->nullable();

            $table->json('shipping_address')->nullable();
            $table->json('billing_address')->nullable();

            $table->text('notes')->nullable();
            $table->unsignedBigInteger('crm_deal_id')->nullable(); // Link to crm_deals

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Coupons
        Schema::create('cms_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->constrained('cms_sites')->onDelete('cascade');
            $table->string('code')->index();
            $table->string('description')->nullable();
            $table->enum('type', ['percent', 'fixed', 'buy_x_get_y', 'bundle', 'free_shipping'])->default('percent');
            $table->decimal('value', 10, 2)->default(0);
            $table->decimal('min_order_value', 10, 2)->default(0);
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->integer('usage_limit')->nullable(); // null = unlimited
            $table->integer('used_count')->default(0);
            $table->integer('per_user_limit')->default(1);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->json('conditions')->nullable(); // {first_time_only, product_ids, category_ids}
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['site_id', 'code']);
        });

        // 5. Cart Sessions
        Schema::create('cms_cart_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->foreignId('site_id')->constrained('cms_sites')->onDelete('cascade');
            $table->json('items')->nullable(); // [{product_id, variant_key, qty, price, name, image}]
            $table->string('coupon_code')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamp('abandoned_at')->nullable();
            $table->timestamp('recovered_at')->nullable();
            $table->integer('recovery_email_count')->default(0);
            $table->timestamps();
        });

        // 6. Loyalty Points
        Schema::create('cms_loyalty_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->index();
            $table->foreignId('site_id')->constrained('cms_sites')->onDelete('cascade');
            $table->integer('points_balance')->default(0);
            $table->enum('tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            $table->timestamps();
            $table->unique(['site_id', 'user_id']);
        });

        Schema::create('cms_loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreignId('site_id')->constrained('cms_sites')->onDelete('cascade');
            $table->enum('type', ['earned', 'redeemed', 'expired', 'bonus']);
            $table->integer('points');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_loyalty_transactions');
        Schema::dropIfExists('cms_loyalty_points');
        Schema::dropIfExists('cms_cart_sessions');
        Schema::dropIfExists('cms_coupons');
        Schema::dropIfExists('cms_orders');
        Schema::dropIfExists('cms_products');
        Schema::dropIfExists('cms_product_categories');
    }
};
