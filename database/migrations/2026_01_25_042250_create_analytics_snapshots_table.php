<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('analytics_snapshots')) {
            Schema::create('analytics_snapshots', function (Blueprint $table) {
                $table->id();
                $table->date('snapshot_date')->index(); // e.g. 2024-01-01
                
                $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
                
                // Denormalized data for fast querying (Time Machine)
                $table->string('department_name')->nullable(); // Store name to handle renames
                $table->string('designation')->nullable();
                
                $table->decimal('annual_ctc', 15, 2)->default(0);
                $table->unsignedTinyInteger('performance_rating')->nullable(); // 1-5
                
                $table->integer('tenure_months')->default(0);
                $table->string('gender')->nullable(); // Male, Female, Other
                $table->string('location_name')->nullable();
                
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_snapshots');
    }
};
