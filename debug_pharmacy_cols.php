<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM pharmacies");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(", ", $columns);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
