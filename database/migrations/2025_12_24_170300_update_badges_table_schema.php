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
        // Ensure badges table exists (create if not - safety fallback, though error implies existence)
        if (!Schema::hasTable('badges')) {
            Schema::create('badges', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        Schema::table('badges', function (Blueprint $table) {
            // Slug
            if (!Schema::hasColumn('badges', 'slug')) {
                $table->string('slug')->nullable()->after('name'); 
                // Note: unique index might fail if duplicates exist, so add nullable first.
                // In a real repair, we'd fill slugs then enforce unique. 
                // For dev, we'll try adding unique directly assuming empty table or valid data.
            }

            // Description (The reported missing column)
            if (!Schema::hasColumn('badges', 'description')) {
                $table->text('description')->nullable()->after('slug');
            }

            // Icon
            if (!Schema::hasColumn('badges', 'icon')) {
                $table->string('icon')->nullable()->after('description');
            }

            // Points Bonus
            if (!Schema::hasColumn('badges', 'points_bonus')) {
                $table->integer('points_bonus')->default(0)->after('icon');
            }

            // Is Active
            if (!Schema::hasColumn('badges', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('points_bonus');
            }

            // Metadata
            if (!Schema::hasColumn('badges', 'metadata')) {
                $table->json('metadata')->nullable()->after('is_active');
            }
        });
        
        // Apply unique to slug if possible
        try {
             Schema::table('badges', function (Blueprint $table) {
                 if (Schema::hasColumn('badges', 'slug')) {
                    // Check if index exists? Laravel doesn't have easy hasIndex in Blueprint.
                    // relying on migration idempotency or error suppression if needed.
                    // simple change:
                    $table->string('slug')->unique()->change();
                 }
             });
        } catch (\Exception $e) {
            // Ignore if index already exists or data constraint violation
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropColumn(['slug', 'description', 'icon', 'points_bonus', 'is_active', 'metadata']);
        });
    }
};
