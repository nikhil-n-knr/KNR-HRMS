<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. workflows table
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('entity_type', 100); // 'timesheet', 'leave_request', 'attendance_regularization', etc.
            $table->string('trigger_event', 100)->nullable(); // 'on_submit', 'on_update', 'manual'
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamps();
            
            $table->index(['entity_type', 'is_active']);
        });

        // 2. workflow_stages table
        Schema::create('workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // 'Manager Review', 'HR Approval'
            $table->integer('stage_order'); // 1, 2, 3...
            $table->enum('approver_type', ['manager', 'team_lead', 'role', 'specific_user','department_head']);
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('can_reject')->default(true);
            $table->boolean('can_edit')->default(false);
            $table->integer('auto_approve_after_hours')->nullable();
            $table->boolean('is_parallel')->default(false);
            $table->timestamps();
            
            $table->index(['workflow_id', 'stage_order']);
        });

        // 3. workflow_instances table
        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained();
            $table->string('entity_type', 100);
            $table->unsignedBigInteger('entity_id');
            $table->foreignId('initiator_id')->constrained('users');
            $table->foreignId('current_stage_id')->nullable()->constrained('workflow_stages')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['entity_type', 'entity_id']);
            $table->index(['status', 'initiator_id']);
        });

        // 4. workflow_approvals table
        Schema::create('workflow_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_instance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('workflow_stages');
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('comments')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();
            
            $table->index(['approver_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_approvals');
        Schema::dropIfExists('workflow_instances');
        Schema::dropIfExists('workflow_stages');
        Schema::dropIfExists('workflows');
    }
};
