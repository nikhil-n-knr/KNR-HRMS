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
        Schema::table('quote_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->after('quote_id');
            // Assuming crm_products is the table name for Product model
            // Not adding foreign key constraint immediately to avoid potential issues if products are soft deleted or table name varies, 
            // but ideally: $table->foreign('product_id')->references('id')->on('crm_products')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropColumn('product_id');
        });
    }
};
