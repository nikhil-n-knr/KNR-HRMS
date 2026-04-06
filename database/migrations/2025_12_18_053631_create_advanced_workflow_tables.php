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
        // 1. Workflows (The Blueprint)
        /*
        // CONFLICT WITH 2025_12_20 MIGRATION
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Standard Leave", "High Value Expense"
            $table->string('module'); // e.g. "Leave", "Attendance"
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Stages (The Steps)
        Schema::create('workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
            $table->integer('stage_order'); // 1, 2, 3
            $table->string('name'); // e.g. "Team Lead Review"
            
            // Approver Logic
            $table->enum('approver_type', ['Role', 'User', 'Manager', 'Self']); 
            $table->foreignId('approver_role_id')->nullable()->constrained('roles');
            $table->foreignId('approver_user_id')->nullable()->constrained('users');
            
            // Advanced Control: SLA & Escalation
            $table->integer('sla_hours')->nullable(); // e.g. 24 hours
            $table->enum('escalation_action', ['Notify', 'AutoApprove', 'AutoReject', 'MoveToNext'])->nullable();
            
            // Parallel Approval Logic
            $table->integer('required_approvals')->default(1); // e.g. 2 of 5 admins must approve

            $table->timestamps();
        });

        // 3. Requests (The Instances)
        Schema::create('workflow_requests', function (Blueprint $table) {
            $table->id();
            $table->string('requestable_type'); // Polymorphic: LeaveRequest, Timesheet
            $table->unsignedBigInteger('requestable_id');
            
            $table->foreignId('workflow_id')->constrained();
            $table->foreignId('current_stage_id')->nullable()->constrained('workflow_stages');
            
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Escalated'])->default('Pending');
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['requestable_type', 'requestable_id']);
        });

        // 4. Request Logs (Audit Trail)
        Schema::create('workflow_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained('workflow_stages');
            $table->foreignId('actor_id')->nullable()->constrained('users'); // Who acted?
            
            $table->enum('action', ['Approved', 'Rejected', 'Escalated', 'Skipped']);
            $table->text('comments')->nullable();
            
            $table->timestamps();
        });
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_logs');
        Schema::dropIfExists('workflow_requests');
        Schema::dropIfExists('workflow_stages');
        Schema::dropIfExists('workflows');
    }
};
