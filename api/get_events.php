<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
require_once '../config.php';

try {
    $id = $_GET['id'] ?? null;
    $dt_id = $_GET['district_id'] ?? $_SESSION['district_id'] ?? ($_POST['district_id'] ?? null);
    
    $query = "SELECT * FROM events WHERE (status = 'APPROVED' OR status = 'PENDING')";
    $params = [];
    
    if ($id) {
        $query .= " AND id = ?";
        $params[] = $id;
    } else if ($dt_id) {
        $query .= " AND (district_id = ? OR (district_id IS NULL AND is_global = 1 AND global_status = 'APPROVED') OR district_id = 0)";
        $params[] = $dt_id;
    } else {
        $query .= " AND (is_global = 1 AND global_status = 'APPROVED') OR district_id = 0 OR district_id IS NULL";
    }
    
    $query .= " ORDER BY event_date DESC, id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $events = $stmt->fetchAll();

    if ($id && count($events) > 0) {
        $event = $events[0];
        if ($current_lang === 'en') {
            if (!empty($event['title_en'])) $event['title'] = $event['title_en'];
            if (!empty($event['description_en'])) $event['description'] = $event['description_en'];
        }
        echo json_encode(['status' => 'success', 'data' => $event]);
        exit;
    }

    if ($current_lang === 'en') {
        foreach ($events as &$e) {
            if (!empty($e['title_en'])) $e['title'] = $e['title_en'];
            if (!empty($e['description_en'])) $e['description'] = $e['description_en'];
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $events,
        'count' => count($events)
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
