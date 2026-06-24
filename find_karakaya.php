<?php
require_once 'config.php';
// Cungus (District 5) places search for Karakaya
$stmt = $pdo->prepare("SELECT id, name, category, image_main, district_id FROM places WHERE district_id = 5 AND (name LIKE '%Karakaya%' OR category = 'HotSpring')");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($results);
?>
