<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';

$district_id = $_GET['id'] ?? 0;

try {
    // 1. Get District Info
    $stmt = $pdo->prepare("SELECT * FROM districts WHERE id = ?");
    $stmt->execute([$district_id]);
    $district = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$district) {
        echo json_encode(['error' => 'İlçe bulunamadı.']);
        exit;
    }

    // 2. Get Settings (Real-time from DB)
    $settings = get_settings($pdo, $district_id);

    // 3. Override mayor settings
    if (!empty($district['mayor_image'])) $settings['mayor_image'] = $district['mayor_image'];
    if (!empty($district['mayor_name'])) $settings['mayor_name'] = $district['mayor_name'];
    if (!empty($district['mayor_title'])) $settings['mayor_title'] = $district['mayor_title'];
    if (!empty($district['mayor_title_en'])) $settings['mayor_title_en'] = $district['mayor_title_en'];

    $mayor_img = $settings['mayor_image'] ?? 'assets/img/mayor/baskan.png';
    $is_en = ($current_lang === 'en');
    
    $stmt_ky = $pdo->prepare("SELECT id, lat, lng FROM places WHERE category = 'Kuruyemis' AND district_id = ? LIMIT 1");
    $stmt_ky->execute([$district_id]);
    $kuruyemis_data = $stmt_ky->fetch(PDO::FETCH_ASSOC);

    // Fetch HotSpring (Special Menu) data
    $stmt_hs = $pdo->prepare("SELECT id, name, name_en, image_main, lat, lng FROM places WHERE category = 'HotSpring' AND district_id = ? LIMIT 1");
    $stmt_hs->execute([$district_id]);
    $hs_data = $stmt_hs->fetch(PDO::FETCH_ASSOC);
    
    $hs_name_tr = !empty($hs_data['name']) ? $hs_data['name'] : ($settings['menu_thermal_tr'] ?? 'Kaplıcalar');
    $hs_name_en = !empty($hs_data['name_en']) ? $hs_data['name_en'] : ($settings['menu_thermal_en'] ?? 'Thermal Centers');
    $hs_img = !empty($hs_data['image_main']) ? $hs_data['image_main'] : ($settings['menu_thermal_img'] ?? 'assets/img/categories/kaplica.jpg');

    // Web structure categories
    $categoriesList = [
        ['id' => 'Historical', 'name' => $is_en ? ($settings['menu_historical_en'] ?? 'Historical') : ($settings['menu_historical_tr'] ?? 'Tarihi Yerler'), 'icon' => 'fa-landmark', 
         'image' => $settings['menu_historical_img'] ?? 'assets/img/categories/historical.jpg', 'status_key' => 'menu_historical_status'],
        ['id' => 'Nature', 'name' => $is_en ? ($settings['menu_nature_en'] ?? 'Nature') : ($settings['menu_nature_tr'] ?? 'Doğal Güzellikler'), 'icon' => 'fa-leaf', 
         'image' => $settings['menu_nature_img'] ?? 'assets/img/categories/nature.jpg', 'status_key' => 'menu_nature_status'],
        ['id' => 'ParkAndGarden', 'name' => $is_en ? ($settings['menu_parks_en'] ?? 'Parks & Gardens') : ($settings['menu_parks_tr'] ?? 'Park & Bahçeler'), 'icon' => 'fa-tree', 
         'image' => $settings['menu_parks_img'] ?? 'assets/img/categories/parks.jpg', 'status_key' => 'menu_parks_status']
    ];
    
    // Yalnızca Çermik ve Çüngüş için "Kaplıcalar" menüsünü grid içine koy (Mevcut ayarları bozmamak için)
    if ($district_id == 3 || $district_id == 5) {
        $categoriesList[] = ['id' => 'HotSpring', 'name' => $is_en ? $hs_name_en : $hs_name_tr, 'icon' => 'fa-hot-tub-person', 
         'image' => $hs_img, 'status_key' => 'menu_thermal_status'];
    }

    if ($district_id == 3) {
        $categoriesList[] = ['id' => 'Kuruyemis', 'name' => $is_en ? ($settings['menu_kuruyemis_en'] ?? 'Nuts & Snacks') : ($settings['menu_kuruyemis_tr'] ?? 'Kuruyemiş'), 'icon' => 'fa-store', 
         'image' => $settings['menu_kuruyemis_img'] ?? 'assets/img/categories/kuruyemis.jpg', 'status_key' => 'menu_kuruyemis_status',
         'lat' => $kuruyemis_data['lat'] ?? null, 'lng' => $kuruyemis_data['lng'] ?? null];
    }

    $categoriesList = array_merge($categoriesList, [
        ['id' => 'cek_gonder', 'name' => $is_en ? ($settings['menu_cek_gonder_en'] ?? 'Send Report') : ($settings['menu_cek_gonder_tr'] ?? 'Çek Gönder'), 'icon' => 'fa-camera', 
         'image' => $settings['menu_cek_gonder_img'] ?? 'assets/img/categories/cek_gonder.jpg', 'status_key' => 'menu_cek_gonder_status'],
        ['id' => 'pharmacy', 'name' => $is_en ? ($settings['menu_pharmacy_en'] ?? 'Pharmacies') : ($settings['menu_pharmacy_tr'] ?? 'Nöbetçi Eczaneler'), 'icon' => 'fa-prescription-bottle-medical', 
         'image' => $settings['menu_pharmacy_img'] ?? 'assets/img/categories/medical.jpg', 'status_key' => 'menu_pharmacy_status'],
        ['id' => 'hotels', 'name' => $is_en ? ($settings['menu_hotels_en'] ?? 'Hotels') : ($settings['menu_hotels_tr'] ?? 'Konaklama'), 'icon' => 'fa-hotel', 
         'image' => $settings['menu_hotels_img'] ?? 'assets/img/categories/hotels.jpg', 'status_key' => 'menu_hotels_status'],
        ['id' => 'restaurants', 'name' => $is_en ? ($settings['menu_restaurants_en'] ?? 'Dining') : ($settings['menu_restaurants_tr'] ?? 'Yeme & İçme'), 'icon' => 'fa-utensils', 
         'image' => $settings['menu_restaurants_img'] ?? 'assets/img/categories/restaurants.jpg', 'status_key' => 'menu_restaurants_status'],
    ]);

    $categories = [];
    foreach ($categoriesList as $cat) {
        $statusKey = $cat['status_key'];
        $isActive = isset($settings[$statusKey]) ? (int)$settings[$statusKey] : 1;
        if ($isActive === 1) {
            unset($cat['status_key']);
            $categories[] = $cat;
        }
    }

    // 4. Custom Menus
    $stmt_m = $pdo->prepare("SELECT * FROM custom_menus WHERE district_id = ? AND is_active = 1 ORDER BY sort_order ASC");
    $stmt_m->execute([$district_id]);
    $db_menus = $stmt_m->fetchAll(PDO::FETCH_ASSOC);
    
    // Özel Menüyü (HotSpring) Hero Banner (Yatay menü) olarak ekle (Tüm ilçeler için dinamik)
    if (!empty($hs_data)) {
        $hsActive = isset($settings['menu_thermal_status']) ? (int)$settings['menu_thermal_status'] : 1;
        if ($hsActive === 1) {
            array_unshift($db_menus, [
                'id' => 99999,
                'district_id' => $district_id,
                'name_tr' => $hs_name_tr,
                'name_en' => $hs_name_en,
                'slug' => 'hotspring',
                'image' => $hs_img,
                'place_id' => (int)$hs_data['id'],
                'lat' => $hs_data['lat'],
                'lng' => $hs_data['lng'],
                'sort_order' => -1,
                'is_active' => 1
            ]);
        }
    }

    // 4b. Live Broadcasts
    $stmt_lb = $pdo->prepare("SELECT * FROM live_broadcasts WHERE (district_id = ? OR district_id IS NULL OR district_id = 0) AND is_active = 1 ORDER BY sort_order ASC, id DESC");
    $stmt_lb->execute([$district_id]);
    $live_broadcasts_raw = $stmt_lb->fetchAll(PDO::FETCH_ASSOC);
    
    $live_broadcasts = [];
    foreach ($live_broadcasts_raw as $lb) {
        $lb['title'] = ($is_en && !empty($lb['title_en'])) ? $lb['title_en'] : $lb['title'];
        $lb['description'] = ($is_en && !empty($lb['description_en'])) ? $lb['description_en'] : $lb['description'];
        $live_broadcasts[] = $lb;
    }

    // 5. Municipal Guide (ALL items for nesting)
    $stmt_g = $pdo->prepare("SELECT * FROM municipal_guide WHERE (district_id = ? OR district_id = 0 OR district_id IS NULL) ORDER BY parent_id ASC, sort_order ASC");
    $stmt_g->execute([$district_id]);
    $guide_raw = $stmt_g->fetchAll(PDO::FETCH_ASSOC);
    
    // Simple localization for guide
    $guide = [];
    foreach ($guide_raw as $g) {
        $g['title'] = ($is_en && !empty($g['title_en'])) ? $g['title_en'] : $g['title'];
        $g['description'] = ($is_en && !empty($g['description_en'])) ? $g['description_en'] : $g['description'];
        $guide[] = $g;
    }

    // 6. Projects (from `services` table)
    $stmt_p = $pdo->prepare("SELECT * FROM services WHERE district_id = ? ORDER BY status DESC, id DESC");
    $stmt_p->execute([$district_id]);
    $projects_raw = $stmt_p->fetchAll(PDO::FETCH_ASSOC);
    
    $projects = [];
    foreach ($projects_raw as $p) {
        $p['title'] = ($is_en && !empty($p['title_en'])) ? $p['title_en'] : $p['title'];
        $p['description'] = ($is_en && !empty($p['description_en'])) ? $p['description_en'] : $p['description'];
        $projects[] = $p;
    }

    // 7. Announcements
    $stmt_a = $pdo->prepare("SELECT id, content, content_en, image, created_at FROM announcements WHERE district_id = ? OR district_id IS NULL ORDER BY created_at DESC LIMIT 20");
    $stmt_a->execute([$district_id]);
    $announcements = $stmt_a->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'id' => (int)$district['id'],
        'name' => $district['name'],
        'slug' => $district['slug'],
        'custom_menus' => $db_menus,
        'live_broadcasts' => $live_broadcasts,
        'settings' => [
            'site_name' => $settings['site_name'] ?? $district['name'],
            'site_logo' => !empty($settings['site_logo']) ? $settings['site_logo'] : (file_exists(__DIR__ . "/../{$district['slug']}/assets/logo.png") ? "{$district['slug']}/assets/logo.png" : 'assets/img/logo/logo.png'),
            'mayor_name' => $settings['mayor_name'] ?? 'Başkan',
            'mayor_title' => $is_en ? ($settings['mayor_title_en'] ?? 'Mayor') : ($settings['mayor_title'] ?? 'Belediye Başkanı'),
            'mayor_image' => $mayor_img,
            'site_address' => $settings['site_address'] ?? '',
            'site_phone' => $settings['site_phone'] ?? '',
            'site_email' => $settings['site_email'] ?? '',
            'facebook_link' => $settings['facebook_link'] ?? '',
            'instagram_link' => $settings['instagram_link'] ?? '',
            'twitter_link' => $settings['twitter_link'] ?? '',
            'youtube_link' => $settings['youtube_link'] ?? '',
            'municipal_guide' => $guide,
            'projects' => $projects,
            'announcements' => $announcements
        ],
        'weather' => [
            'temp' => 24,
            'description' => $is_en ? 'Sunny' : 'Güneşli',
            'icon' => '01d'
        ],
        'categories' => $categories
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
