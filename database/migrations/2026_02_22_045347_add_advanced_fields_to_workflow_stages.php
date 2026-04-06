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
        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->unsignedBigInteger('mentor_id')->nullable()->after('user_id');
            $table->string('color')->nullable()->after('name');
            $table->json('transition_rules')->nullable()->after('is_final');
            
            $table->foreign('mentor_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->dropForeign(['mentor_id']);
            $table->dropColumn(['mentor_id', 'color', 'transition_rules']);
        });
    }
};
