<?php
require_once 'config.php';
$district_id = 3; // Cermik
$stmt = $pdo->prepare("SELECT * FROM places WHERE category = 'HotSpring' AND district_id = ? LIMIT 1");
$stmt->execute([$district_id]);
$h = $stmt->fetch(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($h, JSON_PRETTY_PRINT);
?>
