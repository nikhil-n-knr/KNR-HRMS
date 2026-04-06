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
        Schema::create('crm_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            
            // Company Information
            $table->string('name');
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->enum('size', ['1-10', '11-50', '51-200', '201-500', '501-1000', '1000+'])->nullable();
            $table->decimal('annual_revenue', 15, 2)->nullable();
            
            // Contact Information
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('fax')->nullable();
            
            // Address
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            
            // Metadata
            $table->json('tags')->nullable();
            $table->enum('status', ['active', 'inactive', 'prospect', 'customer'])->default('prospect');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['tenant_id', 'status']);
            $table->index('industry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_accounts');
    }
};
