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
        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            
            // Activity Type
            $table->enum('type', ['note', 'task', 'email', 'call', 'meeting', 'sms'])->default('note');
            
            // Activity Details
            $table->string('subject');
            $table->text('description')->nullable();
            
            // Timing
            $table->timestamp('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_completed')->default(false);
            
            // Priority (for tasks)
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            
            // Polymorphic relations - activity can belong to Contact, Account, Deal, or Lead
            $table->morphs('activityable'); // This already creates an index
            
            // Metadata
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['tenant_id', 'type']);
            $table->index('is_completed');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_activities');
    }
};
