<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('card_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type')->default('Standard'); // Employee, Vendor, Visitor, Standard
            $table->string('orientation')->default('Landscape'); // Landscape | Portrait
            $table->json('dimensions')->nullable(); // {width: 1011, height: 638, dpi: 300}
            $table->json('elements')->nullable();   // Legacy Fabric.js JSON
            $table->longText('design_data')->nullable(); // v2 Studio: { front: {...}, back: {...} }
            $table->string('preview_image')->nullable(); // Path to stored PNG (NOT base64 inline)
            $table->string('base_image_url')->nullable(); // Background/overlay URL
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('card_templates');
    }
};
