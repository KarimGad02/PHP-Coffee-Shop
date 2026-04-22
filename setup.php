<?php

// Plug-and-play setup script for local development.
// Usage:
//   php setup.php
//   php setup.php --fresh

require_once __DIR__ . '/app/config/Database.php';

if (php_sapi_name() !== 'cli') {
    echo "Run this script from CLI only.\n";
    exit(1);
}

$root = __DIR__;
$dbFile = $root . '/storage/cafeteria.sqlite';
$fresh = in_array('--fresh', $argv, true);

echo "== Cafeteria Users API Setup ==\n";

echo "1) Checking required PHP extensions...\n";
if (!extension_loaded('pdo_sqlite')) {
    echo "[ERROR] Missing extension: pdo_sqlite\n";
    echo "Enable it in php.ini, then rerun setup.\n";
    exit(1);
}
if (!extension_loaded('sqlite3')) {
    echo "[ERROR] Missing extension: sqlite3\n";
    echo "Enable it in php.ini, then rerun setup.\n";
    exit(1);
}
echo "[OK] SQLite extensions are enabled.\n";

echo "2) Preparing storage directory...\n";
$storageDir = $root . '/storage';
if (!is_dir($storageDir) && !mkdir($storageDir, 0777, true) && !is_dir($storageDir)) {
    echo "[ERROR] Could not create storage directory.\n";
    exit(1);
}
echo "[OK] Storage directory is ready.\n";

if ($fresh && file_exists($dbFile)) {
    echo "3) Fresh mode: removing existing DB...\n";
    if (!unlink($dbFile)) {
        echo "[ERROR] Could not delete existing database file.\n";
        exit(1);
    }
    echo "[OK] Old database removed.\n";
}

echo "4) Initializing database...\n";
$database = new Database();
$conn = $database->connect();
if (!$conn) {
    $error = method_exists($database, 'getLastError') ? $database->getLastError() : 'Unknown DB error';
    echo "[ERROR] " . $error . "\n";
    exit(1);
}

// Quick sanity check
$stmt = $conn->query("SELECT COUNT(*) AS c FROM users");
$count = (int)($stmt->fetch()['c'] ?? 0);

echo "[OK] Database is ready. Users seeded: " . $count . "\n";

echo "\nDone. Next steps:\n";
echo "- Start server: php -S localhost:8000 -t public\n";
echo "- Test endpoint: http://localhost:8000/auth/me\n";
echo "- Default admin: admin@cafeteria.com / admin123\n";
