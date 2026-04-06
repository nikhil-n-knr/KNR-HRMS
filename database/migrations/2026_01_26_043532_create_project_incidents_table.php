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
        Schema::create('project_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('declared_by')->constrained('users');
            
            $table->string('title');
            $table->text('description')->nullable();
            
            // Classification
            $table->enum('type', ['Security Breach', 'Data Loss', 'Critical Issue', 'Performance Degradation']);
            $table->enum('severity', ['High', 'Critical', 'Severe']);
            
            // Lifecycle
            $table->enum('status', ['Active', 'Contained', 'Resolved', 'Post-Mortem'])->default('Active');
            $table->string('lifecycle_stage')->default('Detection'); // e.g. Detection, Analysis, Containment, Eradication, Recovery
            
            // Smart Data
            $table->json('data_impact_map')->nullable(); // Affected Assets/Tables
            $table->json('security_details')->nullable(); // Vectors, IPs
            $table->timestamp('resolved_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_incidents');
    }
};
