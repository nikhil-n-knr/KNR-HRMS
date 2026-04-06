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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('Internal module name (e.g., crm, analytics)');
            $table->string('display_name')->comment('User-facing module name');
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable()->comment('Icon identifier');
            $table->boolean('is_premium')->default(true)->comment('Is this a premium/paid module?');
            $table->boolean('is_active')->default(true)->comment('Is module available?');
            $table->decimal('base_price', 10, 2)->nullable()->comment('Price per user/month');
            $table->boolean('requires_activation')->default(true)->comment('Requires activation key?');
            $table->enum('activation_type', ['otp', 'license_key', 'none'])->default('otp');
            $table->json('settings')->nullable()->comment('Module-specific settings');
            $table->timestamps();
            
            $table->index('name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
