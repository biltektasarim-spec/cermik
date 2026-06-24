<?php
require_once 'config.php';
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER is_active");
    echo "Success: Added is_verified column.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
