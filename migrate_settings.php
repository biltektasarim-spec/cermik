<?php
require_once 'config.php';
try {
    $pdo->exec("ALTER TABLE settings ADD COLUMN district_id INT DEFAULT 0 AFTER id");
    $pdo->exec("ALTER TABLE settings DROP INDEX name");
    $pdo->exec("ALTER TABLE settings ADD UNIQUE INDEX name_district (name, district_id)");
    echo "Settings table updated successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
