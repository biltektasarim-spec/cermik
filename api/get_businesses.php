<?php
header('Content-Type: application/json');
require_once '../config.php';

try {
    $category = $_GET['category'] ?? null;
    $id = $_GET['id'] ?? null;

    if ($id) {
        // Belirli bir işletme ve ürünleri
        $stmt = $pdo->prepare("SELECT * FROM businesses WHERE id = ?");
        $stmt->execute([$id]);
        $business = $stmt->fetch();

        if ($business && $current_lang === 'en') {
            if (!empty($business['business_name_en'])) $business['business_name'] = $business['business_name_en'];
            // Products localized in detailed view usually but let's be safe
            if (isset($business['products'])) {
                foreach ($business['products'] as &$prod) {
                    if (!empty($prod['name_en'])) $prod['name'] = $prod['name_en'];
                    if (!empty($prod['description_en'])) $prod['description'] = $prod['description_en'];
                }
            }
        }

        echo json_encode(['status' => 'success', 'data' => $business]);
    } else {
        // İşletme listesi
        $district_id = $_GET['district_id'] ?? $_SESSION['district_id'] ?? null;
        $query = "SELECT * FROM businesses WHERE is_approved = 1"; // check is_approved instead of status if applicable, but keep status for safety if column exists
        $params = [];
        
        if ($district_id) {
            $query .= " AND district_id = ?";
            $params[] = $district_id;
        }

        if ($category) {
            $mappedCat = ucfirst(strtolower($category));
            if ($mappedCat === 'Hotels') $mappedCat = 'Hotel';
            if ($mappedCat === 'Restaurants') $mappedCat = 'Restaurant';
            
            $query .= " AND (category = ? OR category = ?)";
            $params[] = $category;
            $params[] = $mappedCat;
        }
        
        $query .= " ORDER BY business_name ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $businesses = $stmt->fetchAll();

        if ($current_lang === 'en') {
            foreach ($businesses as &$b) {
                if (!empty($b['business_name_en'])) $b['business_name'] = $b['business_name_en'];
            }
        }

        echo json_encode(['status' => 'success', 'data' => $businesses]);
    }

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
