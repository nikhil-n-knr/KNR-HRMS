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
        Schema::create('notification_interactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('notification_id')->index(); // Links to 'notifications' table 'id'
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action'); // viewed, clicked, acknowledged, dismissed
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('details')->nullable(); // JSON or text details
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_interactions');
    }
};
