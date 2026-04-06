<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lms_certificates')) {
            Schema::create('lms_certificates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees');
                $table->foreignId('course_id')->constrained('lms_courses');
                $table->foreignId('attempt_id')->constrained('lms_attempts');
                $table->string('certificate_code', 50)->unique(); // Unique verification code
                $table->date('issued_on');
                $table->date('expires_on')->nullable();
                $table->string('pdf_path');
                $table->string('qr_code_path'); // QR for verification
                $table->integer('downloads_count')->default(0);
                $table->timestamp('last_downloaded_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_certificates');
    }
};
