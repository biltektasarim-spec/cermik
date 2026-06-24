<?php
require_once 'config.php';
echo "--- BUSINESSES FOR DISTRICT 1 (CERMIK) ---\n";
$stmt = $pdo->prepare("SELECT * FROM businesses WHERE district_id = 1");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows, JSON_PRETTY_PRINT);

echo "\n--- PHARMACIES FOR DISTRICT 1 (CERMIK) ---\n";
$stmt2 = $pdo->prepare("SELECT * FROM pharmacies WHERE district_id = 1");
$stmt2->execute();
$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows2, JSON_PRETTY_PRINT);
?>
