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
        Schema::create('crm_workflows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('entity_type'); // Lead, Deal, Quote, etc.
            $table->string('trigger_event')->nullable(); // created, updated, status_changed
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamps();
        });

        Schema::create('crm_workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crm_workflow_id')->constrained('crm_workflows')->onDelete('cascade');
            $table->string('name');
            $table->integer('order')->default(0);
            $table->string('action_type')->nullable(); // approval, notification, auto_update
            $table->json('config')->nullable(); // Configuration for the action
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_workflow_stages');
        Schema::dropIfExists('crm_workflows');
    }
};
