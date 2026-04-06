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
        Schema::create('module_activation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            
            // OTP/Key
            $table->string('otp_code', 10)->nullable()->comment('6-digit OTP or dev key');
            $table->string('license_key')->nullable()->comment('Alternative: license key');
            
            // Status
            $table->enum('status', ['pending', 'verified', 'expired', 'failed'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at')->nullable()->comment('OTP expiry time');
            
            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->integer('attempts')->default(0)->comment('Failed verification attempts');
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'module_id']);
            $table->index('status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_activation_requests');
    }
};
