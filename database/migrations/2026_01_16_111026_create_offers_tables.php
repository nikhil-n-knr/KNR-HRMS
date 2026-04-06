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
        // 1. Create Document Requests Table
        Schema::create('document_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_letter_id')->constrained('offer_letters')->cascadeOnDelete();
            
            $table->string('name'); // e.g. "Passport Copy", "Degree Certificate"
            $table->boolean('is_mandatory')->default(false);
            $table->string('status')->default('Pending'); // Pending, Submitted, Verified, Rejected
            $table->text('rejection_reason')->nullable();
            
            $table->string('file_path')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();
            
            $table->timestamps();
        });

        // 2. Add columns to offer_templates
        Schema::table('offer_templates', function (Blueprint $table) {
            $table->string('header_image')->nullable()->after('content');
            $table->string('footer_image')->nullable()->after('header_image');
        });

        // 3. Add columns to offer_letters
        Schema::table('offer_letters', function (Blueprint $table) {
            $table->foreignId('offer_template_id')->nullable()->after('job_application_id')->constrained('offer_templates')->nullOnDelete();
            $table->string('designation')->nullable()->after('joining_date'); // Explicit override or confirmed role
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_requests');

        Schema::table('offer_templates', function (Blueprint $table) {
            $table->dropColumn(['header_image', 'footer_image']);
        });

        Schema::table('offer_letters', function (Blueprint $table) {
            $table->dropForeign(['offer_template_id']);
            $table->dropColumn(['offer_template_id', 'designation']);
        });
    }
};
