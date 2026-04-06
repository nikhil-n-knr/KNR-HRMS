<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            
            // Period
            $table->integer('year');
            $table->integer('month');
            $table->integer('quarter'); // Calculated: 1-4
            
            // Assigned User/Team
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('cascade');
            
            // Forecast Amounts
            $table->decimal('pipeline_total', 15, 2)->default(0); // Total value in pipeline
            $table->decimal('weighted_pipeline', 15, 2)->default(0); // Weighted by probability
            $table->decimal('committed', 15, 2)->default(0); // High confidence deals
            $table->decimal('best_case', 15, 2)->default(0); // Optimistic prediction
            $table->decimal('worst_case', 15, 2)->default(0); // Pessimistic prediction
            $table->decimal('actual_revenue', 15, 2)->nullable(); // Filled when period ends
            
            // Metrics
            $table->integer('deals_count')->default(0);
            $table->integer('won_deals')->default(0);
            $table->integer('lost_deals')->default(0);
            $table->decimal('win_rate', 5, 2)->default(0); // Percentage
            
            // Notes & Adjustments
            $table->text('notes')->nullable();
            $table->json('adjustments')->nullable(); // Manual forecast adjustments
            
            $table->timestamps();
            
            // Unique constraint: one forecast per user/team per period
            $table->unique(['tenant_id', 'user_id', 'year', 'month']);
            $table->index(['tenant_id', 'year', 'quarter']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_forecasts');
    }
};
