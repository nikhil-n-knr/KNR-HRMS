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
        // 1. LMS Plans
        Schema::create('lms_plans', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name');
            $blueprint->string('slug')->unique();
            $blueprint->text('description')->nullable();
            $blueprint->decimal('price', 10, 2);
            $blueprint->string('currency', 3)->default('INR');
            $blueprint->enum('billing_interval', ['once', 'monthly', 'quarterly', 'yearly'])->default('once');
            $blueprint->json('features')->nullable();
            $blueprint->boolean('is_active')->default(true);
            $blueprint->timestamps();
        });

        // 2. LMS Subscriptions
        Schema::create('lms_subscriptions', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('plan_id')->nullable()->constrained('lms_plans')->onDelete('set null');
            $blueprint->foreignId('course_id')->nullable()->constrained('lms_courses')->onDelete('cascade');
            $blueprint->string('status')->default('active'); // active, expired, cancelled, pending
            $blueprint->dateTime('starts_at')->nullable();
            $blueprint->dateTime('ends_at')->nullable();
            $blueprint->json('metadata')->nullable();
            $blueprint->timestamps();
        });

        // 3. LMS Transactions
        Schema::create('lms_transactions', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('subscription_id')->nullable()->constrained('lms_subscriptions')->onDelete('set null');
            $blueprint->decimal('amount', 10, 2);
            $blueprint->string('currency', 3)->default('INR');
            $blueprint->string('payment_provider')->nullable(); // stripe, razorpay, manual
            $blueprint->string('transaction_reference')->unique();
            $blueprint->string('status')->default('pending'); // successful, failed, pending, refunded
            $blueprint->json('response_data')->nullable(); // Full gateway response
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_transactions');
        Schema::dropIfExists('lms_subscriptions');
        Schema::dropIfExists('lms_plans');
    }
};
