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
        Schema::table('offer_letters', function (Blueprint $table) {
            $table->foreignId('template_id')->nullable()->constrained('offer_templates')->nullOnDelete();
            $table->timestamp('esigned_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('offer_letters', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropColumn(['template_id', 'esigned_at']);
        });
    }
};
