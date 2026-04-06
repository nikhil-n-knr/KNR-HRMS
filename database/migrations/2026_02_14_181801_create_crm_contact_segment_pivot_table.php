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
        Schema::create('crm_contact_segment_pivot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_segment_id')->constrained('crm_contact_segments')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('crm_contacts')->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['contact_segment_id', 'contact_id'], 'segment_contact_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_contact_segment_pivot');
    }
};
