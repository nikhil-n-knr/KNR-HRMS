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
        Schema::create('physical_document_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Cabinet A
            $table->enum('type', ['Cabinet', 'Safe', 'Offsite_Storage', 'Rack']);
            $table->integer('access_level')->default(1); // Security Level
            $table->timestamps();
        });

        Schema::create('physical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users'); // Owner of the document
            $table->string('document_type'); // Original Degree, Passport
            $table->foreignId('location_id')->nullable()->constrained('physical_document_locations');
            $table->string('container_ref')->nullable(); // Folder 24, Box 1
            $table->enum('status', ['In_Custody', 'With_Employee', 'Missing', 'Returned'])->default('In_Custody');
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->timestamp('received_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_records');
        Schema::dropIfExists('physical_document_locations');
    }
};
