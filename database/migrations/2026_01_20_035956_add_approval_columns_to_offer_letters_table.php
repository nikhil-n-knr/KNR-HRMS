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
            $table->string('approval_status')->default('Pending')->after('status'); // Pending, Approved, Rejected, Released
            $table->json('approvers')->nullable()->after('approval_status'); // IDs of approvers
            $table->string('approval_token')->nullable()->after('approvers'); // For internal approval link
            $table->json('approval_data')->nullable()->after('approval_token'); // Snapshot of critical data at approval time
            $table->string('otp')->nullable()->after('approval_data'); // Candidate Portal OTP
            $table->timestamp('otp_expires_at')->nullable()->after('otp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_letters', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'approvers', 'approval_token', 'approval_data', 'otp', 'otp_expires_at']);
        });
    }
};
