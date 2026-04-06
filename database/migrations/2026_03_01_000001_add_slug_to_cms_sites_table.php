<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_sites', function (Blueprint $table) {
            // Slug is a short identifier for subdomain-based routing (e.g. myshop.KNR Office.in)
            if (!Schema::hasColumn('cms_sites', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
        });

        // Backfill slugs for existing sites
        \DB::table('cms_sites')->whereNull('slug')->orWhere('slug', '')->get()->each(function ($site) {
            $slug = \Str::slug($site->name) ?: 'site-' . $site->id;
            \DB::table('cms_sites')->where('id', $site->id)->update(['slug' => $slug . '-' . $site->id]);
        });
    }

    public function down(): void
    {
        Schema::table('cms_sites', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
