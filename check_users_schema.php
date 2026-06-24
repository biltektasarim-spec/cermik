<?php
require_once 'config.php';
try {
    // Check users table columns
    $res = $pdo->query("DESCRIBE users");
    $cols = $res->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(", ", $cols) . "\n";
    
    // Check if city/district exist, if not add them
    if (!in_array('city', $cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN city VARCHAR(100) DEFAULT 'Diyarbakır'");
        echo "Added 'city' column.\n";
    }
    if (!in_array('district', $cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN district VARCHAR(100) DEFAULT NULL");
        echo "Added 'district' column.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
