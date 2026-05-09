<?php
/**
 * One-time database repair script.
 * DELETE THIS FILE after running it.
 */

$dbPath = __DIR__ . '/../app/data/database.sqlite';

if (!file_exists($dbPath)) {
    echo "No database file found — nothing to fix.";
    exit;
}

// Try to repair in-place first: drop stale _old tables and fix schema
try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = OFF');

    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    echo "<pre>Tables found: " . implode(', ', $tables) . "\n\n";

    // Show full schema for any _old tables
    foreach ($tables as $t) {
        if (strpos($t, '_old') !== false) {
            $sql = $pdo->query("SELECT sql FROM sqlite_master WHERE name='$t'")->fetchColumn();
            echo "Schema of $t:\n$sql\n\n";
        }
    }

    // Show any sqlite_master entries that reference _old tables
    $refs = $pdo->query("SELECT type, name, sql FROM sqlite_master WHERE sql LIKE '%_old%'")->fetchAll(PDO::FETCH_ASSOC);
    if ($refs) {
        echo "Entries in sqlite_master referencing _old tables:\n";
        foreach ($refs as $r) {
            echo "  [{$r['type']}] {$r['name']}:\n  {$r['sql']}\n\n";
        }
    } else {
        echo "No sqlite_master entries reference _old tables.\n\n";
    }

    // Drop _old tables
    foreach ($tables as $t) {
        if (strpos($t, '_old') !== false) {
            $pdo->exec("DROP TABLE IF EXISTS \"$t\"");
            echo "Dropped table: $t\n";
        }
    }

    $pdo->exec('PRAGMA foreign_keys = ON');
    echo "\nIn-place repair done. Try reloading the app.\n";
    echo "If the error persists, reload THIS page with ?reset=1 to wipe and recreate the database.\n";

} catch (Exception $e) {
    echo "In-place repair failed: " . $e->getMessage() . "\n";
    echo "Reload with ?reset=1 to wipe and recreate the database.\n";
}

// Nuclear option: wipe and recreate
if (isset($_GET['reset']) && $_GET['reset'] === '1') {
    if (unlink($dbPath)) {
        // Also remove WAL and SHM sidecar files if present
        @unlink($dbPath . '-wal');
        @unlink($dbPath . '-shm');
        echo "\nDatabase file deleted. The app will recreate it from init.sql on next load.\n";
        echo "Go to <a href='/matrixcoders/public/'>the app</a> to trigger the rebuild.\n";
    } else {
        echo "\nFailed to delete database file. Check file permissions.\n";
    }
}

echo "</pre>";
echo "<p><strong>Remember to delete this file after use!</strong></p>";
