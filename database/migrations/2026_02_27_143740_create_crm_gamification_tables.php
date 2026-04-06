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
        Schema::create('crm_achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('icon')->nullable();
            $table->integer('points_reward')->default(0);
            $table->string('requirement_type'); // deal_won, activity_logged, etc.
            $table->integer('requirement_value'); // number of deals, etc.
            $table->timestamps();
        });

        Schema::create('crm_user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('achievement_id')->constrained('crm_achievements')->onDelete('cascade');
            $table->timestamp('earned_at');
            $table->timestamps();
        });

        Schema::create('crm_user_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('total_points')->default(0);
            $table->integer('deals_won_count')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->integer('activities_count')->default(0);
            $table->integer('current_streak')->default(0);
            $table->timestamps();
            
            $table->unique(['tenant_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_user_stats');
        Schema::dropIfExists('crm_user_achievements');
        Schema::dropIfExists('crm_achievements');
    }
};
