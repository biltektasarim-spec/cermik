<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT * FROM custom_menus");
header('Content-Type: application/json');
echo json_encode($stmt->fetchAll());
?>
