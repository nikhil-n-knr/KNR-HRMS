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
        if (!Schema::hasTable('shift_rotations')) {
            Schema::create('shift_rotations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('frequency'); // weekly, bi-weekly, monthly
                $table->json('pattern'); // ["Shift A", "Shift B", "Shift C"]
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('employee_rotation')) {
            Schema::create('employee_rotation', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('rotation_id')->constrained('shift_rotations')->onDelete('cascade');
                $table->date('start_date');
                $table->integer('current_step')->default(0); // Index in pattern array
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_rotation');
        Schema::dropIfExists('shift_rotations');
    }
};
