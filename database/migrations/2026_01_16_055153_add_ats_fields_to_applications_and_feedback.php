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
        Schema::table('job_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('job_applications', 'rejection_reason')) {
                $table->string('rejection_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('job_applications', 'rejection_notes')) {
                $table->text('rejection_notes')->nullable()->after('rejection_reason');
            }
            if (!Schema::hasColumn('job_applications', 'offer_details')) {
                $table->json('offer_details')->nullable()->after('rejection_notes');
            }
        });

        Schema::table('interview_feedback', function (Blueprint $table) {
            if (!Schema::hasColumn('interview_feedback', 'recommendation')) {
                $table->enum('recommendation', ['Strong Hire', 'Hire', 'No Hire', 'Strong No', 'On Hold'])->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['rejection_reason', 'rejection_notes', 'offer_details']);
        });

        Schema::table('interview_feedback', function (Blueprint $table) {
            $table->dropColumn(['recommendation']);
        });
    }
};
