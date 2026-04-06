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
        Schema::create('app_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique(); // e.g., 'user_management'
            $table->string('icon')->nullable(); // e.g., 'UsersIcon'
            $table->integer('order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('app_sub_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('app_modules')->onDelete('cascade');
            $table->string('name');
            $table->string('key'); // e.g., 'user'
            $table->string('route')->nullable(); // e.g., 'users.index'
            $table->integer('order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['module_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_sub_modules');
        Schema::dropIfExists('app_modules');
    }
};
