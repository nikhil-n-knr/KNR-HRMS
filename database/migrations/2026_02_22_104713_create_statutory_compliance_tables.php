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
        // 1. Update Locations for Multi-State Support
        Schema::table('locations', function (Blueprint $table) {
            $table->boolean('pt_enabled')->default(false);
            $table->boolean('lwf_enabled')->default(false);
            $table->boolean('is_hq')->default(false);
            $table->string('state_code', 2)->nullable(); // KA, TN, DL, etc.
        });

        // 2. Compliance Rules (PT, LWF, Leaves)
        Schema::create('compliance_state_rules', function (Blueprint $table) {
            $table->id();
            $table->string('state_code', 2);
            $table->string('component'); // professional_tax, lwf, leave_mandate
            $table->json('rules'); // Stores slabs, frequencies, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Licences (Digital Vault)
        Schema::create('compliance_licences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('document_path');
            $table->string('state_code', 2);
            $table->foreignId('location_id')->nullable()->constrained();
            $table->date('expiry_date');
            $table->json('metadata')->nullable(); // For custom fields
            $table->timestamps();
        });

        // 4. Minimum Wage Variations
        Schema::create('minimum_wages', function (Blueprint $table) {
            $table->id();
            $table->string('state_code', 2);
            $table->string('skill_level'); // Unskilled, Semi-Skilled, Skilled, Highly Skilled
            $table->string('industry')->default('IT/Commercial');
            $table->string('zone')->default('Zone 1');
            $table->decimal('basic_wage', 10, 2);
            $table->decimal('vda', 10, 2)->default(0);
            $table->date('effective_from');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['pt_enabled', 'lwf_enabled', 'is_hq', 'state_code']);
        });
        Schema::dropIfExists('compliance_state_rules');
        Schema::dropIfExists('compliance_licences');
        Schema::dropIfExists('minimum_wages');
    }
};
