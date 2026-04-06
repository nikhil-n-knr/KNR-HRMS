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
        Schema::create('crm_deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('account_id')->nullable()->constrained('crm_accounts')->onDelete('cascade');
            $table->foreignId('contact_id')->nullable()->constrained('crm_contacts')->onDelete('set null');
            
            // Deal Information
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('value', 15, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            
            // Pipeline & Stage
            $table->enum('stage', [
                'lead', 
                'qualified', 
                'proposal', 
                'negotiation', 
                'closed_won', 
                'closed_lost'
            ])->default('lead');
            $table->integer('probability')->default(10); // 0-100%
            
            // Dates
            $table->date('expected_close_date')->nullable();
            $table->timestamp('closed_at')->nullable();
            
            // Status
            $table->enum('status', ['open', 'won', 'lost', 'abandoned'])->default('open');
            $table->text('loss_reason')->nullable(); // If closed_lost
            
            // Metadata
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['tenant_id', 'status', 'stage']);
            $table->index('account_id');
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_deals');
    }
};
