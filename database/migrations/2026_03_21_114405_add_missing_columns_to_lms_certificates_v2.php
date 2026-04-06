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
        Schema::table('lms_certificates_v2', function (Blueprint $table) {
            if (!Schema::hasColumn('lms_certificates_v2', 'rule_id')) {
                $table->unsignedBigInteger('rule_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('lms_certificates_v2', 'template_id')) {
                $table->unsignedBigInteger('template_id')->nullable()->after('rule_id');
            }
            if (!Schema::hasColumn('lms_certificates_v2', 'program_id')) {
                $table->unsignedBigInteger('program_id')->nullable()->after('course_id');
            }
            if (!Schema::hasColumn('lms_certificates_v2', 'module_id')) {
                $table->unsignedBigInteger('module_id')->nullable()->after('program_id');
            }
            if (!Schema::hasColumn('lms_certificates_v2', 'type')) {
                $table->string('type')->default('course')->after('module_id');
            }
            if (!Schema::hasColumn('lms_certificates_v2', 'qr_data')) {
                $table->text('qr_data')->nullable()->after('unique_code');
            }
            if (!Schema::hasColumn('lms_certificates_v2', 'pdf_path')) {
                $table->string('pdf_path')->nullable()->after('qr_data');
            }
            if (!Schema::hasColumn('lms_certificates_v2', 'metadata')) {
                $table->json('metadata')->nullable()->after('pdf_path');
            }
            if (!Schema::hasColumn('lms_certificates_v2', 'is_revoked')) {
                $table->boolean('is_revoked')->default(false)->after('issued_at');
            }
            if (!Schema::hasColumn('lms_certificates_v2', 'revoked_at')) {
                $table->timestamp('revoked_at')->nullable()->after('is_revoked');
            }
            if (!Schema::hasColumn('lms_certificates_v2', 'revocation_reason')) {
                $table->string('revocation_reason')->nullable()->after('revoked_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lms_certificates_v2', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn(['program_id', 'is_revoked']);
        });
    }
};
