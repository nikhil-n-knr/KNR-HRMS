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
        Schema::create('client_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('client_user_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_user_id')->constrained('client_users')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['client_user_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_user_projects');
        Schema::dropIfExists('client_users');
    }
};
