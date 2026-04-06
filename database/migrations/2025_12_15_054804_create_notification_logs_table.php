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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_id')->nullable();
            $table->string('channel'); // email, sms, whatsapp
            $table->string('provider')->nullable(); // ses, twilio, etc
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->mediumText('content')->nullable();
            $table->string('status')->default('pending'); // pending, sent, failed, retrying
            $table->integer('retry_count')->default(0);
            $table->json('provider_response')->nullable();
            $table->text('error_message')->nullable();
            $table->string('trace_id')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
