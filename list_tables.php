<?php
require_once 'config.php';
header('Content-Type: text/plain');

$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);
?>
