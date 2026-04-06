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
        Schema::create('crm_contact_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->integer('total_records')->default(0);
            $table->integer('processed_records')->default(0);
            $table->integer('failed_records')->default(0);
            $table->json('errors')->nullable(); // Store validation errors
            $table->foreignId('imported_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_contact_imports');
    }
};
