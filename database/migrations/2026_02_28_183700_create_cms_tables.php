<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->json('colors')->nullable();
            $table->json('typography')->nullable();
            $table->string('css_framework')->default('tailwind_v4');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cms_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('domain')->unique()->nullable();
            $table->foreignId('theme_id')->nullable()->constrained('cms_themes')->onDelete('set null');
            $table->json('global_settings')->nullable();
            $table->boolean('is_live')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->constrained('cms_sites')->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
            $table->json('seo_meta')->nullable();
            $table->json('layout_data')->nullable(); // The actual canvas structure
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->integer('priority')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['site_id', 'slug']);
        });

        Schema::create('cms_page_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('page_id')->constrained('cms_pages')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('layout_data'); // The snapshot
            $table->string('commit_message')->nullable();
            $table->timestamps();
        });

        Schema::create('cms_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->json('ai_metadata')->nullable(); // Auto generated alt tags, etc
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_media');
        Schema::dropIfExists('cms_page_versions');
        Schema::dropIfExists('cms_pages');
        Schema::dropIfExists('cms_sites');
        Schema::dropIfExists('cms_themes');
    }
};
