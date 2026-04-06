<?php
/**
 * Fix all tables with broken `id` columns (missing PRIMARY KEY + AUTO_INCREMENT).
 * This happens when a MySQL dump is imported without the AUTO_INCREMENT attribute.
 */

$host     = '127.0.0.1';
$port     = '3306';
$dbname   = 'knr_hrms';
$user     = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "✅ Connected to MySQL\n\n";
} catch (Exception $e) {
    die("❌ Connection failed: " . $e->getMessage() . "\n");
}

// Get all tables in the database
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "Found " . count($tables) . " tables.\n\n";

$fixed   = [];
$skipped = [];
$errors  = [];

foreach ($tables as $table) {
    // Get column info for `id`
    $cols = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
    $idCol = null;
    foreach ($cols as $col) {
        if ($col['Field'] === 'id') {
            $idCol = $col;
            break;
        }
    }

    if (!$idCol) {
        $skipped[] = "$table (no `id` column)";
        continue;
    }

    $hasKey   = !empty($idCol['Key']);   // 'PRI' if primary key
    $hasAuto  = strpos($idCol['Extra'], 'auto_increment') !== false;

    if ($hasKey && $hasAuto) {
        $skipped[] = "$table (already OK)";
        continue;
    }

    echo "🔧 Fixing: $table  [Key={$idCol['Key']}, Extra={$idCol['Extra']}]\n";

    try {
        // Step 1: ensure NOT NULL
        $pdo->exec("ALTER TABLE `$table` MODIFY `id` BIGINT UNSIGNED NOT NULL");

        // Step 2: add primary key if missing
        if (empty($idCol['Key'])) {
            $pdo->exec("ALTER TABLE `$table` ADD PRIMARY KEY (`id`)");
            echo "   → Added PRIMARY KEY\n";
        }

        // Step 3: set AUTO_INCREMENT
        if (!$hasAuto) {
            $pdo->exec("ALTER TABLE `$table` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
            echo "   → Set AUTO_INCREMENT\n";
        }

        $fixed[] = $table;
        echo "   ✅ Done\n";
    } catch (Exception $e) {
        $errors[] = "$table: " . $e->getMessage();
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n========= SUMMARY =========\n";
echo "✅ Fixed  : " . count($fixed)   . " tables: " . implode(', ', $fixed)   . "\n";
echo "⏭️  Skipped: " . count($skipped) . " tables\n";
echo "❌ Errors : " . count($errors)  . " tables: " . implode('; ', $errors)  . "\n";
