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
        Schema::create('offer_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained('job_applications')->cascadeOnDelete();
            
            $table->date('offer_date');
            $table->date('joining_date');
            $table->date('expiry_date')->nullable();
            
            // International
            $table->string('salary_currency')->default('INR');
            $table->decimal('salary_amount', 15, 2);
            $table->json('salary_breakdown')->nullable(); // Components
            
            $table->string('status')->default('Draft'); // Draft, Pending Approval, Sent, Accepted, Rejected
            $table->string('token')->unique()->nullable(); // Public link
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_letters');
    }
};
