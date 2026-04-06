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
            $table->boolean('is_serialized')->default(true); // If false, assets in this category default to quantity-based
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->integer('quantity')->default(1);
            $table->boolean('is_serialized')->default(true);
        });

        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->integer('quantity')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            //
        });
    }
};
