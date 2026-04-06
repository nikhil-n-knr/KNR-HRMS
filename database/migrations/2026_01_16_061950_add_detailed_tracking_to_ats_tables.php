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
            $table->unsignedTinyInteger('screening_rating')->nullable()->comment('1-5 rating for screening stage');
            $table->text('screening_feedback')->nullable();
        });

        Schema::table('interviews', function (Blueprint $table) {
            $table->enum('result', ['Pending', 'Passed', 'Failed', 'Hold'])->default('Pending')->after('status');
            $table->string('cancellation_reason')->nullable()->after('result');
            $table->string('reschedule_reason')->nullable()->after('cancellation_reason');
            $table->boolean('no_show')->default(false)->after('reschedule_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['screening_rating', 'screening_feedback']);
        });

        Schema::table('interviews', function (Blueprint $table) {
            $table->dropColumn(['result', 'cancellation_reason', 'reschedule_reason', 'no_show']);
        });
    }
};
