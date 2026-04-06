<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->integer('version')->default(1);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->string('submodule');
            $table->string('action');
            $table->text('description')->nullable();
            $table->json('ai_hint')->nullable();
            $table->timestamps();

            $table->unique(['module', 'submodule', 'action']);
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->enum('data_scope', ['self', 'team', 'department', 'location', 'tenant', 'global'])->default('tenant');
            $table->integer('limit_value')->nullable();
            $table->string('limit_type')->nullable();
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
        });

        Schema::create('user_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users');
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
        });

        Schema::create('user_scope', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions');
            $table->enum('data_scope', ['self', 'team', 'department', 'location', 'tenant', 'global']);
            $table->integer('limit_value')->nullable();
            $table->text('reason')->nullable();
            $table->date('valid_until')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['user_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_scope');
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
