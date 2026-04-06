<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Forms
        Schema::create('cms_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->constrained('cms_sites')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->index();
            $table->json('fields')->nullable(); // drag-drop field definitions
            $table->json('settings')->nullable(); // {submit_action, crm_lead_map, whatsapp_notify, redirect_url}
            $table->integer('submissions_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['site_id', 'slug']);
        });

        // 2. Form Submissions
        Schema::create('cms_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('cms_forms')->onDelete('cascade');
            $table->foreignId('site_id')->constrained('cms_sites')->onDelete('cascade');
            $table->json('data'); // submitted field values
            $table->unsignedBigInteger('crm_lead_id')->nullable(); // If converted to lead
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->timestamps();
        });

        // 3. Page Analytics (view tracking)
        Schema::create('cms_page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('cms_sites')->onDelete('cascade');
            $table->unsignedBigInteger('page_id')->nullable()->index();
            $table->string('session_id', 64)->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('url')->nullable();
            $table->string('referrer')->nullable();
            $table->enum('device_type', ['desktop', 'tablet', 'mobile'])->default('desktop');
            $table->string('country', 2)->nullable();
            $table->string('city')->nullable();
            $table->integer('time_on_page')->nullable(); // seconds
            $table->timestamps(); // created_at = view time
        });

        // 4. A/B Tests
        Schema::create('cms_ab_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->constrained('cms_sites')->onDelete('cascade');
            $table->string('name');
            $table->unsignedBigInteger('page_id')->nullable()->index();
            $table->json('variants'); // [{name, content_json, traffic_pct}]
            $table->string('goal_metric')->default('conversion'); // conversion/time_on_page/clicks
            $table->enum('status', ['draft', 'running', 'paused', 'completed'])->default('draft');
            $table->string('winner')->nullable(); // variant name that won
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        // 5. PWA Config (per site)
        Schema::create('cms_pwa_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('cms_sites')->onDelete('cascade')->unique();
            $table->string('app_name');
            $table->string('short_name')->nullable();
            $table->text('description')->nullable();
            $table->string('theme_color', 7)->default('#10B981');
            $table->string('background_color', 7)->default('#ffffff');
            $table->string('display')->default('standalone');
            $table->json('icons')->nullable(); // [{src, sizes, type}]
            $table->boolean('offline_enabled')->default(true);
            $table->boolean('push_notifications_enabled')->default(false);
            $table->string('vapid_public_key')->nullable();
            $table->string('vapid_private_key')->nullable();
            $table->timestamps();
        });

        // 6. SEO Audit Snapshots
        Schema::create('cms_seo_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('cms_sites')->onDelete('cascade');
            $table->unsignedBigInteger('page_id')->nullable()->index();
            $table->integer('score')->default(0); // 0-100
            $table->json('issues')->nullable(); // list of issues with severity
            $table->json('suggestions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_seo_audits');
        Schema::dropIfExists('cms_pwa_configs');
        Schema::dropIfExists('cms_ab_tests');
        Schema::dropIfExists('cms_page_views');
        Schema::dropIfExists('cms_form_submissions');
        Schema::dropIfExists('cms_forms');
    }
};
