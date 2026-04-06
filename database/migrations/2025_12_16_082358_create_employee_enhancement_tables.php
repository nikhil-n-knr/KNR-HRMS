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
        // 2. Employee Documents Table
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('title'); // e.g., "Aadhar Card", "Offer Letter"
            $table->string('file_path'); // Storage path
            
            $table->string('file_type')->nullable(); // mime type (pdf, jpg)
            $table->string('file_size')->nullable(); // in KB/MB
            
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
