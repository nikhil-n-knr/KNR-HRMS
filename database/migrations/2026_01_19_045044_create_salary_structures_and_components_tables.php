<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., India Standard
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_structure_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Basic, HRA
            $table->enum('type', ['earning', 'deduction']);
            $table->enum('calculation_type', ['fixed', 'percentage', 'formula'])->default('fixed');
            $table->decimal('value', 10, 2)->default(0); // For percentage or fixed amount
            $table->string('formula')->nullable(); // e.g., "ctc * 0.4"
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('salary_components');
        Schema::dropIfExists('salary_structures');
    }
};
