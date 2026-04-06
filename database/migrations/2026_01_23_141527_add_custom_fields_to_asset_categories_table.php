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
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->json('custom_attributes')->nullable(); // For Dynamic Form Builder
            $table->string('depreciation_method')->default('Straight Line');
            $table->integer('useful_life_years')->nullable();
            $table->decimal('scrap_value_percent', 5, 2)->default(0); // e.g. 5.00%
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            //
        });
    }
};
