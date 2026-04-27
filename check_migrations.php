<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ran = DB::table('migrations')->pluck('migration');
foreach ($ran as $m) {
    echo "$m\n";
}
