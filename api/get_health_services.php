<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
require_once '../config.php';
require_once 'fetch_pharmacies.php';

$district_id = $_GET['district_id'] ?? 0;

if (!$district_id) {
    echo json_encode(['status' => 'error', 'message' => 'İlçe ID belirtilmedi.']);
    exit;
}

try {
    // 1. Nöbetçi eczaneleri güncelle/çek
    fetchPharmacies($district_id);
    
    $stmt_pharmacies = $pdo->prepare("SELECT * FROM pharmacies WHERE district_id = ? AND is_on_duty = 1 ORDER BY name ASC");
    $stmt_pharmacies->execute([$district_id]);
    $pharmacies = $stmt_pharmacies->fetchAll(PDO::FETCH_ASSOC);

    // 2. Hastaneleri çek
    $stmt_hospitals = $pdo->prepare("SELECT * FROM hospitals WHERE district_id = ? ORDER BY name ASC");
    $stmt_hospitals->execute([$district_id]);
    $hospitals = $stmt_hospitals->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'district_id' => $district_id,
        'data' => [
            'pharmacies' => $pharmacies,
            'hospitals' => $hospitals
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
