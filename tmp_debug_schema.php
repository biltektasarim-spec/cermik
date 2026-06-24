<?php
require 'config.php';

echo "--- SETTINGS ---\n";
try {
    $stmt = $pdo->query("SELECT * FROM settings WHERE name LIKE 'sms_%'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['name'] . ": " . $row['value'] . "\n";
    }
} catch (Exception $e) {
    echo "Error settings: " . $e->getMessage() . "\n";
}

echo "\n--- USERS SCHEMA ---\n";
try {
    $stmt = $pdo->query("DESCRIBE users");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error users: " . $e->getMessage() . "\n";
}
