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
        Schema::table('crm_email_threads', function (Blueprint $table) {
            $table->string('trackable_type')->nullable()->change();
            $table->unsignedBigInteger('trackable_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_email_threads', function (Blueprint $table) {
            $table->string('trackable_type')->nullable(false)->change();
            $table->unsignedBigInteger('trackable_id')->nullable(false)->change();
        });
    }
};
