<?php
require_once '../config.php';
header('Content-Type: application/json');

$districts = $pdo->query("SELECT id, name FROM districts")->fetchAll(PDO::FETCH_ASSOC);
$services = $pdo->query("SELECT id, title, district_id, status FROM services")->fetchAll(PDO::FETCH_ASSOC);

$counts = [];
foreach($services as $s) {
    $did = $s['district_id'];
    $counts[$did] = ($counts[$did] ?? 0) + 1;
}

$results = [
    'districts' => $districts,
    'service_counts_by_id' => $counts,
    'total_services' => count($services),
    'sample_services' => array_slice($services, 0, 5)
];

echo json_encode($results, JSON_PRETTY_PRINT);
