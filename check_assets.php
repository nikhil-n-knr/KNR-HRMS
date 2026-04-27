<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$assets = \App\Models\Asset::all();
echo "Total Assets: " . $assets->count() . "\n";
foreach ($assets as $asset) {
    echo "ID: {$asset->id}, Name: {$asset->name}, Status: {$asset->status}, Lifecycle: {$asset->lifecycle_state}\n";
}
