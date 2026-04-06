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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('unit')->default('Unit'); // Box, Kg, Each
            $table->decimal('current_stock', 10, 2)->default(0);
            $table->decimal('min_stock_level', 10, 2)->default(0);
            $table->boolean('is_recurring')->default(false);
            $table->integer('recurring_frequency_days')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->enum('type', ['Purchase', 'Consumption', 'Adjustment']);
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users');
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['Draft', 'Approved', 'Ordered', 'Received'])->default('Draft');
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->json('items')->nullable(); // Snapshot of what to buy
            $table->date('expected_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_items');
    }
};
