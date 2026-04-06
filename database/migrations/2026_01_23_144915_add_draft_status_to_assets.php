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
        // Add 'Draft' to the enum list
        DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('Available', 'Assigned', 'In_Service', 'Scrapped', 'Lost', 'Draft') DEFAULT 'Available'");
    }

    public function down(): void
    {
        // Revert to original enum (Optional, but good practice)
        DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('Available', 'Assigned', 'In_Service', 'Scrapped', 'Lost') DEFAULT 'Available'");
    }
};
