<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ABM Command Center
        Schema::create('crm_abm_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained('crm_accounts')->onDelete('cascade');
            $table->integer('abm_score')->default(0);
            $table->string('status')->default('target'); // target, engaged, qualified, champion
            $table->json('journey_map')->nullable();
            $table->decimal('target_value', 15, 2)->nullable();
            $table->foreignId('assigned_rep_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // Revenue Tracker & Attribution
        Schema::create('crm_revenue_attribution', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('deal_id')->constrained('crm_deals')->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained('crm_marketing_campaigns')->onDelete('set null');
            $table->string('channel')->nullable(); // WhatsApp, Email, Voice, etc.
            $table->string('touchpoint_type')->nullable(); // first_touch, last_touch, mid_touch
            $table->decimal('attributed_amount', 15, 2)->default(0);
            $table->json('interaction_path')->nullable(); // Mind map data
            $table->timestamps();
        });

        // Flow Builder (Visual Automation)
        Schema::create('crm_marketing_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->json('nodes'); // Flow structure
            $table->json('edges'); // Connections
            $table->integer('total_executions')->default(0);
            $table->integer('conversions')->default(0);
            $table->decimal('revenue_impact', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Product History for Import Analyzer
        Schema::create('crm_product_import_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('file_name');
            $table->string('status')->default('processed');
            $table->integer('total_rows')->default(0);
            $table->json('ai_insights')->nullable(); // Churn, Top bundles, etc.
            $table->json('mapping_data')->nullable(); 
            $table->timestamps();
        });
        
        // Channel ROI stats (for Dashboard)
        Schema::create('crm_channel_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('channel'); // WhatsApp, Email, Voice, Social
            $table->decimal('spend', 15, 2)->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            $table->integer('leads_generated')->default(0);
            $table->decimal('roi_percentage', 10, 2)->default(0);
            $table->date('recorded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_channel_stats');
        Schema::dropIfExists('crm_product_import_history');
        Schema::dropIfExists('crm_marketing_flows');
        Schema::dropIfExists('crm_revenue_attribution');
        Schema::dropIfExists('crm_abm_accounts');
    }
};
