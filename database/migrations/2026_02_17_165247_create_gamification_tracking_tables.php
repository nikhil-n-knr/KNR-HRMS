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
        if (!Schema::hasTable('employee_points')) {
            Schema::create('employee_points', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
                $table->foreignId('rule_id')->nullable()->constrained('point_rules')->nullOnDelete();
                $table->integer('points');
                $table->string('event_key');
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('employee_badges')) {
            Schema::create('employee_badges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
                $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
                $table->timestamp('awarded_at')->useCurrent();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_badges');
        Schema::dropIfExists('employee_points');
    }
};
