<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('knowledge_articles');
        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->default('General');
            $table->text('summary');
            $table->longText('content')->nullable();
            $table->string('icon')->default('BookOpenIcon');
            $table->integer('read_time')->default(5);
            $table->boolean('is_featured')->default(false);
            $table->string('visibility')->default('public'); // public, internal, client_shared
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_articles');
    }
};
