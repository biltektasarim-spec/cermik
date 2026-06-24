<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT id, name, slug FROM districts");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows, JSON_PRETTY_PRINT);
?>
