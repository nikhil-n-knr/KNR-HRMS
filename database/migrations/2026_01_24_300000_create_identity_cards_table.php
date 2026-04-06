<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('identity_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // For Employees
            // We store vendor_id implicitly via user or metadata if needed, but for "Vendor Staff" typically they might not be users yet. 
            // The prompt says "You add a new guard in the Vendor Module". Assuming we link to a 'VendorStaff' or store generic details.
            // For simplicity in this iteration, we'll store basic details directly if no user_id, or link to a generic 'vendor_staff' json.
            
            $table->string('card_number')->unique(); // ID-2026-001
            $table->string('type')->default('Employee'); // Employee, Vendor, Visitor
            $table->json('details')->nullable(); // { name, role, vendor_name, blood_group, authorized_areas }
            $table->string('qr_code')->unique(); // UUID for verification
            $table->date('issue_date');
            $table->date('valid_until')->nullable(); 
            $table->string('status')->default('Active'); // Active, Lost, Expired, Revoked, Printed (but not issued)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('identity_cards');
    }
};
