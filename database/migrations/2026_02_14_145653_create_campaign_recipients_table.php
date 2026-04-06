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
        Schema::create('crm_campaign_recipients', function (Blueprint $table) {
            $table->id();
            
            // Note: campaigns created with 145648, this is 145653. Constraint works.
            $table->foreignId('campaign_id')->constrained('crm_marketing_campaigns')->cascadeOnDelete();
            
            $table->foreignId('contact_id')->nullable()->constrained('crm_contacts')->nullOnDelete();
            // Can be lead too... but let's assume we copy lead data if needed or implement polymorph later.
            // For now, simpler: recipient_email, recipient_name
            
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            
            $table->string('status')->default('pending'); // pending, sent, delivered, failed, bounced
            
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            
            $table->text('error_message')->nullable(); // On failure
            
            $table->timestamps();
            
            $table->index(['campaign_id', 'status']); // For stats aggregation
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_campaign_recipients');
    }
};
