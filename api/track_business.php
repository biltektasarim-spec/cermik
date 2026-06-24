<?php
require_once __DIR__ . '/../config.php';

// JSON response header
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$business_id = isset($_POST['business_id']) ? (int)$_POST['business_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($business_id > 0 && in_array($action, ['view', 'direction'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO business_stats (business_id, event_type) VALUES (?, ?)");
        $stmt->execute([$business_id, $action]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        error_log('[REHBER ANALYTICS ERROR] ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
}
