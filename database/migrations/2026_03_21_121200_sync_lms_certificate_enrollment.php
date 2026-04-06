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
            if (!Schema::hasColumn('lms_certificates_v2', 'enrollment_id')) {
                $table->unsignedBigInteger('enrollment_id')->nullable()->after('user_id');
                $table->foreign('enrollment_id')->references('id')->on('lms_enrollments')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lms_certificates_v2', function (Blueprint $table) {
            $table->dropForeign(['enrollment_id']);
            $table->dropColumn(['enrollment_id']);
        });
    }
};
