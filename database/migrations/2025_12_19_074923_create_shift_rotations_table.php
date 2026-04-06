<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('shift_rotations')) {
            Schema::create('shift_rotations', function (Blueprint $table) {
                $table->id();
                $table->string('name'); 
                $table->integer('cycle_days')->default(7); 
                $table->json('pattern')->nullable(); 
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_rotations');
    }
};
