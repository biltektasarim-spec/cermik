<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM districts");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(", ", $columns) . "\n\n";
    
    $stmt = $pdo->query("SELECT * FROM districts WHERE id = 3");
    $district = $stmt->fetch();
    echo json_encode($district, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
