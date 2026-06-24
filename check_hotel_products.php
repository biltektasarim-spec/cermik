<?php
require_once 'config.php';
// Get first few hotels
$hotels = $pdo->query("SELECT id, business_name FROM businesses WHERE category='Hotel' LIMIT 5")->fetchAll();
$results = [];
foreach ($hotels as $h) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE business_id = ?");
    $stmt->execute([$h['id']]);
    $results[$h['business_name']] = $stmt->fetchAll();
}
header('Content-Type: application/json');
echo json_encode($results);
?>
