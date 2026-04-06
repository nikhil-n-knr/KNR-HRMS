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
        // 1. Git Providers (GitHub, GitLab, etc.)
        if (!Schema::hasTable('git_providers')) {
            Schema::create('git_providers', function (Blueprint $table) {
                $table->id();
                $table->enum('type', ['github', 'bitbucket', 'gitlab', 'azure']);
                $table->string('name')->comment('Display name like "Company GitHub"');
                $table->text('access_token')->comment('Encrypted');
                $table->text('refresh_token')->nullable()->comment('Encrypted');
                $table->text('client_id')->nullable()->comment('Encrypted');
                $table->text('client_secret')->nullable()->comment('Encrypted');
                $table->string('base_url')->nullable()->comment('For self-hosted instances');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Git Repositories (Linked to Projects)
        if (!Schema::hasTable('git_repositories')) {
            Schema::create('git_repositories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('git_provider_id')->constrained()->cascadeOnDelete();
                $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
                $table->string('external_id')->comment('Repo ID from provider');
                $table->string('name');
                $table->string('url')->nullable();
                $table->string('clone_url')->nullable();
                $table->json('branch_filter')->nullable()->comment('Array of tracked branches');
                $table->json('path_rules')->nullable()->comment('Map folders to modules');
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. User Mapping (HRMS User <-> Git Identity)
        if (!Schema::hasTable('git_user_maps')) {
            Schema::create('git_user_maps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('git_provider_id')->constrained()->cascadeOnDelete();
                $table->string('git_username')->nullable();
                $table->string('git_email')->nullable();
                $table->string('git_avatar_url')->nullable();
                $table->timestamps();

                $table->unique(['git_provider_id', 'git_email']); // Avoid duplicates per provider
            });
        }

        // 4. Git Commits
        if (!Schema::hasTable('git_commits')) {
            Schema::create('git_commits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('git_repository_id')->constrained()->cascadeOnDelete();
                $table->string('hash')->index();
                $table->text('message');
                $table->string('author_name');
                $table->string('author_email');
                $table->timestamp('committed_at');
                $table->integer('additions')->default(0);
                $table->integer('deletions')->default(0);
                $table->timestamps();

                $table->unique(['git_repository_id', 'hash']);
            });
        }

        // 5. Pull Requests
        if (!Schema::hasTable('git_pull_requests')) {
            Schema::create('git_pull_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('git_repository_id')->constrained()->cascadeOnDelete();
                $table->string('external_id');
                $table->string('title');
                $table->string('state')->comment('open, merged, closed');
                $table->string('author_name');
                $table->json('reviewers')->nullable();
                $table->timestamp('created_at_provider')->nullable();
                $table->timestamp('merged_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();

                $table->unique(['git_repository_id', 'external_id']);
            });
        }

        // 6. Task Links (Pivot)
        if (!Schema::hasTable('task_git_links')) {
            Schema::create('task_git_links', function (Blueprint $table) {
                $table->id();
                // Assuming 'project_tasks' is the table name for Task model
                $table->foreignId('task_id')->constrained('project_tasks')->cascadeOnDelete();
                $table->foreignId('git_commit_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('git_pull_request_id')->nullable()->constrained()->nullOnDelete();
                $table->string('link_type')->default('auto')->comment('auto (regex) or manual');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_git_links');
        Schema::dropIfExists('git_pull_requests');
        Schema::dropIfExists('git_commits');
        Schema::dropIfExists('git_user_maps');
        Schema::dropIfExists('git_repositories');
        Schema::dropIfExists('git_providers');
    }
};
