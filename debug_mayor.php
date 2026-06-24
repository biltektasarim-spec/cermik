<?php
require_once 'config.php';
try {
    // 1. District image
    $stmt = $pdo->prepare("SELECT id, name, image FROM districts WHERE id = 3");
    $stmt->execute();
    $district = $stmt->fetch();
    
    // 2. Mayor settings
    $stmt = $pdo->prepare("SELECT name, value FROM settings WHERE district_id = 3 AND name LIKE 'mayor_%'");
    $stmt->execute();
    $settings = $stmt->fetchAll();
    
    echo "--- DISTRICT ---\n";
    echo json_encode($district, JSON_PRETTY_PRINT) . "\n\n";
    echo "--- SETTINGS ---\n";
    echo json_encode($settings, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
