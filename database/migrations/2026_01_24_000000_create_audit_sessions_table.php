<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('auditor_id')->constrained('users')->cascadeOnDelete(); // User who performed audit
            $table->json('stats')->nullable(); // { total_assets, scanned_count, missing_count, accuracy_rate }
            $table->text('notes')->nullable();
            $table->string('status')->default('Completed');
            $table->timestamps();
        });

        Schema::create('audit_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('status'); // 'Found', 'Missing', 'Unexpected'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_items');
        Schema::dropIfExists('audit_sessions');
    }
};
