<?php
require_once 'config.php';
try {
    // Check if is_active already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM places LIKE 'is_active'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE places ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER district_id");
        echo "Success: is_active column added to places table.\n";
    } else {
        echo "Notice: is_active column already exists.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
