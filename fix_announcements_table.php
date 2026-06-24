<?php
require_once 'config.php';

try {
    echo "Checking announcements table columns...\n";
    $stmt = $pdo->query("DESCRIBE announcements");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('is_active', $columns)) {
        echo "Adding is_active column...\n";
        $pdo->exec("ALTER TABLE announcements ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER image");
    }

    if (!in_array('is_global', $columns)) {
        echo "Adding is_global column...\n";
        $pdo->exec("ALTER TABLE announcements ADD COLUMN is_global TINYINT(1) DEFAULT 0 AFTER is_active");
    }

    echo "Migration completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
