<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * NOTE: The initial migration (2026_01_24_400000) already includes design_data,
     * orientation, and base_image_url as of the v2 Studio refactor.
     * This migration now only runs safe conditional adds for environments
     * that had the old schema (before the base migration was updated).
     */
    public function up(): void
    {
        Schema::table('card_templates', function (Blueprint $table) {
            // Safely add columns only if they don't already exist
            if (!Schema::hasColumn('card_templates', 'design_data')) {
                $table->longText('design_data')->nullable()->after('elements');
            }
            if (!Schema::hasColumn('card_templates', 'orientation')) {
                $table->string('orientation')->default('Landscape')->after('type');
            }
            if (!Schema::hasColumn('card_templates', 'base_image_url')) {
                $table->string('base_image_url')->nullable()->after('preview_image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_templates', function (Blueprint $table) {
            // Only drop columns that this migration added (conditionally)
            $toDrop = [];
            if (Schema::hasColumn('card_templates', 'design_data')) $toDrop[] = 'design_data';
            if (Schema::hasColumn('card_templates', 'orientation')) $toDrop[] = 'orientation';
            if (Schema::hasColumn('card_templates', 'base_image_url')) $toDrop[] = 'base_image_url';
            if (!empty($toDrop)) $table->dropColumn($toDrop);
        });
    }
};
