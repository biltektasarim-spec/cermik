<?php
header('Content-Type: application/json');
require_once '../config.php';

try {
    $parent_id = isset($_GET['parent_id']) ? $_GET['parent_id'] : null;
    
    if ($parent_id !== null) {
        $stmt = $pdo->prepare("SELECT * FROM municipal_guide WHERE parent_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$parent_id]);
    } else {
        // Fetch only top-level items for the sidebar initially or all for simplicity
        $stmt = $pdo->query("SELECT * FROM municipal_guide WHERE parent_id IS NULL ORDER BY sort_order ASC, id ASC");
    }
    
    $items = $stmt->fetchAll();

    if ($current_lang === 'en') {
        foreach ($items as &$item) {
            if (!empty($item['title_en'])) $item['title'] = $item['title_en'];
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $items
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
