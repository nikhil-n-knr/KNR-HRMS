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
        /*
        Schema::dropIfExists('approval_steps');
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('workflow_stages');
        Schema::dropIfExists('workflows');
        */
        if (Schema::hasColumn('users', 'team_id')) {
             try {
                 Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('team_id');
                 });
             } catch (\Exception $e) {}
        }
        Schema::dropIfExists('teams');
        Schema::enableForeignKeyConstraints();

        // 1. Teams (Org Hierarchy)
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            
            $table->unsignedBigInteger('manager_id')->nullable()->index();
            $table->foreign('manager_id', 'fk_teams_manager')->references('id')->on('users')->nullOnDelete();
            
            $table->unsignedBigInteger('parent_team_id')->nullable()->index();
            $table->foreign('parent_team_id', 'fk_teams_parent')->references('id')->on('teams')->nullOnDelete();
            
            $table->timestamps();
        });

        // Add team_id to employees/users if not exists
        if (!Schema::hasColumn('users', 'team_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('team_id')->nullable()->after('department_id')->constrained('teams')->nullOnDelete()->index();
            });
        }

        // 2. Workflows (Definition)
        /*
        // DISABLING LEGACY WORKFLOW TABLES TO PREVENT CONFLICT WITH 2026_02_11 MIGRATION
        if (!Schema::hasTable('workflows')) {
             Schema::create('workflows', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('description')->nullable();
                $table->string('trigger_event')->unique(); // e.g., 'leave_request', 'timesheet_submission'
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 3. Workflow Stages (Steps in the definition)
         if (!Schema::hasTable('workflow_stages')) {
            Schema::create('workflow_stages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('workflow_id');
                $table->foreign('workflow_id', 'fk_ws_workflow')->references('id')->on('workflows')->onDelete('cascade');
                
                $table->string('name');
                $table->string('approver_type');
                
                $table->unsignedBigInteger('role_id')->nullable();
                $table->foreign('role_id', 'fk_ws_role')->references('id')->on('roles');
                
                $table->unsignedBigInteger('user_id')->nullable();
                $table->foreign('user_id', 'fk_ws_user')->references('id')->on('users');

                $table->integer('order')->default(1);
                $table->integer('sla_hours')->nullable();
                $table->timestamps();

                $table->index(['workflow_id', 'order']);
            });
         }

        // 4. Approvals (Active Instances)
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_id');
            $table->foreign('workflow_id', 'fk_app_workflow')->references('id')->on('workflows');

            $table->string('approvable_type');
            $table->unsignedBigInteger('approvable_id');
            
            $table->unsignedBigInteger('requester_id')->index();
            $table->foreign('requester_id', 'fk_app_requester')->references('id')->on('users');

            $table->string('status')->default('pending')->index();
            $table->integer('current_stage_order')->default(1);
            $table->timestamps();
            
            $table->index(['approvable_type', 'approvable_id']);
        });

        // 5. Approval Steps (Individual Actions)
        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approval_id');
            $table->foreign('approval_id', 'fk_step_approval')->references('id')->on('approvals')->onDelete('cascade');
            
            $table->unsignedBigInteger('stage_id')->nullable();
            $table->foreign('stage_id', 'fk_step_stage')->references('id')->on('workflow_stages')->nullOnDelete();
            
            $table->unsignedBigInteger('approver_id')->nullable()->index();
            $table->foreign('approver_id', 'fk_step_approver')->references('id')->on('users');

            $table->string('status')->default('pending')->index();
            $table->timestamp('actioned_at')->nullable();
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
        Schema::dropIfExists('approval_steps');
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('workflow_stages');
        Schema::dropIfExists('workflows');
        
        if (Schema::hasColumn('users', 'team_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['team_id']);
                $table->dropColumn('team_id');
            });
        }
        
        Schema::dropIfExists('teams');
    }
};
