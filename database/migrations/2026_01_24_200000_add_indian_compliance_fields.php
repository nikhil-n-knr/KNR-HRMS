<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Vendors: Compliance Fields
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('gstin', 15)->nullable()->after('email');
            $table->string('pan', 10)->nullable()->after('gstin');
            $table->boolean('msme_reg')->default(false)->after('pan');
            $table->decimal('tds_rate', 5, 2)->default(0.00)->after('msme_reg'); // e.g. 10.00
            $table->json('bank_details')->nullable()->after('tds_rate'); // {acc, ifsc}
            $table->string('category')->nullable()->after('bank_details'); // IT, Stationery
        });

        // 2. Assets: Procurement Link & Software Seats
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('category_id')->constrained()->onDelete('set null');
            $table->integer('max_seats')->nullable()->after('meta'); // For Licenses
            $table->integer('seats_used')->default(0)->after('max_seats');
            $table->boolean('is_returnable')->default(true)->after('seats_used');
        });

        // 3. Purchase Requests: Tax & Invoice Info
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('tenant_id')->constrained()->onDelete('set null');
            $table->string('invoice_no')->nullable()->after('status');
            $table->decimal('gst_amount', 10, 2)->default(0)->after('total_cost');
            $table->boolean('tds_deducted')->default(false)->after('gst_amount');
            $table->string('po_number')->nullable()->after('id'); // Auto-gen PO
        });
    }

    public function down()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['gstin', 'pan', 'msme_reg', 'tds_rate', 'bank_details', 'category']);
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['vendor_id', 'max_seats', 'seats_used', 'is_returnable']);
        });

        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['vendor_id', 'invoice_no', 'gst_amount', 'tds_deducted', 'po_number']);
        });
    }
};
