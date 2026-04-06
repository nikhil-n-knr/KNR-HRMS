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
        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('month'); // YYYY-MM
            $table->integer('tasks_completed')->default(0);
            $table->integer('tasks_overdue')->default(0);
            $table->integer('bugs_raised')->default(0);
            $table->decimal('avg_completion_hours', 8, 2)->default(0);
            $table->integer('quality_score')->default(100); // 0-100
            $table->integer('brownie_points')->default(0);
            $table->timestamps();
            
            // Unique constraint: One metric record per employee per month
            $table->unique(['employee_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_metrics');
    }
};
