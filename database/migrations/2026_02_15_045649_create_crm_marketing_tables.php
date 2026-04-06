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
        if (!Schema::hasTable('crm_campaign_recipients')) {
            Schema::create('crm_campaign_recipients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('crm_marketing_campaigns')->onDelete('cascade');
                $table->foreignId('contact_id')->nullable()->constrained('crm_contacts');
                $table->string('recipient_email');
                $table->string('recipient_name')->nullable();
                $table->string('status')->default('pending'); // pending, sent, failed, bounced
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('opened_at')->nullable();
                $table->timestamp('clicked_at')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('crm_campaign_events')) {
            Schema::create('crm_campaign_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('crm_marketing_campaigns')->onDelete('cascade');
                $table->foreignId('contact_id')->nullable()->constrained('crm_contacts');
                $table->string('event_type'); // open, click, bounce, unsubscribe
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_campaign_events');
        Schema::dropIfExists('crm_campaign_recipients');
    }
};
