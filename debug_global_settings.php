<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SELECT name, value FROM settings WHERE district_id = 0 AND name LIKE '%mayor%'");
    $settings = $stmt->fetchAll();
    echo json_encode($settings, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
