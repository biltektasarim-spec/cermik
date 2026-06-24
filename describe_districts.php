<?php
require_once 'config.php';
$stmt = $pdo->query("DESCRIBE districts");
header('Content-Type: application/json');
echo json_encode($stmt->fetchAll());
?>
