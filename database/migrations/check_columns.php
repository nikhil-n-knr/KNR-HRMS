<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        $columns = Schema::getColumnListing('workflow_stages');
        echo json_encode($columns);
    }
};
