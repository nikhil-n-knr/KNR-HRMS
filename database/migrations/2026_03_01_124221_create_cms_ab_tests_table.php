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
        Schema::create('cms_ab_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->constrained('cms_sites')->onDelete('cascade');
            $table->foreignId('page_id')->constrained('cms_pages')->onDelete('cascade');
            
            $table->string('name');
            $table->string('goal')->default('click'); // click, form_submit, purchase, etc.
            $table->json('variants'); // [{name, traffic, views, conversions, winner}]
            $table->enum('status', ['running', 'paused', 'completed'])->default('running');
            $table->string('winner_variant')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_ab_tests');
    }
};
