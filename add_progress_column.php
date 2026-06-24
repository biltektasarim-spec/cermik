<?php
require_once 'config.php';

try {
    $pdo->exec("ALTER TABLE services ADD COLUMN progress INT DEFAULT 0");
    echo "Column 'progress' added successfully to 'services' table.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
