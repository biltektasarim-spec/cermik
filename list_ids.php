<?php
require_once 'config.php';
$stmt = $pdo->prepare("SELECT id FROM places ORDER BY id DESC LIMIT 100");
$stmt->execute();
$ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Existing IDs in places: " . implode(', ', $ids);
?>
