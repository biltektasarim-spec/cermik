<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT id, name FROM districts");
header('Content-Type: application/json');
echo json_encode($stmt->fetchAll());
?>
