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
        // 1. Dynamic Attribute Definitions (Slots)
        Schema::create('asset_attribute_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_category_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "Screen Size", "RAM", "Fabric Type"
            $table->string('field_type')->default('text'); // text, number, boolean, date, select
            $table->json('options')->nullable(); // For 'select' type: ["16GB", "32GB"]
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });

        // 2. Attribute Values (The Actual Data)
        Schema::create('asset_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_definition_id')->constrained('asset_attribute_definitions')->cascadeOnDelete();
            $table->text('value')->nullable(); // Stored as string, casted in model
            $table->timestamps();
        });

        // 3. Business Rules (Logic Injection)
        Schema::create('business_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "High Value Approval"
            $table->string('module')->default('Asset'); // Asset, Inventory, Document
            $table->string('trigger_event'); // Create, Update, Delete
            $table->json('conditions'); // e.g. { "field": "cost", "operator": ">", "value": 50000 }
            $table->json('actions'); // e.g. { "type": "notification", "target": "VP" }
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_rules');
        Schema::dropIfExists('asset_attribute_values');
        Schema::dropIfExists('asset_attribute_definitions');
    }
};
