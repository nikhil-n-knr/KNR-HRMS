<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();

    // 1. Update tenants table
    $updatedTenants = DB::table('tenants')
        ->where('name', 'like', '%KNR%')
        ->orWhere('slug', 'like', '%knr%')
        ->get();
    foreach ($updatedTenants as $t) {
        $newName = str_ireplace('KNR', 'PiSparrow', $t->name);
        $newSlug = str_ireplace('knr', 'pisparrow', $t->slug);
        DB::table('tenants')->where('id', $t->id)->update([
            'name' => $newName,
            'slug' => $newSlug
        ]);
        echo "Updated Tenant ID {$t->id}: '{$t->name}' -> '{$newName}'\n";
    }

    // 2. Update users table (emails and names)
    $users = DB::table('users')->get();
    foreach ($users as $u) {
        $newName = str_ireplace('KNR', 'PiSparrow', $u->name);
        $newEmail = str_ireplace('knrint.com', 'pisparrow.com', $u->email);
        if ($newName !== $u->name || $newEmail !== $u->email) {
            DB::table('users')->where('id', $u->id)->update([
                'name' => $newName,
                'email' => $newEmail
            ]);
            echo "Updated User ID {$u->id}: Name '{$u->name}' -> '{$newName}', Email '{$u->email}' -> '{$newEmail}'\n";
        }
    }

    // 3. Update employees table (emails and employee_code)
    $employees = DB::table('employees')->get();
    foreach ($employees as $e) {
        $newEmail = str_ireplace('knrint.com', 'pisparrow.com', $e->email);
        $newCode = str_ireplace('KNR', 'PSP', $e->employee_code);
        if ($newEmail !== $e->email || $newCode !== $e->employee_code) {
            DB::table('employees')->where('id', $e->id)->update([
                'email' => $newEmail,
                'employee_code' => $newCode
            ]);
            echo "Updated Employee ID {$e->id}: Code '{$e->employee_code}' -> '{$newCode}', Email '{$e->email}' -> '{$newEmail}'\n";
        }
    }

    // 4. Update app_modules table
    $modules = DB::table('app_modules')->get();
    foreach ($modules as $m) {
        $newName = str_ireplace('KNR', 'PiSparrow', $m->name);
        if ($newName !== $m->name) {
            DB::table('app_modules')->where('id', $m->id)->update(['name' => $newName]);
            echo "Updated App Module ID {$m->id}: '{$m->name}' -> '{$newName}'\n";
        }
    }

    // 5. Update app_sub_modules table
    $subModules = DB::table('app_sub_modules')->get();
    foreach ($subModules as $sm) {
        $newName = str_ireplace('KNR', 'PiSparrow', $sm->name);
        if ($newName !== $sm->name) {
            DB::table('app_sub_modules')->where('id', $sm->id)->update(['name' => $newName]);
            echo "Updated App Sub-Module ID {$sm->id}: '{$sm->name}' -> '{$newName}'\n";
        }
    }

    // 6. Update projects table
    $projects = DB::table('projects')->get();
    foreach ($projects as $p) {
        $newName = str_ireplace('KNR', 'PiSparrow', $p->name);
        if ($newName !== $p->name) {
            DB::table('projects')->where('id', $p->id)->update(['name' => $newName]);
            echo "Updated Project ID {$p->id}: '{$p->name}' -> '{$newName}'\n";
        }
    }

    // 7. Update clients table
    $clients = DB::table('clients')->get();
    foreach ($clients as $c) {
        $newName = str_ireplace('KNR', 'PiSparrow', $c->name);
        $newEmail = str_ireplace('knrint.com', 'pisparrow.com', $c->email);
        if ($newName !== $c->name || $newEmail !== $c->email) {
            DB::table('clients')->where('id', $c->id)->update([
                'name' => $newName,
                'email' => $newEmail
            ]);
            echo "Updated Client ID {$c->id}: Name '{$c->name}' -> '{$newName}', Email '{$c->email}' -> '{$newEmail}'\n";
        }
    }

    DB::commit();
    echo "\nDatabase branding update complete!\n";
} catch (\Exception $ex) {
    DB::rollBack();
    echo "Error updating database: " . $ex->getMessage() . "\n";
}
