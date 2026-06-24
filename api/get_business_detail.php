<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID gerekli.']);
    exit;
}

try {
    // 1. İşletme bilgilerini çek
    $stmt = $pdo->prepare("SELECT b.*, d.slug as district_slug, d.name as district_name FROM businesses b JOIN districts d ON b.district_id = d.id WHERE b.id = ?");
    $stmt->execute([$id]);
    $business = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$business) {
        // 1.1. Eğer işletme bulunamadıysa hastaneler tablosuna bak
        $stmt_h = $pdo->prepare("SELECT h.*, d.slug as district_slug, d.name as district_name FROM hospitals h JOIN districts d ON h.district_id = d.id WHERE h.id = ?");
        $stmt_h->execute([$id]);
        $hospital = $stmt_h->fetch(PDO::FETCH_ASSOC);

        if ($hospital) {
            // Hastane verisini işletme formatına uyarla
            $business = [
                'id' => $hospital['id'],
                'district_id' => $hospital['district_id'],
                'district_name' => $hospital['district_name'],
                'district_slug' => $hospital['district_slug'],
                'business_name' => $hospital['name'],
                'business_name_en' => $hospital['name_en'] ?? '',
                'category' => 'hospital',
                'description' => $hospital['description'] ?? '',
                'description_en' => $hospital['description_en'] ?? '',
                'lat' => $hospital['lat'],
                'lng' => $hospital['lng'],
                'panorama_360' => $hospital['panorama_360'] ?? '',
                'image_main' => $hospital['image_main'] ?? '',
                'contact_info' => '',
                'phone' => '',
                'working_hours' => '{}',
                'hotel_info' => '{}',
                'image_gallery' => '[]',
                'order_enabled' => 0,
                'order_link' => ''
            ];
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Kayıt bulunamadı.']);
            exit;
        }
    }

    // 2. Ürünleri/Menüyü getir
    $stmt_p = $pdo->prepare("SELECT * FROM products WHERE business_id = ?");
    $stmt_p->execute([$id]);
    $products = $stmt_p->fetchAll(PDO::FETCH_ASSOC);

    // 3. Çalışma saatleri (JSON parse)
    $working_hours = json_decode($business['working_hours'] ?? '{}', true) ?: [];

    // 4. Otel bilgisi (JSON parse)
    $hotel_info = json_decode($business['hotel_info'] ?? '{}', true) ?: [];

    // 5. Galeri (JSON parse)
    $gallery = json_decode($business['image_gallery'] ?? '[]', true) ?: [];

    // 6. Check-in İstatistikleri
    $district_id = $business['district_id'];
    
    // Günlük
    $stmt_day = $pdo->prepare("SELECT COUNT(*) FROM check_ins WHERE district_id = ? AND target_id = ? AND target_type = 'business' AND status = 'APPROVED' AND DATE(created_at) = CURDATE()");
    $stmt_day->execute([$district_id, $id]);
    $count_day = (int)$stmt_day->fetchColumn();

    // Aylık
    $stmt_month = $pdo->prepare("SELECT COUNT(*) FROM check_ins WHERE district_id = ? AND target_id = ? AND target_type = 'business' AND status = 'APPROVED' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $stmt_month->execute([$district_id, $id]);
    $count_month = (int)$stmt_month->fetchColumn();

    // Yıllık
    $stmt_year = $pdo->prepare("SELECT COUNT(*) FROM check_ins WHERE district_id = ? AND target_id = ? AND target_type = 'business' AND status = 'APPROVED' AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $stmt_year->execute([$district_id, $id]);
    $count_year = (int)$stmt_year->fetchColumn();

    // Localization
    $is_en = ($current_lang === 'en');
    $display_name = ($is_en && !empty($business['business_name_en'])) ? $business['business_name_en'] : $business['business_name'];
    $display_desc = ($is_en && !empty($business['description_en'])) ? $business['description_en'] : ($business['description'] ?? '');

    // Products with localization
    $localized_products = [];
    foreach ($products as $p) {
        $localized_products[] = [
            'id' => (int)$p['id'],
            'name' => ($is_en && !empty($p['name_en'])) ? $p['name_en'] : $p['name'],
            'description' => ($is_en && !empty($p['description_en'])) ? $p['description_en'] : ($p['description'] ?? ''),
            'price' => (float)($p['price'] ?? 0),
            'image_path' => $p['image_path'] ?? '',
        ];
    }

    $response = [
        'status' => 'success',
        'data' => [
            'id' => (int)$business['id'],
            'business_name' => $display_name,
            'category' => $business['category'],
            'description' => $display_desc,
            'image_main' => $business['image_main'] ?? '',
            'panorama_360' => $business['panorama_360'] ?? '',
            'image_gallery' => $gallery,
            'contact_info' => $business['contact_info'] ?? '',
            'lat' => $business['lat'] ?? '',
            'lng' => $business['lng'] ?? '',
            'working_hours' => $working_hours,
            'hotel_info' => $hotel_info,
            'products' => $localized_products,
            'district_id' => (int)$business['district_id'],
            'district_name' => $business['district_name'],
            'district_slug' => $business['district_slug'],
            'order_enabled' => (int)$business['order_enabled'],
            'order_link' => $business['order_link'] ?? '',
            'stats' => [
                'daily' => $count_day,
                'monthly' => $count_month,
                'yearly' => $count_year,
            ],
        ],
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
