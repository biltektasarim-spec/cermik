<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SELECT id, name, image_gallery FROM places WHERE district_id = 3 AND category = 'Historical'");
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
