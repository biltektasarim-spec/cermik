<?php
require_once 'config.php';
try {
    $pdo->exec("ALTER TABLE places ADD COLUMN is_approved TINYINT(1) DEFAULT 1 AFTER district_id");
    echo json_encode(['status' => 'success', 'message' => 'is_approved column added to places table.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
