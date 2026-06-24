<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT name, value FROM settings WHERE name LIKE 'sms_%'");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($res, JSON_PRETTY_PRINT);
?>
