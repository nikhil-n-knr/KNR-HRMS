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
        if (!Schema::hasTable('project_documents')) {
            Schema::create('project_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()->onDelete('cascade');
                $table->foreignId('module_id')->nullable()->constrained('project_modules')->nullOnDelete();
                $table->foreignId('uploader_id')->constrained('users');
                
                $table->string('name');
                $table->string('category')->default('General'); // MOM, DFD, Contract, Design, etc.
                $table->string('file_path');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('file_size')->default(0); // in bytes
                
                // Access Control
                $table->string('visibility')->default('team'); // 'public', 'team', 'private'
                $table->json('shared_with')->nullable(); // Array of User IDs if private/shared specifically
                
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_documents');
    }
};
