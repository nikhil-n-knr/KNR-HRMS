<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_products', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_products', 'brand')) {
                $table->string('brand', 100)->nullable()->after('category_id');
            }
            if (!Schema::hasColumn('cms_products', 'gender')) {
                $table->enum('gender', ['men', 'women', 'unisex', 'kids'])->nullable()->after('brand');
            }
            if (!Schema::hasColumn('cms_products', 'size_chart')) {
                $table->json('size_chart')->nullable()->after('gender'); // [{size:'S',chest:36,waist:30}]
            }
            if (!Schema::hasColumn('cms_products', 'tags')) {
                $table->json('tags')->nullable()->after('size_chart'); // ['casual','summer','linen']
            }
            if (!Schema::hasColumn('cms_products', 'sales_count')) {
                $table->unsignedInteger('sales_count')->default(0)->after('tags');
            }
            if (!Schema::hasColumn('cms_products', 'wishlist_count')) {
                $table->unsignedInteger('wishlist_count')->default(0)->after('sales_count');
            }
            if (!Schema::hasColumn('cms_products', 'colour_swatches')) {
                $table->json('colour_swatches')->nullable()->after('wishlist_count'); // [{name:'Red',hex:'#ef4444',stock:20}]
            }
            if (!Schema::hasColumn('cms_products', 'sub_category_id')) {
                $table->unsignedBigInteger('sub_category_id')->nullable()->index()->after('category_id');
            }
        });

        // Wishlist table
        if (!Schema::hasTable('cms_wishlists')) {
            Schema::create('cms_wishlists', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->foreignId('product_id')->constrained('cms_products')->onDelete('cascade');
                $table->foreignId('site_id')->constrained('cms_sites')->onDelete('cascade');
                $table->timestamps();
                $table->unique(['user_id', 'product_id']);
            });
        }

        // Product Reviews
        if (!Schema::hasTable('cms_product_reviews')) {
            Schema::create('cms_product_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('cms_products')->onDelete('cascade');
                $table->foreignId('site_id')->constrained('cms_sites')->onDelete('cascade');
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('reviewer_name');
                $table->string('reviewer_email')->nullable();
                $table->tinyInteger('rating'); // 1-5
                $table->string('title')->nullable();
                $table->text('body')->nullable();
                $table->json('images')->nullable();
                $table->boolean('verified_purchase')->default(false);
                $table->boolean('is_approved')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('cms_products', function (Blueprint $table) {
            $cols = ['brand','gender','size_chart','tags','sales_count','wishlist_count','colour_swatches','sub_category_id'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('cms_products', $col)) $table->dropColumn($col);
            }
        });
        Schema::dropIfExists('cms_product_reviews');
        Schema::dropIfExists('cms_wishlists');
    }
};
