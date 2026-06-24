<?php
require_once 'config.php';
$stmt = $pdo->prepare("SELECT id, name, category FROM places WHERE district_id = 5 AND name LIKE '%Karakaya%'");
$stmt->execute();
header('Content-Type: application/json');
echo json_encode($stmt->fetchAll());
?>
