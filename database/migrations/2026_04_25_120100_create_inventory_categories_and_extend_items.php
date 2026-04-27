<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('inventory_categories')->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('item_type', ['Consumable', 'Non_Consumable', 'Serviceable'])->default('Consumable');
            $table->timestamps();

            $table->index(['tenant_id', 'parent_id']);
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('tenant_id')->constrained('inventory_categories')->nullOnDelete();
            $table->foreignId('subcategory_id')->nullable()->after('category_id')->constrained('inventory_categories')->nullOnDelete();
            $table->string('sku')->nullable()->after('name');
            $table->string('barcode')->nullable()->after('sku');
            $table->enum('reorder_mode', ['Manual', 'MinMax', 'Scheduled'])->default('Manual')->after('min_stock_level');
            $table->enum('recurring_mode', ['None', 'Daily', 'Weekly', 'Monthly', 'CustomCron'])->default('None')->after('reorder_mode');
            $table->json('recurring_config')->nullable()->after('recurring_mode');
            $table->foreignId('default_vendor_id')->nullable()->after('recurring_config')->constrained('vendors')->nullOnDelete();
            $table->unsignedBigInteger('storage_node_id')->nullable()->after('default_vendor_id');

            $table->index(['category_id', 'subcategory_id']);
            $table->index('storage_node_id');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropIndex(['category_id', 'subcategory_id']);
            $table->dropIndex(['storage_node_id']);
            $table->dropConstrainedForeignId('default_vendor_id');
            $table->dropConstrainedForeignId('subcategory_id');
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn([
                'sku',
                'barcode',
                'reorder_mode',
                'recurring_mode',
                'recurring_config',
                'storage_node_id',
            ]);
        });

        Schema::dropIfExists('inventory_categories');
    }
};
