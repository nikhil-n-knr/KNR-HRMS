<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['assets', 'asset_categories', 'location_nodes'];
foreach ($tables as $table) {
    echo "$table exists: " . (Schema::hasTable($table) ? "YES" : "NO") . "\n";
    if (Schema::hasTable($table)) {
        echo "  deleted_at exists: " . (Schema::hasColumn($table, 'deleted_at') ? "YES" : "NO") . "\n";
    }
}
