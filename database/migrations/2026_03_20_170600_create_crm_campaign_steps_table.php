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
        if (!Schema::hasTable('crm_campaign_steps')) {
            Schema::create('crm_campaign_steps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_journey_id')->constrained('crm_campaign_journeys')->onDelete('cascade');
                $table->string('type'); // email, wait, condition
                $table->json('configuration')->nullable();
                $table->integer('order_index')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_campaign_steps');
    }
};
