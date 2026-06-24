<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

$district_id = (int)($_GET['district_id'] ?? $_SESSION['district_id'] ?? 0);

try {
    // Tablo yoksa oluştur (auto-migration)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS announcements (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            district_id INT NOT NULL DEFAULT 0,
            content     TEXT,
            content_en  TEXT,
            image       VARCHAR(255),
            is_active   TINYINT(1) DEFAULT 1,
            created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_district (district_id),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $id = (int)($_GET['id'] ?? 0);
    $params = [];
    $where  = ['is_active = 1'];

    if ($id > 0) {
        $where[] = 'id = ?';
        $params[] = $id;
    } else if ($district_id > 0) {
        $where[] = '(district_id = ? OR is_global = 1 OR district_id = 0 OR district_id IS NULL)';
        $params[] = $district_id;
    } else {
        $where[] = '(district_id = 0 OR district_id IS NULL OR is_global = 1)';
    }

    $whereClause = 'WHERE ' . implode(' AND ', $where);

    $stmt = $pdo->prepare("
        SELECT id, district_id, content, content_en, image, is_global, created_at
        FROM announcements
        $whereClause
        ORDER BY created_at DESC, id DESC
        LIMIT 30
    ");
    $stmt->execute($params);
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Language support
    if ($current_lang === 'en') {
        foreach ($announcements as &$a) {
            if (!empty($a['content_en'])) $a['content'] = $a['content_en'];
        }
    }

    if ($id > 0 && count($announcements) > 0) {
        echo json_encode([
            'status' => 'success',
            'data'   => $announcements[0],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'data'   => $announcements,
        'count'  => count($announcements),
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
