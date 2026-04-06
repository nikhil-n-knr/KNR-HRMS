<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_rotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            // We use integer foreign ID but we haven't found the migration file for shift_rotations yet.
            // Assuming the table will be named 'shift_rotations'.
            // To be safe, we'll create it without 'constrained' first or ensure order.
            // Better: 'constrained()' relies on order. I will create 'shift_rotations' first in logic. 
            // Since I can't find the file, I'll rely on string reference or constrained with table name.
            $table->foreignId('shift_rotation_id')->constrained('shift_rotations')->cascadeOnDelete();
            
            $table->date('start_date');
            $table->date('end_date')->nullable(); // If they leave the rotation

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_rotations');
    }
};
