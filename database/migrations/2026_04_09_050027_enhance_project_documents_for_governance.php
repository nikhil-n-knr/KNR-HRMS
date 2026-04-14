<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_documents', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->string('uploader_type')->nullable()->after('uploader_id');
        });

        // Set default uploader_type for existing documents
        DB::table('project_documents')->update(['uploader_type' => 'App\Models\User']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_documents', function (Blueprint $table) {
            $table->dropColumn(['description', 'uploader_type']);
        });
    }
};
