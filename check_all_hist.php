<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SELECT id, name, is_approved FROM places WHERE district_id = 3 AND category = 'Historical'");
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
