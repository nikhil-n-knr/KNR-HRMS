<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;

$user = User::where('email', 'admin@test.com')->first();
if (!$user) {
    echo "Admin user not found!\n";
    exit;
}

echo "User: " . $user->name . " (ID: " . $user->id . ")\n";
echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
echo "Permissions count: " . $user->getAllPermissions()->count() . "\n";
echo "Tenant: " . ($user->tenant->name ?? 'None') . "\n";
echo "Employee Profile: " . ($user->employee ? 'Exists' : 'Missing') . "\n";

$roles = Role::all();
echo "\nAvailable Roles in system:\n";
foreach ($roles as $role) {
    echo "- " . $role->name . " (" . $role->guard_name . ")\n";
}
