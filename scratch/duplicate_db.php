<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // 1. Create database if not exists
    DB::statement("CREATE DATABASE IF NOT EXISTS pisparrow_hrms");
    echo "Created database pisparrow_hrms (or it already existed).\n";
    
    // 2. Get list of tables from knr_hrms
    $tables = DB::select("SHOW TABLES");
    
    foreach ($tables as $table) {
        $tableArray = (array)$table;
        $tableName = reset($tableArray);
        
        echo "Duplicating table: $tableName ... ";
        
        // Drop table in target database if exists to ensure clean copy
        DB::statement("DROP TABLE IF EXISTS pisparrow_hrms.`$tableName`");
        
        // Copy structure
        DB::statement("CREATE TABLE pisparrow_hrms.`$tableName` LIKE knr_hrms.`$tableName`");
        
        // Copy data
        DB::statement("INSERT INTO pisparrow_hrms.`$tableName` SELECT * FROM knr_hrms.`$tableName`");
        
        echo "Done.\n";
    }
    
    echo "Database duplication completed successfully!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
