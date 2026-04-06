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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('work_assignments');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('project_modules');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('clients');
        Schema::enableForeignKeyConstraints();

        // 1. Clients Table
        Schema::create('clients', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->boolean('portal_access')->default(false);
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Projects Table
        Schema::create('projects', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('planning');
            $table->string('visibility')->default('team_locked');
            $table->date('start_date')->nullable();
            $table->date('deadline')->nullable();
            $table->json('gamification_settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Project Modules Table
        Schema::create('project_modules', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('project_modules')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 4. Tasks Table
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('module_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('todo'); 
            $table->string('priority')->default('medium'); 
            $table->string('complexity')->default('medium'); 
            
            $table->decimal('estimated_hours', 8, 2)->default(0);
            $table->decimal('actual_hours', 8, 2)->default(0);
            $table->boolean('billable')->default(true);
            $table->unsignedBigInteger('blocked_by_task_id')->nullable();

            $table->unsignedBigInteger('created_by'); 
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Work Assignments
        Schema::create('work_assignments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('task_id')->nullable();
            $table->unsignedBigInteger('assignee_id');
            $table->string('assignee_type');
            
            $table->decimal('allocated_hours', 8, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->index(['assignee_id', 'assignee_type']);
            $table->index(['task_id', 'assignee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_assignments');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('project_modules');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('clients');
    }
};
