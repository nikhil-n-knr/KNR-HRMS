<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('salary_structure_id')->constrained('salary_structures'); // The template used
            
            $table->decimal('annual_ctc', 12, 2);
            $table->date('effective_date'); // When this salary starts
            
            // JSON to store exact breakdown at this point in time
            // e.g. { "basic": 50000, "hra": 20000, "pf": 1800 }
            $table->json('breakdown')->nullable(); 
            
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable(); // e.g., "Annual Appraisal 2024"
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_salaries');
    }
};
