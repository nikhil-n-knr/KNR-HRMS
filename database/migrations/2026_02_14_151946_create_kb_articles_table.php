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
        Schema::create('crm_kb_articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('author_id')->nullable();
            
            $table->string('title');
            $table->string('slug');
            $table->longText('content');
            $table->string('status')->default('draft'); // draft, published
            $table->integer('views')->default(0);
            
            $table->timestamps();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('crm_kb_categories')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['tenant_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_kb_articles');
    }
};
