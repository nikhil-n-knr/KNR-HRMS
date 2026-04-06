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
        Schema::create('vault_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('icon')->nullable(); // Heroicon name
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('vault_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('vault_categories')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->integer('order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_client_visible')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vault_articles');
        Schema::dropIfExists('vault_categories');
    }
};
