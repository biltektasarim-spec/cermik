<?php
require_once 'config.php';
$district_id = 3; // Cermik
$stmt = $pdo->prepare("SELECT name, panorama_360, image_main FROM places WHERE category = 'HotSpring' AND district_id = ? LIMIT 1");
$stmt->execute([$district_id]);
$h = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($h, JSON_PRETTY_PRINT);
?>
