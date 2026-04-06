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
        if (!Schema::hasTable('crm_meeting_notes')) {
            Schema::create('crm_meeting_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('meeting_id')->constrained('crm_meetings')->onDelete('cascade');
                $table->longText('summary')->nullable();
                $table->longText('decisions')->nullable();
                $table->longText('next_steps')->nullable();
                $table->date('follow_up_date')->nullable();
                $table->json('discussed_products')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_meeting_notes');
    }
};
