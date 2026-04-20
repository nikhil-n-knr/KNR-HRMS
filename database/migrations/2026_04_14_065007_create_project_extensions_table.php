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
        Schema::create('project_extensions', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('project_id')->constrained()->onDelete('cascade');
            $blueprint->enum('type', ['time', 'effort', 'both'])->default('both');
            $blueprint->decimal('hours_added', 8, 2)->default(0);
            $blueprint->integer('days_added')->default(0);
            $blueprint->string('reason'); // misestimation, resource_busy, scope_creep, etc.
            $blueprint->text('notes')->nullable();
            $blueprint->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_extensions');
    }
};
