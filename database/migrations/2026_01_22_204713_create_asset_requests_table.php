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
        Schema::create('asset_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('category_id')->constrained('asset_categories'); // Requested Type
            $table->string('priority')->default('Normal'); // Low, Normal, High
            $table->text('reason')->nullable();
            
            // Workflow Status
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Fulfilled'])->default('Pending');
            
            // Approval Audit
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Fulfillment Link
            $table->foreignId('fulfilled_asset_id')->nullable()->constrained('assets');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_requests');
    }
};
