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
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_electronic')->default(false);
            $table->integer('maintenance_interval_days')->nullable();
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('asset_categories');
            $table->string('name');
            $table->string('serial_number')->unique()->nullable();
            $table->enum('status', ['Available', 'Assigned', 'In_Service', 'Scrapped', 'Lost'])->default('Available');
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->decimal('current_value', 10, 2)->nullable();
            $table->foreignId('location_id')->nullable()->constrained('locations');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('returned_at')->nullable();
            $table->text('condition_on_return')->nullable();
            $table->enum('ack_status', ['Pending', 'Accepted', 'Rejected'])->default('Pending');
            $table->timestamps();
        });

        Schema::create('asset_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->enum('type', ['Repair', 'Upgrade', 'Routine_Service']);
            $table->string('vendor_name')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->json('parts_replaced')->nullable();
            $table->text('description')->nullable();
            $table->date('service_date');
            $table->date('next_service_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance_logs');
        Schema::dropIfExists('asset_assignments');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_categories');
    }
};
