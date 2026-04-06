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
        // 1. Email Integration Hub
        if (!Schema::hasTable('crm_email_accounts')) {
            Schema::create('crm_email_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('email_address');
                $table->enum('provider', ['imap', 'gmail', 'outlook'])->default('imap');
                $table->text('credentials'); // Encrypted JSON
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('crm_email_threads')) {
            Schema::create('crm_email_threads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('subject')->nullable();
                $table->morphs('trackable'); // Lead, Contact, Deal
                $table->string('external_thread_id')->nullable()->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('crm_email_messages')) {
            Schema::create('crm_email_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('thread_id')->constrained('crm_email_threads')->onDelete('cascade');
                $table->string('message_id')->unique();
                $table->string('from_email');
                $table->string('from_name')->nullable();
                $table->json('to_emails');
                $table->json('cc_emails')->nullable();
                $table->json('bcc_emails')->nullable();
                $table->longText('body_html')->nullable();
                $table->longText('body_text')->nullable();
                $table->enum('direction', ['inbound', 'outbound'])->default('inbound');
                $table->enum('status', ['draft', 'sent', 'bounced', 'delivered'])->default('delivered');
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('crm_email_stats')) {
            Schema::create('crm_email_stats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('message_id')->constrained('crm_email_messages')->onDelete('cascade');
                $table->enum('event_type', ['open', 'click', 'bounce', 'reply']);
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->string('payload')->nullable(); // For url clicks
                $table->timestamp('occurred_at');
                $table->timestamps();
            });
        }

        // 2. Meeting & Calendar Engine
        if (!Schema::hasTable('crm_meeting_types')) {
            Schema::create('crm_meeting_types', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('name');
                $table->integer('duration_minutes')->default(30);
                $table->integer('buffer_minutes')->default(0);
                $table->json('reminder_ladder')->nullable(); // e.g. [7d, 1d, 1h, 10m]
                $table->foreignId('default_template_id')->nullable(); // Link to email templates
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('crm_calendar_syncs')) {
            Schema::create('crm_calendar_syncs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->enum('provider', ['google', 'outlook']);
                $table->text('access_token');
                $table->text('refresh_token');
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('crm_meetings')) {
            Schema::table('crm_meetings', function (Blueprint $table) {
                if (!Schema::hasColumn('crm_meetings', 'meeting_type_id')) {
                    $table->foreignId('meeting_type_id')->nullable()->constrained('crm_meeting_types')->onDelete('set null');
                }
                if (!Schema::hasColumn('crm_meetings', 'trackable_type')) {
                    $table->nullableMorphs('trackable'); // Lead, Contact, Deal
                }
                if (!Schema::hasColumn('crm_meetings', 'assigned_to')) {
                    $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('cascade');
                }
                if (!Schema::hasColumn('crm_meetings', 'conferencing_link')) {
                    $table->string('conferencing_link')->nullable();
                }
                if (!Schema::hasColumn('crm_meetings', 'external_event_id')) {
                    $table->string('external_event_id')->nullable()->index();
                }
                if (!Schema::hasColumn('crm_meetings', 'status')) {
                    $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled');
                }
                if (!Schema::hasColumn('crm_meetings', 'notes')) {
                    $table->longText('notes')->nullable();
                }
                if (!Schema::hasColumn('crm_meetings', 'ai_summary')) {
                    $table->json('ai_summary')->nullable();
                }
            });
        } else {
            Schema::create('crm_meetings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('meeting_type_id')->nullable()->constrained('crm_meeting_types')->onDelete('set null');
                $table->morphs('trackable'); // Lead, Contact, Deal
                $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->timestamp('start_time');
                $table->timestamp('end_time');
                $table->string('location')->nullable();
                $table->string('conferencing_link')->nullable();
                $table->string('external_event_id')->nullable()->index();
                $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled');
                $table->longText('notes')->nullable();
                $table->json('ai_summary')->nullable();
                $table->timestamps();
            });
        }

        // 3. Automation & Campaigns
        if (!Schema::hasTable('crm_automation_rules')) {
            Schema::create('crm_automation_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('name');
                $table->string('trigger_event'); // e.g. 'meeting_scheduled', 'email_sent'
                $table->json('conditions')->nullable();
                $table->json('actions');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('crm_campaign_journeys')) {
            Schema::create('crm_campaign_journeys', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('name');
                $table->enum('type', ['broadcast', 'drip'])->default('broadcast');
                $table->json('enrollment_criteria')->nullable();
                $table->enum('status', ['draft', 'active', 'paused', 'completed'])->default('draft');
                $table->timestamps();
            });
        }

        // 4. Handover & Audit
        if (!Schema::hasTable('crm_handover_logs')) {
            Schema::create('crm_handover_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->foreignId('from_user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('to_user_id')->constrained('users')->onDelete('cascade');
                $table->string('entity_type'); // 'Account', 'Lead', 'Deal'
                $table->json('entity_ids');
                $table->json('transfer_options');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_handover_logs');
        Schema::dropIfExists('crm_campaign_journeys');
        Schema::dropIfExists('crm_automation_rules');
        Schema::dropIfExists('crm_meetings');
        Schema::dropIfExists('crm_calendar_syncs');
        Schema::dropIfExists('crm_meeting_types');
        Schema::dropIfExists('crm_email_stats');
        Schema::dropIfExists('crm_email_messages');
        Schema::dropIfExists('crm_email_threads');
        Schema::dropIfExists('crm_email_accounts');
    }
};
