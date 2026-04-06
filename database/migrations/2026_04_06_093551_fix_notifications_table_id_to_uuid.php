<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to fix the notifications table ID to UUID.
     * The table currently has a bigint auto_increment ID which fails 
     * with Laravel's standard notification UUID inserts.
     */
    public function up(): void
    {
        // 1. Drop existing table if any (safe for broken notifications log)
        // or attempt to convert it. To be 100% stable with SQLite/MySQL/etc
        // recreation is the most solid way for changing primary keys from int to uuid.
        
        Schema::dropIfExists('notifications');
        
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::dropIfExists('notifications');
         // Fallback to legacy bigint structure if needed
         Schema::create('notifications', function (Blueprint $table) {
             $table->bigIncrements('id');
             $table->string('type');
             $table->morphs('notifiable');
             $table->text('data');
             $table->timestamp('read_at')->nullable();
             $table->timestamps();
         });
    }
};
