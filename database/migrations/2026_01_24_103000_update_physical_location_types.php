<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Using raw statement to avoid Doctrine dependency issues for Enum modification
        DB::statement("ALTER TABLE physical_document_locations MODIFY COLUMN type ENUM('Room', 'Cabinet', 'Safe', 'Offsite_Storage', 'Rack', 'Shelf', 'Bin') NOT NULL DEFAULT 'Cabinet'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE physical_document_locations MODIFY COLUMN type ENUM('Cabinet', 'Safe', 'Offsite_Storage', 'Rack') NOT NULL");
    }
};
