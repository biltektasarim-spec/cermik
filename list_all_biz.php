<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SELECT id, district_id, business_name, category FROM businesses");
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
