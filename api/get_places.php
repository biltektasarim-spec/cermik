<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
require_once '../config.php';

try {
    $id = $_GET['id'] ?? null;
    $category = $_GET['category'] ?? null;
    $district_id = $_GET['district_id'] ?? $_SESSION['district_id'] ?? null;
    
    if ($category === 'hotels' || $category === 'restaurants' || $category === 'Hotel' || $category === 'Restaurant') {
        $biz_cat = ($category === 'hotels' || $category === 'Hotel') ? 'Hotel' : 'Restaurant';
        $query = "SELECT 
                    id, 
                    ? as category,
                    business_name as name,
                    business_name_en as name_en,
                    description,
                    description_en,
                    image_main as image_path,
                    '' as location_url,
                    panorama_360,
                    image_gallery,
                    '' as qr_code_path,
                    lat,
                    lng,
                    contact_info as address,
                    phone as phone,
                    order_enabled as order_enabled,
                    order_link as order_link,
                    1 as is_on_duty,
                    1 as popular_score 
                  FROM businesses 
                  WHERE is_approved = 1 AND category = ?";
        $params = [$category, $biz_cat];

        if ($id) {
            $query .= " AND id = ?";
            $params[] = $id;
        }
        if ($district_id) {
            $query .= " AND district_id = ?";
            $params[] = $district_id;
        }
        $query .= " ORDER BY business_name ASC";

    } else {
        $query = "SELECT * FROM places WHERE is_approved = 1";
        $params = [];
        
        if ($id) {
            $query .= " AND id = ?";
            $params[] = $id;
        }
        if ($category) {
            $mappedCat = ucfirst(strtolower($category));
            if ($mappedCat === 'Hotspring') $mappedCat = 'HotSpring';
            if ($mappedCat === 'Parkandgarden') $mappedCat = 'ParkAndGarden';
            
            $query .= " AND (category = ? OR category = ?)";
            $params[] = $category;
            $params[] = $mappedCat;
        }
        if ($district_id) {
            $query .= " AND district_id = ?";
            $params[] = $district_id;
        }
        
        $query .= " ORDER BY popular_score DESC";
        if (!$id && !$category && !$district_id) $query .= " LIMIT 10";
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $places = $stmt->fetchAll();

    if ($id && count($places) > 0) {
        $place = $places[0];
        
        // Check-in İstatistiklerini ekle (Sadece tekil detay istendiğinde)
        $target_id = $place['id'];
        $target_type = ($category === 'hotels' || $category === 'restaurants') ? 'business' : 'place';
        $d_id = $place['district_id'] ?? $district_id;

        // Günlük
        $stmt_day = $pdo->prepare("SELECT COUNT(*) FROM check_ins WHERE district_id = ? AND target_id = ? AND target_type = ? AND status = 'APPROVED' AND DATE(created_at) = CURDATE()");
        $stmt_day->execute([$d_id, $target_id, $target_type]);
        $count_day = (int)$stmt_day->fetchColumn();

        // Aylık
        $stmt_month = $pdo->prepare("SELECT COUNT(*) FROM check_ins WHERE district_id = ? AND target_id = ? AND target_type = ? AND status = 'APPROVED' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
        $stmt_month->execute([$d_id, $target_id, $target_type]);
        $count_month = (int)$stmt_month->fetchColumn();

        // Yıllık
        $stmt_year = $pdo->prepare("SELECT COUNT(*) FROM check_ins WHERE district_id = ? AND target_id = ? AND target_type = ? AND status = 'APPROVED' AND YEAR(created_at) = YEAR(CURRENT_DATE())");
        $stmt_year->execute([$d_id, $target_id, $target_type]);
        $count_year = (int)$stmt_year->fetchColumn();

        $place['stats'] = [
            'daily' => $count_day,
            'monthly' => $count_month,
            'yearly' => $count_year
        ];

        if ($current_lang === 'en' && !empty($place['name_en'])) {
            $place['name'] = $place['name_en'];
        }
        echo json_encode(['status' => 'success', 'data' => $place]);
        exit;
    }

    if ($current_lang === 'en') {
        foreach ($places as &$p) {
            if (!empty($p['name_en'])) $p['name'] = $p['name_en'];
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $places
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
