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
        Schema::create('pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            
            // Stage Details
            $table->string('name'); // e.g., "Qualification", "Proposal", "Negotiation"
            $table->string('color')->default('#3B82F6'); // Hex color for Kanban board
            $table->integer('order')->default(0); // Sort order
            $table->enum('type', ['open', 'won', 'lost'])->default('open'); // Stage type
            
            // Probability & Forecasting
            $table->integer('win_probability')->default(0); // 0-100%
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false); // Default stage for new deals
            
            // Automation
            $table->json('automation_rules')->nullable(); // Actions when deal enters this stage
            $table->integer('expected_duration_days')->nullable(); // For SLA tracking
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pipeline_stages');
    }
};
