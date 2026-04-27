<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "asset_categories.deleted_at: " . (Schema::hasColumn('asset_categories', 'deleted_at') ? 'YES' : 'NO') . "\n";
echo "assets.deleted_at: " . (Schema::hasColumn('assets', 'deleted_at') ? 'YES' : 'NO') . "\n";
echo "location_nodes.deleted_at: " . (Schema::hasColumn('location_nodes', 'deleted_at') ? 'YES' : 'NO') . "\n";
