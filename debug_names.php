<?php
require_once 'config.php';
echo "--- CERMIK HOTSPRINGS ---\n";
$stmt = $pdo->prepare("SELECT id, name, name_en, district_id FROM places WHERE category = 'HotSpring' AND district_id = 3");
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- CUNGUS HOTSPRINGS ---\n";
$stmt = $pdo->prepare("SELECT id, name, name_en, district_id FROM places WHERE category = 'HotSpring' AND district_id = 5");
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
