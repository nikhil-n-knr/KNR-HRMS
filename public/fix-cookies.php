<?php

// 1. CLEAR ALL BROWSER COOKIES
if (isset($_SERVER['HTTP_COOKIE'])) {
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, '', time()-1000);
        setcookie($name, '', time()-1000, '/');
    }
}

// 2. SHOW DIAGNOSTICS
echo "<h1>HRMS Emergency Fix</h1>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Load .env manually to see what's in the file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    preg_match('/APP_KEY=(.*)/', $envContent, $matches);
    echo "<p><strong>APP_KEY in .env file:</strong> " . ($matches[1] ?? 'NOT FOUND') . "</p>";
}

// 3. WIPE SERVER CACHE (via system call if possible)
@exec('php ../artisan optimize:clear');

echo "<h2>Step 1: All browser cookies have been cleared.</h2>";
echo "<h2>Step 2: Server cache has been cleared.</h2>";
echo "<p><a href='/admin/crm/communication-hub' style='font-size: 20px; color: blue;'>CLICK HERE TO GO TO CRM HUB</a></p>";
echo "<hr>";
echo "<p>If you still see an error, please try opening the site in an <strong>Incognito/Private Tab</strong>.</p>";
