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
        Schema::create('tax_regimes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., 'Old Regime', 'New Regime (2025)'
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('tax_slabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regime_id')->constrained('tax_regimes')->onDelete('cascade');
            $table->decimal('min_income', 15, 2);
            $table->decimal('max_income', 15, 2)->nullable(); // Nullable for "Above X amounts"
            $table->decimal('tax_rate_percentage', 5, 2); // e.g., 5.00
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_slabs');
        Schema::dropIfExists('tax_regimes');
    }
};
