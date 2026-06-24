<?php
require_once 'config.php';
echo "--- PHARMACIES ---\n";
$stmt = $pdo->query("SELECT district_id, COUNT(*) as count FROM pharmacies GROUP BY district_id");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);

echo "\n--- HOSPITALS ---\n";
$stmt = $pdo->query("SELECT district_id, COUNT(*) as count FROM hospitals GROUP BY district_id");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);

echo "\n--- BUSINESSES BY CAT ---\n";
$stmt = $pdo->query("SELECT district_id, category, COUNT(*) as count FROM businesses GROUP BY district_id, category");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>
