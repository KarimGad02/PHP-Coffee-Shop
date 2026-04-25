<?php
// Find the database file
$dbPath = __DIR__ . '/storage/cafeteria.sqlite';

if (!file_exists($dbPath)) {
    die("Database not found! Make sure you are in the right folder.\n");
}

// Connect directly to SQLite
$conn = new PDO('sqlite:' . $dbPath);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Generate a flawless hash right now
$perfectHash = password_hash('admin123', PASSWORD_DEFAULT);

// Forcefully update the admin user
$stmt = $conn->prepare("UPDATE users SET password = :hash WHERE email = 'admin@cafeteria.com'");
$stmt->execute(['hash' => $perfectHash]);

echo "Success! The admin password is now securely locked to 'admin123'.\n";