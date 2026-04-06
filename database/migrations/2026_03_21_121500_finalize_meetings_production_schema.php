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
        Schema::table('crm_meetings', function (Blueprint $table) {
            if (!Schema::hasColumn('crm_meetings', 'employee_id')) {
                $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('cascade');
            }
            if (!Schema::hasColumn('crm_meetings', 'client_id')) {
                $table->foreignId('client_id')->nullable()->constrained('crm_clients')->onDelete('cascade');
            }
            if (!Schema::hasColumn('crm_meetings', 'provider')) {
                $table->enum('provider', ['zoom', 'meet', 'teams'])->nullable()->after('type');
            }
            if (!Schema::hasColumn('crm_meetings', 'link')) {
                $table->string('link')->nullable()->after('provider');
            }
        });

        Schema::create('meeting_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('crm_meetings')->onDelete('cascade');
            $table->morphs('attendee'); // Employee, Contact, Lead
            $table->string('email')->nullable();
            $table->enum('status', ['pending', 'accepted', 'declined', 'tentative'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_attendees');
        Schema::table('crm_meetings', function (Blueprint $table) {
            $table->dropColumn(['employee_id', 'client_id', 'provider', 'link']);
        });
    }
};
