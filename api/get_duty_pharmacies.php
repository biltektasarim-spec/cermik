<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';
require_once 'fetch_pharmacies.php';

$district_id = $_GET['district_id'] ?? 0;

if (!$district_id) {
    echo json_encode(['status' => 'error', 'message' => 'İlçe ID belirtilmedi.']);
    exit;
}

try {
    // 1. Önce fetch işlemini tetikle (Cache kontrolü fetchPharmacies içinde yapılıyor)
    fetchPharmacies($district_id);

    // 2. Güncel nöbetçi eczaneleri çek
    $stmt = $pdo->prepare("SELECT * FROM pharmacies WHERE district_id = ? AND is_on_duty = 1 ORDER BY name ASC");
    $stmt->execute([$district_id]);
    $pharmacies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'district_id' => $district_id,
        'data' => $pharmacies
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
