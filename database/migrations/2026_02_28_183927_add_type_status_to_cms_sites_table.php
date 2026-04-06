<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_sites', function (Blueprint $table) {
            $table->enum('type', ['static', 'ecommerce'])->default('static')->after('name');
            $table->enum('status', ['draft', 'live', 'maintenance'])->default('draft')->after('is_live');
            $table->json('settings')->nullable()->after('global_settings')
                ->comment('PWA config, payment gateways, SEO globals, social links');
            $table->string('currency', 3)->default('INR')->after('settings');
            $table->string('razorpay_key_id')->nullable()->after('currency');
            $table->string('razorpay_key_secret')->nullable()->after('razorpay_key_id');
        });
    }

    public function down(): void
    {
        Schema::table('cms_sites', function (Blueprint $table) {
            $table->dropColumn(['type', 'status', 'settings', 'currency', 'razorpay_key_id', 'razorpay_key_secret']);
        });
    }
};
