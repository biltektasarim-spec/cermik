<?php
require_once 'config.php';
$stmt = $pdo->prepare("SELECT id, name, slug FROM districts WHERE id = 2");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($row, JSON_PRETTY_PRINT);
?>
