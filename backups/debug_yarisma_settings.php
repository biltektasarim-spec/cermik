<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=yahya_db;charset=utf8', 'root', '21212121');
    $stmt = $pdo->query("DESC settings");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(", ", $cols) . "\n";
    
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Google Client ID: " . ($settings['google_client_id'] ?? 'NOT FOUND') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
