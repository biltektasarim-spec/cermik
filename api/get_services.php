<?php
header('Content-Type: application/json');
require_once '../config.php';

try {
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $district_id = $_GET['district_id'] ?? $_SESSION['district_id'] ?? ($_COOKIE['district_id'] ?? null);
    
    $params = [];
    $where_parts = [];
    
    if ($district_id) {
        $where_parts[] = "district_id = ?";
        $params[] = $district_id;
    }
    
    if ($status !== null) {
        $where_parts[] = "status = ?";
        $params[] = $status;
    }
    
    $where_clause = !empty($where_parts) ? "WHERE " . implode(" AND ", $where_parts) : "";
    $sql = "SELECT * FROM services {$where_clause} ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $services = $stmt->fetchAll();

    if ($current_lang === 'en') {
        foreach ($services as &$s) {
            if (!empty($s['title_en'])) $s['title'] = $s['title_en'];
            if (!empty($s['description_en'])) $s['description'] = $s['description_en'];
        }
    }

    // Ensure numeric types for mobile consistency
    foreach ($services as &$s) {
        $s['id'] = (int)$s['id'];
        $s['status'] = (int)$s['status'];
        $s['progress'] = (int)($s['progress'] ?? 0);
        $s['district_id'] = (int)$s['district_id'];
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $services
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
