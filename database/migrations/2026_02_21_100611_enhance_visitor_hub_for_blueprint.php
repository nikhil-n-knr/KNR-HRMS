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
        Schema::table('visitors', function (Blueprint $table) {
            if (!Schema::hasColumn('visitors', 'id_verified')) {
                $table->boolean('id_verified')->default(false);
            }
            if (!Schema::hasColumn('visitors', 'ocr_data')) {
                $table->json('ocr_data')->nullable(); // Extracted ID data
            }
        });

        Schema::table('visitor_passes', function (Blueprint $table) {
            if (!Schema::hasColumn('visitor_passes', 'expected_duration')) {
                $table->integer('expected_duration')->nullable(); // In Minutes
            }
            if (!Schema::hasColumn('visitor_passes', 'actual_duration')) {
                $table->integer('actual_duration')->nullable(); // In Minutes
            }
            if (!Schema::hasColumn('visitor_passes', 'host_response_time')) {
                $table->integer('host_response_time')->nullable(); // Time in seconds between check-in and host acknowledgment
            }
            if (!Schema::hasColumn('visitor_passes', 'nda_status')) {
                $table->string('nda_status')->default('Pending'); // Signed, Skipped, Pending
            }
            if (!Schema::hasColumn('visitor_passes', 'scanned_id_data')) {
                $table->json('scanned_id_data')->nullable();
            }
            if (!Schema::hasColumn('visitor_passes', 'badge_printed_at')) {
                $table->timestamp('badge_printed_at')->nullable();
            }
        });

        // Denied Access Ledger
        if (!Schema::hasTable('visitor_access_logs')) {
            Schema::create('visitor_access_logs', function (Blueprint $table) {
                $table->id();
                $table->string('status'); // Failed Scan, Blacklisted Match, Overstay Alert, Host Rejected
                $table->string('visitor_name')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('reason')->nullable();
                $table->foreignId('visitor_id')->nullable()->constrained('visitors')->nullOnDelete();
                $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_access_logs');
        
        Schema::table('visitor_passes', function (Blueprint $table) {
            $table->dropColumn([
                'expected_duration', 
                'actual_duration', 
                'host_response_time', 
                'nda_status', 
                'scanned_id_data',
                'badge_printed_at'
            ]);
        });
        
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropColumn(['id_verified', 'ocr_data']);
        });
    }
};
