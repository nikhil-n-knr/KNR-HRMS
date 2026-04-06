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
        Schema::table('offer_letters', function (Blueprint $table) {
            if (!Schema::hasColumn('offer_letters', 'designation')) {
                $table->string('designation')->nullable()->after('job_application_id');
            }
            if (!Schema::hasColumn('offer_letters', 'content')) {
                $table->longText('content')->nullable()->after('manual_path');
            }
            if (!Schema::hasColumn('offer_letters', 'salary_structure_id')) {
                $table->foreignId('salary_structure_id')->nullable()->constrained('salary_structures')->nullOnDelete()->after('job_application_id');
            }
            if (!Schema::hasColumn('offer_letters', 'document_template_id')) {
                $table->foreignId('document_template_id')->nullable()->constrained('document_templates')->nullOnDelete()->after('salary_structure_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_letters', function (Blueprint $table) {
            $table->dropColumn(['designation', 'content', 'salary_structure_id', 'document_template_id']);
        });
    }
};
