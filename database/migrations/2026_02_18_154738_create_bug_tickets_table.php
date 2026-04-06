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
        Schema::create('bug_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('project_modules')->onDelete('cascade');
            $table->foreignId('task_id')->nullable()->constrained('project_tasks')->onDelete('set null');
            
            // Polymorphic Reporter (User or Employee)
            $table->unsignedBigInteger('reporter_id');
            $table->string('reporter_type');
            
            // Polymorphic Assignee (Employee or Team)
            $table->unsignedBigInteger('assignee_id')->nullable();
            $table->string('assignee_type')->nullable(); // Nullable for unassigned

            $table->enum('severity', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->enum('priority', ['urgent', 'high', 'normal', 'low'])->default('normal');
            
            $table->foreignId('workflow_stage_id')->nullable()->constrained('workflow_stages')->onDelete('set null');
            
            $table->json('environment_metadata')->nullable(); // OS, Browser, Version
            $table->text('steps_to_reproduce')->nullable();
            $table->text('resolution_summary')->nullable();
            
            $table->boolean('is_client_visible')->default(false);
            
            $table->string('subject');
            $table->text('description')->nullable();
            $table->json('attachments')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['project_id', 'module_id']);
            $table->index(['reporter_type', 'reporter_id']);
            $table->index(['assignee_type', 'assignee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bug_tickets');
    }
};
