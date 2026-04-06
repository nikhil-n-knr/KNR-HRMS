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
        Schema::create('document_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // header, footer
            $table->longText('content')->nullable(); // For HTML
            $table->json('settings')->nullable(); // For Structured/Config
            $table->string('image_path')->nullable(); // For Logo/Banner
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_components');
    }
};
