<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SELECT id, district_id, name, category FROM places WHERE category LIKE '%Hotel%' OR name LIKE '%Otel%' OR name LIKE '%Pansiyon%'");
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
