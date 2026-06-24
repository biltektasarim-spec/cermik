<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SELECT is_approved FROM places LIMIT 1");
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
