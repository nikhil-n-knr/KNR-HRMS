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
        Schema::create('crm_contact_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained('crm_contacts')->onDelete('cascade');
            $table->foreignId('related_contact_id')->constrained('crm_contacts')->onDelete('cascade');
            $table->string('relation_type'); // alumni, past_colleague, mentor, etc.
            $table->integer('strength')->default(1); // 1-10
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('crm_contacts', function (Blueprint $table) {
            $table->integer('influence_score')->default(50)->after('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_contacts', function (Blueprint $table) {
            $table->dropColumn('influence_score');
        });
        Schema::dropIfExists('crm_contact_relationships');
    }
};
