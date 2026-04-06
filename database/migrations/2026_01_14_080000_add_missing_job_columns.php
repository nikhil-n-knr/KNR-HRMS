<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            if (!Schema::hasColumn('job_postings', 'min_experience')) {
                $table->integer('min_experience')->nullable()->after('description');
            }
            if (!Schema::hasColumn('job_postings', 'max_experience')) {
                $table->integer('max_experience')->nullable()->after('min_experience');
            }
            if (!Schema::hasColumn('job_postings', 'salary_min')) {
                $table->decimal('salary_min', 12, 2)->nullable()->after('max_experience');
            }
            if (!Schema::hasColumn('job_postings', 'salary_max')) {
                $table->decimal('salary_max', 12, 2)->nullable()->after('salary_min');
            }
            if (!Schema::hasColumn('job_postings', 'salary_currency')) {
                $table->string('salary_currency', 3)->default('INR')->after('salary_max');
            }
            if (!Schema::hasColumn('job_postings', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_postings', function (Blueprint $table) {
            $table->dropColumn(['min_experience', 'max_experience', 'salary_min', 'salary_max', 'salary_currency']);
        });
    }
};
