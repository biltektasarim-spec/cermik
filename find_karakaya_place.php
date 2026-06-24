<?php
require_once 'config.php';
$districtId = 5;
$stmt = $pdo->prepare("SELECT id, name, category, slug FROM places WHERE district_id = ? AND (name LIKE '%Karakaya%' OR slug LIKE '%karakaya%')");
$stmt->execute([$districtId]);
header('Content-Type: application/json');
echo json_encode($stmt->fetchAll());
?>
