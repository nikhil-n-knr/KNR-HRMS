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
        Schema::create('organization_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            
            // Activation
            $table->boolean('is_activated')->default(false);
            $table->timestamp('activated_at')->nullable();
            $table->foreignId('activated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('activation_key')->nullable()->comment('License key used for activation');
            
            // Trial/Subscription
            $table->boolean('is_trial')->default(false);
            $table->timestamp('trial_ends_at')->nullable();
            $table->enum('subscription_status', ['active', 'expired', 'cancelled', 'pending'])->default('pending');
            $table->timestamp('subscription_starts_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            
            // Usage limits
            $table->integer('user_limit')->nullable()->comment('Max users allowed (NULL = unlimited)');
            $table->integer('current_users')->default(0)->comment('Current active users');
            
            // Settings
            $table->json('settings')->nullable()->comment('Module-specific org settings');
            
            $table->timestamps();
            
            $table->unique(['tenant_id', 'module_id']);
            $table->index('is_activated');
            $table->index('subscription_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_modules');
    }
};
