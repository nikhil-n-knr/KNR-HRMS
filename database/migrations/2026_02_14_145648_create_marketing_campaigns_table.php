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
        Schema::create('crm_marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('subject');
            $table->string('status')->default('draft'); // draft, scheduled, processing, completed
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            
            // Note: templates table is created AFTER this migration based on timestamp.
            // So we delay the foreign key or just use unsignedBigInteger.
            $table->unsignedBigInteger('template_id')->nullable();
            $table->longText('content')->nullable(); // Snapshot of content
            
            $table->foreignId('segment_id')->nullable()->constrained('crm_contact_segments')->nullOnDelete();
            
            $table->string('type')->default('email'); // email, sms
            
            // Store aggregate stats here for quick access
            $table->json('stats')->nullable(); // {"total": 100, "sent": 98, "opened": 45, "clicked": 12}
            
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->index('template_id'); // We'll manually enforce this application-side
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_marketing_campaigns');
    }
};
