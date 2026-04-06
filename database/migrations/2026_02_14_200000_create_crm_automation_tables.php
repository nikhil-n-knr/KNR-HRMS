<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_marketing_automations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('trigger_type'); // e.g. contact_created, tag_added
            $table->json('trigger_config')->nullable();
            $table->timestamps();
        });

        Schema::create('crm_automation_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained('crm_marketing_automations')->onDelete('cascade');
            $table->enum('type', ['action', 'condition', 'delay']);
            $table->string('action_type')->nullable(); // e.g. send_email, add_tag
            $table->json('config')->nullable();
            $table->integer('step_order');
            $table->timestamps();
        });

        Schema::create('crm_automation_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained('crm_marketing_automations');
            $table->foreignId('contact_id')->constrained('crm_contacts');
            $table->unsignedBigInteger('current_step_id')->nullable();
            $table->enum('status', ['active', 'completed', 'paused'])->default('active');
            $table->timestamp('next_execution_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_automation_executions');
        Schema::dropIfExists('crm_automation_steps');
        Schema::dropIfExists('crm_marketing_automations');
    }
};
