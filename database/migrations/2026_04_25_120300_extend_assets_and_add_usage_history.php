<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('category_sub_id')->nullable()->after('category_id')->constrained('asset_categories')->nullOnDelete();
            $table->enum('procurement_source', ['Purchase', 'Lease', 'Rental', 'Client_Provided'])->nullable()->after('vendor_id');
            $table->enum('ownership_type', ['Company', 'Client', 'Employee_Deposit'])->default('Company')->after('procurement_source');
            $table->enum('lifecycle_state', ['Draft', 'Active', 'Assigned', 'In_Repair', 'In_Storage', 'Lost', 'Scrapped'])->default('Active')->after('status');

            $table->index(['category_id', 'category_sub_id']);
            $table->index(['lifecycle_state', 'status']);
        });

        Schema::create('asset_usage_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->enum('usage_type', ['AssignedToEmployee', 'PlacedInRoom', 'LoanedToClient', 'SentForRepair']);
            $table->nullableMorphs('from_entity');
            $table->nullableMorphs('to_entity');
            $table->timestamp('start_at')->useCurrent();
            $table->timestamp('end_at')->nullable();
            $table->string('reference_doc')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['asset_id', 'start_at']);
            $table->index(['usage_type', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_usage_history');

        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex(['category_id', 'category_sub_id']);
            $table->dropIndex(['lifecycle_state', 'status']);
            $table->dropConstrainedForeignId('category_sub_id');
            $table->dropColumn([
                'procurement_source',
                'ownership_type',
                'lifecycle_state',
            ]);
        });
    }
};
