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
        Schema::table('document_templates', function (Blueprint $table) {
            $table->json('layout_config')->nullable()->after('type'); // Stores height, padding, margins, etc.
            $table->json('pages_data')->nullable()->after('body_html'); // Stores content for multi-page docs
            $table->string('header_image')->nullable()->after('header_html');
            $table->string('footer_image')->nullable()->after('footer_html');
            $table->string('watermark_image')->nullable()->after('watermark_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_templates', function (Blueprint $table) {
            //
        });
    }
};
