<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SELECT id, name, image FROM categories");
    $categories = $stmt->fetchAll();
    echo json_encode($categories, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
