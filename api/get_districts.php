<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

try {
    $stmt = $pdo->query("SELECT id, name, slug, image, lat, lng FROM districts WHERE is_active = 1 ORDER BY name ASC");
    $districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($districts, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
