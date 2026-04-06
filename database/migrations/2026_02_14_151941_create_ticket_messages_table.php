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
        Schema::create('crm_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id')->nullable(); // Agent ID
            
            // If message is from contact (customer), user_id is null.
            // We can infer sender from context or add explicit type.
            $table->string('sender_type')->default('agent'); // agent, contact, system
            
            $table->text('message');
            $table->boolean('is_internal')->default(false); // Internal notes
            
            $table->json('attachments')->nullable();
            
            $table->timestamps();
            
            $table->foreign('ticket_id')->references('id')->on('crm_tickets')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_ticket_messages');
    }
};
