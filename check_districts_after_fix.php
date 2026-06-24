<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT id, name, slug FROM districts");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>
