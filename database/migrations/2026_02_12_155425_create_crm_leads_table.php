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
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            
            // Lead Information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('title')->nullable(); // Job title
            $table->string('website')->nullable();
            
            // Lead Qualification
            $table->enum('source', ['website', 'referral', 'social_media', 'cold_call', 'event', 'other'])->default('other');
            $table->enum('status', ['new', 'contacted', 'qualified', 'unqualified', 'converted'])->default('new');
            $table->integer('score')->default(0); // Lead scoring
            
            // Address
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            
            // Conversion tracking
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_to_contact_id')->nullable()->constrained('crm_contacts')->onDelete('set null');
            $table->foreignId('converted_to_account_id')->nullable()->constrained('crm_accounts')->onDelete('set null');
            
            // Metadata
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['tenant_id', 'status']);
            $table->index('email');
            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_leads');
    }
};
