<?php
// Fix notifications table - has id=0 duplicates, no PRIMARY KEY, no AUTO_INCREMENT

$host = '127.0.0.1'; $port = '3306';
$db   = 'knr_hrms';  $user = 'root'; $pass = '';

$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

echo "Step 1: Check notifications table...\n";
$rows = $pdo->query("SELECT COUNT(*) as cnt FROM notifications")->fetch();
echo "  Total rows: {$rows['cnt']}\n";

$zeros = $pdo->query("SELECT COUNT(*) as cnt FROM notifications WHERE id = 0")->fetch();
echo "  Zero-id rows: {$zeros['cnt']}\n";

if ($zeros['cnt'] > 0) {
    echo "\nStep 2: Reassign zero-id rows with unique sequential IDs...\n";
    // Get the current max id
    $maxRow = $pdo->query("SELECT MAX(id) as max_id FROM notifications WHERE id != 0")->fetch();
    $nextId = ($maxRow['max_id'] ?? 0) + 1;

    // Update each zero-id row one at a time
    $zeroRows = $pdo->query("SELECT rowid FROM (SELECT @rownum := @rownum + 1 AS rowid FROM notifications, (SELECT @rownum := 0) r WHERE id = 0) t")->fetchAll();
    
    // Simpler: use a session variable approach
    $pdo->exec("SET @new_id = (SELECT COALESCE(MAX(id), 0) FROM notifications WHERE id > 0)");
    $pdo->exec("UPDATE notifications SET id = (@new_id := @new_id + 1) WHERE id = 0");
    
    $zeros2 = $pdo->query("SELECT COUNT(*) as cnt FROM notifications WHERE id = 0")->fetch();
    echo "  Zero-id rows after fix: {$zeros2['cnt']}\n";
}

echo "\nStep 3: Add PRIMARY KEY to notifications...\n";
try {
    $pdo->exec("ALTER TABLE notifications MODIFY id BIGINT UNSIGNED NOT NULL");
    $pdo->exec("ALTER TABLE notifications ADD PRIMARY KEY (id)");
    echo "  ✅ PRIMARY KEY added\n";
} catch (Exception $e) {
    echo "  ⚠️  " . $e->getMessage() . "\n";
}

echo "\nStep 4: Set AUTO_INCREMENT...\n";
try {
    $pdo->exec("ALTER TABLE notifications MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
    echo "  ✅ AUTO_INCREMENT set\n";
} catch (Exception $e) {
    echo "  ❌ " . $e->getMessage() . "\n";
}

echo "\nStep 5: Verify...\n";
$desc = $pdo->query("DESCRIBE notifications")->fetchAll(PDO::FETCH_ASSOC);
foreach ($desc as $col) {
    if ($col['Field'] === 'id') {
        echo "  id => Key={$col['Key']}, Extra={$col['Extra']}\n";
        $ok = ($col['Key'] === 'PRI' && strpos($col['Extra'], 'auto_increment') !== false);
        echo $ok ? "  ✅ notifications.id is fully fixed!\n" : "  ❌ Still broken\n";
    }
}

echo "\nDone! Now run: php artisan migrate to sync migration table.\n";
