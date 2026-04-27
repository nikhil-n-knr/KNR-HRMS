<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('location_nodes')->nullOnDelete();
            $table->enum('node_type', ['Branch', 'Building', 'Floor', 'Room', 'Zone', 'Locker', 'Cupboard', 'Shelf', 'Bin']);
            $table->string('name');
            $table->string('code')->nullable();
            $table->integer('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'parent_id']);
            $table->index(['node_type', 'is_active']);
        });

        Schema::create('location_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_node_id')->constrained('location_nodes')->cascadeOnDelete();
            $table->morphs('entity');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('released_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['location_node_id', 'released_at']);
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->unsignedBigInteger('current_location_node_id')->nullable()->after('location_id');
            $table->index('current_location_node_id');
        });

        Schema::table('physical_records', function (Blueprint $table) {
            $table->unsignedBigInteger('current_location_node_id')->nullable()->after('location_id');
            $table->index('current_location_node_id');
        });
    }

    public function down(): void
    {
        Schema::table('physical_records', function (Blueprint $table) {
            $table->dropIndex(['current_location_node_id']);
            $table->dropColumn('current_location_node_id');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex(['current_location_node_id']);
            $table->dropColumn('current_location_node_id');
        });

        Schema::dropIfExists('location_assignments');
        Schema::dropIfExists('location_nodes');
    }
};
