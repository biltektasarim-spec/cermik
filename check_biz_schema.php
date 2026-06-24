<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("DESCRIBE businesses");
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
