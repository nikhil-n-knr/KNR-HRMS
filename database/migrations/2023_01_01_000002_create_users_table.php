<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('mobile')->unique()->nullable();
            $table->string('password'); // Laravel default, maps to password_hash
            $table->enum('status', ['active', 'suspended', 'inactive'])->default('active');
            $table->string('employee_id')->unique()->nullable();
            $table->enum('employee_type', ['permanent', 'contract', 'gig', 'platform'])->default('permanent');
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->foreignId('location_id')->nullable()->constrained('locations');
            $table->foreignId('manager_id')->nullable()->constrained('users');
            $table->boolean('mfa_enabled')->default(false);
            $table->string('mfa_secret')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
