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
        Schema::create('crm_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('referral_code')->unique();
            $table->string('website')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(0); // percentage
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->timestamps();
        });

        Schema::create('crm_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('partner_id')->constrained('crm_partners')->onDelete('cascade');
            $table->string('referral_link_clicked_at')->nullable();
            $table->IPAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::table('crm_leads', function (Blueprint $table) {
            $table->foreignId('partner_id')->nullable()->constrained('crm_partners')->onDelete('set null')->after('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->dropForeign(['partner_id']);
            $table->dropColumn('partner_id');
        });
        Schema::dropIfExists('crm_referrals');
        Schema::dropIfExists('crm_partners');
    }
};
