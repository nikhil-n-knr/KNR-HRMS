<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_priorities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color')->default('#6366f1');
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->unique(['project_id', 'name']);
        });

        // Convert tasks.priority from ENUM to String to allow custom values
        // Note: Using raw SQL for ENUM modification as it's cleaner in MySQL/MariaDB
        DB::statement("ALTER TABLE project_tasks MODIFY COLUMN priority VARCHAR(255) DEFAULT 'Medium'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_priorities');
        // Reverting enum is tricky without knowing exact original state, skipping for safety or defining explicitly
        // DB::statement("ALTER TABLE project_tasks MODIFY COLUMN priority ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium'");
    }
};
