<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=yahya_db;charset=utf8', 'root', '21212121');
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'google_client_id' LIMIT 1");
    $stmt->execute();
    $cid = $stmt->fetchColumn();
    echo "Yarisma Client ID: " . $cid . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
