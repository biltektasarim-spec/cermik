<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SELECT category, COUNT(*) as count FROM places WHERE district_id = 3 GROUP BY category");
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
