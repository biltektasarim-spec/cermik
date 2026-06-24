<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SELECT id, name, is_active FROM districts");
    $districts = $stmt->fetchAll();
    echo json_encode($districts, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
