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
        Schema::table('cms_media', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_media', 'site_id')) $table->unsignedBigInteger('site_id')->nullable()->after('tenant_id');
            if (!Schema::hasColumn('cms_media', 'url')) $table->string('url')->nullable()->after('file_path');
            if (!Schema::hasColumn('cms_media', 'file_type')) $table->string('file_type')->nullable()->after('mime_type');
            if (!Schema::hasColumn('cms_media', 'file_size')) $table->unsignedBigInteger('file_size')->nullable()->after('file_type');
            if (!Schema::hasColumn('cms_media', 'alt')) $table->string('alt')->nullable()->after('file_size');
            if (!Schema::hasColumn('cms_media', 'caption')) $table->string('caption', 500)->nullable()->after('alt');
            
            // Clean up duplicate 'size' if 'file_size' exists
            if (Schema::hasColumn('cms_media', 'size') && Schema::hasColumn('cms_media', 'file_size')) {
                $table->dropColumn('size');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cms_media', function (Blueprint $table) {
            $cols = ['site_id', 'url', 'file_type', 'file_size', 'alt', 'caption'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('cms_media', $col)) $table->dropColumn($col);
            }
        });
    }
};
