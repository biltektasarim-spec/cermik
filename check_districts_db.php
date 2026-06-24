<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SELECT id, name, is_active FROM districts");
    $districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Total Districts: " . count($districts) . "\n";
    foreach ($districts as $d) {
        echo "ID: {$d['id']}, Name: {$d['name']}, Active: {$d['is_active']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
