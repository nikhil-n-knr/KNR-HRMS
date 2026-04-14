<?php
use App\Models\ClientUser;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "Client Users Count: " . ClientUser::count() . "\n";
foreach (ClientUser::limit(5)->get() as $cu) {
    echo "ID: {$cu->id}, Email: {$cu->email}, ClientID: {$cu->client_id}, Projects: " . $cu->projects->count() . "\n";
}

$user = ClientUser::first();
if ($user) {
    echo "\nTesting Dashboard queries for ID: {$user->id}\n";
    $projectIds = $user->projects->pluck('id');
    echo "Project IDs: " . implode(',', $projectIds->toArray()) . "\n";
}
