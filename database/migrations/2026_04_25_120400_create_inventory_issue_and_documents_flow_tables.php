<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_issue_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->enum('issue_type', ['Office_Consumption', 'Employee_Issue', 'Client_Delivery', 'Project_Use']);
            $table->morphs('issued_to');
            $table->decimal('quantity', 10, 2);
            $table->date('issue_date');
            $table->boolean('returnable')->default(false);
            $table->decimal('returned_qty', 10, 2)->default(0);
            $table->enum('status', ['Open', 'Partial_Returned', 'Closed'])->default('Open');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['item_id', 'issue_date']);
            $table->index(['status', 'returnable']);
        });

        Schema::create('recurring_consumption_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->morphs('scope');
            $table->enum('frequency', ['Weekly', 'Monthly']);
            $table->unsignedTinyInteger('day_of_week')->nullable();
            $table->unsignedTinyInteger('day_of_month')->nullable();
            $table->decimal('expected_qty', 10, 2);
            $table->boolean('auto_create_request')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['item_id', 'is_active']);
            $table->index(['frequency', 'day_of_week', 'day_of_month'], 'rc_rules_freq_day_idx');
        });

        Schema::create('client_item_supplies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->decimal('supplied_qty', 10, 2);
            $table->decimal('unit_rate', 10, 2)->nullable();
            $table->date('supplied_on');
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'supplied_on']);
            $table->index(['item_id', 'supplied_on']);
        });

        Schema::create('physical_record_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_id')->constrained('physical_records')->cascadeOnDelete();
            $table->enum('movement_type', ['CheckIn', 'CheckOut', 'Transfer', 'Return', 'Missing', 'Recover']);
            $table->foreignId('from_node_id')->nullable()->constrained('location_nodes')->nullOnDelete();
            $table->foreignId('to_node_id')->nullable()->constrained('location_nodes')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('movement_at')->useCurrent();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['record_id', 'movement_at']);
            $table->index(['movement_type']);
        });

        Schema::table('physical_records', function (Blueprint $table) {
            $table->string('category')->nullable()->after('document_type');
            $table->string('subcategory')->nullable()->after('category');
            $table->string('owner_type')->nullable()->after('subcategory');
            $table->unsignedBigInteger('owner_id')->nullable()->after('owner_type');
            $table->unsignedBigInteger('retention_policy_id')->nullable()->after('owner_id');
            $table->string('confidentiality_level')->nullable()->after('retention_policy_id');

            $table->index(['owner_type', 'owner_id']);
            $table->index(['category', 'subcategory']);
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->enum('vendor_type', ['Asset_Supplier', 'Repair', 'Stationery', 'Multi'])->default('Multi')->after('name');
            $table->json('supported_categories')->nullable()->after('phone');
            $table->json('service_locations')->nullable()->after('supported_categories');
            $table->decimal('rating', 3, 2)->nullable()->after('sla_response_hours');
            $table->unsignedInteger('lead_time_days')->nullable()->after('rating');
        });

        Schema::create('vendor_item_map', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->morphs('entity');
            $table->decimal('contract_rate', 10, 2)->nullable();
            $table->decimal('min_order_qty', 10, 2)->nullable();
            $table->unsignedInteger('sla_days')->nullable();
            $table->timestamps();

            $table->index(['vendor_id', 'entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_item_map');

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn([
                'vendor_type',
                'supported_categories',
                'service_locations',
                'rating',
                'lead_time_days',
            ]);
        });

        Schema::table('physical_records', function (Blueprint $table) {
            $table->dropIndex(['owner_type', 'owner_id']);
            $table->dropIndex(['category', 'subcategory']);
            $table->dropColumn([
                'category',
                'subcategory',
                'owner_type',
                'owner_id',
                'retention_policy_id',
                'confidentiality_level',
            ]);
        });

        Schema::dropIfExists('physical_record_movements');
        Schema::dropIfExists('client_item_supplies');
        Schema::dropIfExists('recurring_consumption_rules');
        Schema::dropIfExists('inventory_issue_lines');
    }
};
