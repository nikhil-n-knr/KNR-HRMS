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
        Schema::table('crm_deals', function (Blueprint $table) {
            $table->integer('health_score')->default(100)->after('probability');
            $table->decimal('weighted_value', 15, 2)->nullable()->after('value');
            $table->json('ai_insights')->nullable()->after('health_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_deals', function (Blueprint $table) {
            $table->dropColumn(['health_score', 'weighted_value', 'ai_insights']);
        });
    }
};
