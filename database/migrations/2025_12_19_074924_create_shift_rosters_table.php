<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('shift_id')->constrained()->restrictOnDelete();
            
            // Scheduling Metadata
            $table->boolean('is_published')->default(true);
            $table->string('notes')->nullable(); // e.g., "Manual Override", "Swapped"
            $table->foreignId('assigned_by')->nullable()->constrained('users'); // Auditor

            $table->timestamps();

            // Constraint: One roster entry per employee per day
            $table->unique(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_rosters');
    }
};
