<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_support_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('crm_sla_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained('crm_support_policies')->onDelete('cascade');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent']);
            $table->integer('response_time_minutes');
            $table->integer('resolve_time_minutes');
            $table->timestamps();
        });

        Schema::table('crm_tickets', function (Blueprint $table) {
            $table->foreignId('sla_policy_id')->nullable()->constrained('crm_support_policies');
            $table->timestamp('response_due_at')->nullable();
            $table->timestamp('resolve_due_at')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->enum('sla_status', ['compliant', 'response_breached', 'resolution_breached'])->default('compliant');
        });
    }

    public function down(): void
    {
        Schema::table('crm_tickets', function (Blueprint $table) {
            $table->dropForeign(['sla_policy_id']);
            $table->dropColumn(['sla_policy_id', 'response_due_at', 'resolve_due_at', 'first_response_at', 'resolved_at', 'sla_status']);
        });
        Schema::dropIfExists('crm_sla_targets');
        Schema::dropIfExists('crm_support_policies');
    }
};
