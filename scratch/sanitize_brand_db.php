<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();

    echo "Starting database sanitization and updates on DB: " . DB::getDatabaseName() . "\n\n";

    // 1. Setup lists of random names
    $randomNames = [
        'Amit Sharma', 'Priya Patel', 'Rahul Verma', 'Karan Malhotra', 
        'Siddharth Sen', 'Vikram Joshi', 'Ananya Iyer', 'Rohan Mehta', 
        'Sneha Rao', 'Suresh Kumar', 'Abhishek Roy', 'Pooja Nair', 
        'Deepak Gupta', 'Aishwarya Singh', 'Manish Pandey', 'Neha Gupta', 
        'Arjun Saxena', 'Sojana Reddy', 'Vijay Mallya', 'Preeti Deshmukh', 
        'Sunita Patil', 'Rajesh Khanna', 'Kunal Kapoor', 'Anil Deshmukh', 
        'Ravi Shankar', 'Sanjay Dutt', 'Madhavan Pillai', 'Ritu Karidhal', 
        'Kiran Mazumdar', 'Meera Nair', 'Shashi Tharoor', 'Anoop Menon',
        'John Doe', 'Jane Smith', 'Alice Johnson', 'Bob Miller', 'Charlie Davis'
    ];
    shuffle($randomNames);

    $mandatoryNames = ['Nikhil', 'Divya', 'Likith', 'Arrjuan'];
    $mandatoryAssigned = [];

    // Helper to generate fake details
    $getFakePhone = function() {
        return '9' . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);
    };
    $getFakeAadhaar = function() {
        return '9' . str_pad(rand(0, 99999999999), 11, '0', STR_PAD_LEFT);
    };
    $getFakePAN = function() {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $pan = '';
        for ($i = 0; $i < 3; $i++) {
            $pan .= $chars[rand(0, 25)];
        }
        $pan .= 'P'; // Individual
        $pan .= $chars[rand(0, 25)];
        $pan .= str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $pan .= $chars[rand(0, 25)];
        return $pan;
    };
    $getFakeUAN = function() {
        return '100' . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);
    };
    $getFakeESI = function() {
        return '31' . str_pad(rand(0, 999999999999999), 15, '0', STR_PAD_LEFT);
    };

    // 2. Fetch and process users and employees
    $users = DB::table('users')->orderBy('id')->get();
    $employees = DB::table('employees')->orderBy('id')->get();

    echo "Processing " . count($users) . " users...\n";

    // Keep track of user renaming map
    $userRenameMap = [];
    
    // Process Users
    foreach ($users as $index => $u) {
        $oldName = $u->name;
        $newName = '';
        
        // Let's decide on name
        if (stripos($oldName, 'Nikhil') !== false) {
            $newName = 'Nikhil';
            $mandatoryAssigned['Nikhil'] = true;
        } else if (stripos($oldName, 'Admin') !== false) {
            $newName = 'Nikhil';
            $mandatoryAssigned['Nikhil'] = true;
        } else {
            // Assign from mandatory if not yet assigned
            foreach ($mandatoryNames as $mName) {
                if (empty($mandatoryAssigned[$mName])) {
                    $newName = $mName;
                    $mandatoryAssigned[$mName] = true;
                    break;
                }
            }
            if (!$newName) {
                // Assign a random name
                $newName = array_pop($randomNames) ?: 'Generic User ' . $u->id;
            }
        }

        // Email domain @pisparrow.com
        $emailPrefix = strtolower(str_replace(' ', '.', $newName));
        $newEmail = $emailPrefix . '@pisparrow.com';
        
        // Ensure email uniqueness in this local migration
        $suffix = 1;
        while (DB::table('users')->where('email', $newEmail)->where('id', '!=', $u->id)->exists()) {
            $newEmail = $emailPrefix . $suffix . '@pisparrow.com';
            $suffix++;
        }

        // Update User
        DB::table('users')->where('id', $u->id)->update([
            'name' => $newName,
            'email' => $newEmail,
            'mobile' => $getFakePhone()
        ]);

        $userRenameMap[$u->id] = [
            'name' => $newName,
            'email' => $newEmail
        ];

        echo "User ID {$u->id}: '{$oldName}' -> '{$newName}' ({$newEmail})\n";
    }

    echo "\nProcessing " . count($employees) . " employees...\n";

    // Process Employees
    foreach ($employees as $e) {
        $oldName = $e->first_name . ' ' . $e->last_name;
        $newName = '';
        $newEmail = '';
        
        // If employee has a user_id that we renamed, match them!
        if ($e->user_id && isset($userRenameMap[$e->user_id])) {
            $newName = $userRenameMap[$e->user_id]['name'];
            $newEmail = $userRenameMap[$e->user_id]['email'];
        } else {
            // Otherwise, check if we need to assign a mandatory name
            foreach ($mandatoryNames as $mName) {
                if (empty($mandatoryAssigned[$mName])) {
                    $newName = $mName;
                    $mandatoryAssigned[$mName] = true;
                    break;
                }
            }
            if (!$newName) {
                $newName = array_pop($randomNames) ?: 'Generic Employee ' . $e->id;
            }
            $emailPrefix = strtolower(str_replace(' ', '.', $newName));
            $newEmail = $emailPrefix . '@pisparrow.com';
        }

        // Ensure email uniqueness
        $suffix = 1;
        while (DB::table('employees')->where('email', $newEmail)->where('id', '!=', $e->id)->exists()) {
            $newEmail = strstr($newEmail, '@', true) . $suffix . '@pisparrow.com';
            $suffix++;
        }

        // Split name
        $parts = explode(' ', $newName);
        $firstName = $parts[0];
        $lastName = isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : 'PSP';

        // Code update (ensure no KNR prefix)
        $newCode = 'PSP' . str_pad($e->id + 10000, 5, '0', STR_PAD_LEFT);

        DB::table('employees')->where('id', $e->id)->update([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $newEmail,
            'employee_code' => $newCode,
            'phone' => $getFakePhone(),
            'uan_number' => $getFakeUAN(),
            'esi_number' => $getFakeESI(),
            'pan_number' => $getFakePAN(),
            'aadhaar_number' => $getFakeAadhaar()
        ]);

        echo "Employee ID {$e->id}: '{$oldName}' -> '{$firstName} {$lastName}' ({$newCode}, {$newEmail})\n";
    }

    // 3. Tweak projects to be more generic
    echo "\nProcessing projects...\n";
    $genericProjects = [
        'LEAP - Admin' => ['name' => 'Core Portal Admin', 'code' => 'CPADMIN'],
        'LEAP - Mobile' => ['name' => 'Core Portal Mobile', 'code' => 'CPMOBILE'],
        'LEAP - Web' => ['name' => 'Core Portal Web', 'code' => 'CPWEB'],
        'LEAP - Admin Web' => ['name' => 'Admin Gateway Web', 'code' => 'AGWEB'],
        'LEAP - Teacher App' => ['name' => 'Client Hub Mobile', 'code' => 'CHMOBILE'],
        'ISaakshi - IGrantha' => ['name' => 'SaaS Storage Engine', 'code' => 'SSESTORAGE'],
        'HRMS - PiSparrow' => ['name' => 'Enterprise HR Suite', 'code' => 'EHRMS'],
        'CRM - KIM' => ['name' => 'Global Sales CRM', 'code' => 'GSALES'],
        'CRM - PiSparrow' => ['name' => 'Client Relations CRM', 'code' => 'CRCRM'],
        'CMS - NPS' => ['name' => 'E-Commerce CMS', 'code' => 'ECCMS'],
        'CMS - PiSparrow' => ['name' => 'Content Manager CMS', 'code' => 'CMC'],
        'LMS - PiSparrow' => ['name' => 'Knowledge Hub LMS', 'code' => 'KHLMS'],
        'LMS - Grant(EV)' => ['name' => 'Green Mobility LMS', 'code' => 'GMLMS'],
        'Ask' => ['name' => 'Help Desk Core', 'code' => 'HDC']
    ];

    $projects = DB::table('projects')->get();
    foreach ($projects as $p) {
        $newName = $p->name;
        $newCode = $p->code;

        // Check if exact match in generic mapping
        if (isset($genericProjects[$p->name])) {
            $newName = $genericProjects[$p->name]['name'];
            $newCode = $genericProjects[$p->name]['code'];
        } else {
            // Apply generic transformations
            $newName = str_ireplace('KNR', 'PiSparrow', $p->name);
            $newName = str_ireplace('LEAP', 'Core Portal', $newName);
            
            $newCode = str_ireplace('KNR', 'PSP', $p->code);
            $newCode = str_ireplace('LEAP', 'CP', $newCode);
        }

        // Clean Repository URL
        $newRepoUrl = $p->repository_url;
        if ($newRepoUrl) {
            $newRepoUrl = str_ireplace('KNR-INT', 'pisparrow-hub', $newRepoUrl);
        }

        DB::table('projects')->where('id', $p->id)->update([
            'name' => $newName,
            'code' => $newCode,
            'repository_url' => $newRepoUrl
        ]);

        echo "Project ID {$p->id}: '{$p->name}' -> '{$newName}' (Code: '{$p->code}' -> '{$newCode}')\n";
    }

    // 4. Clean Clients table
    echo "\nProcessing clients...\n";
    $clients = DB::table('clients')->get();
    $genericClients = ['Vertex Solutions', 'Nebula Services', 'Apex Innovations', 'Zenith Tech', 'Acme Corp'];
    foreach ($clients as $index => $c) {
        $newName = isset($genericClients[$index % count($genericClients)]) ? $genericClients[$index % count($genericClients)] : 'Generic Client ' . $c->id;
        if (count($clients) > count($genericClients)) {
            $newName .= ' ' . ($index + 1);
        }
        $newEmail = strtolower(str_replace(' ', '.', $newName)) . '@client.com';
        
        DB::table('clients')->where('id', $c->id)->update([
            'name' => $newName,
            'email' => $newEmail
        ]);
        echo "Client ID {$c->id}: '{$c->name}' -> '{$newName}' ({$newEmail})\n";
    }

    // 5. Clean up other app brand tables (app_modules, app_sub_modules, tenants)
    DB::table('tenants')->update([
        'name' => 'PiSparrow Tech Solution',
        'slug' => 'pisparrow'
    ]);

    DB::commit();
    echo "\nDatabase sanitization completed successfully!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
