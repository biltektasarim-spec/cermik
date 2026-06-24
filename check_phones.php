<?php
require_once 'config.php';
echo "--- PHARMACIES (CUNGUS) ---\n";
$stmt = $pdo->prepare("SELECT name, phone, address FROM pharmacies WHERE district_id = 5");
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- HOSPITALS (CUNGUS) ---\n";
$stmt = $pdo->prepare("SELECT name, phone FROM hospitals WHERE district_id = 5");
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
