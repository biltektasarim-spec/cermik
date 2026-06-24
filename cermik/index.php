<?php
require_once '../config.php';

$slug = 'cermik';
$stmt = $pdo->prepare("SELECT * FROM districts WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$current_district = $stmt->fetch();

function resolve_image_path($path) {
    if (!$path) return '';
    if (strpos($path, 'http') === 0) return $path;
    if (strpos($path, 'assets/') === 0 || strpos($path, 'uploads/') === 0) {
        // Yerel klasörde varsa direkt kullan
        if (file_exists(__DIR__ . '/' . $path)) {
            return $path;
        }
        // Yoksa bir üst dizinden (root) çek
         return '../' . $path;
    }
    return $path;
}

if (!$current_district) {
    header("HTTP/1.0 404 Not Found");
    echo "İlçe bulunamadı.";
    exit;
}
$district_id = $current_district['id'];
$_SESSION['district_id'] = $district_id; // Set session for APIs
setcookie('district_id', $district_id, time() + (86400 * 30), "/");
setcookie('district_slug', $slug, time() + (86400 * 30), "/");

$settings = get_settings($pdo, $district_id);
$is_en = ($current_lang === 'en');

// Override mayor settings with district specific ones if available
if (!empty($current_district['mayor_image'])) $settings['mayor_image'] = $current_district['mayor_image'];
if (!empty($current_district['mayor_name'])) $settings['mayor_name'] = $current_district['mayor_name'];
if (!empty($current_district['mayor_title'])) $settings['mayor_title'] = $current_district['mayor_title'];
if (!empty($current_district['mayor_title_en'])) $settings['mayor_title_en'] = $current_district['mayor_title_en'];

// Category Images
$img_hist = $settings['menu_historical_img'] ?? 'assets/img/categories/historical.jpg';
$img_nature = $settings['menu_nature_img'] ?? 'assets/img/categories/nature.jpg';
$img_parks = $settings['menu_parks_img'] ?? 'assets/img/categories/parks.jpg';
$img_kuruyemis = $settings['menu_kuruyemis_img'] ?? 'assets/img/categories/kuruyemis.jpg';
$img_cek = $settings['menu_cek_gonder_img'] ?? 'assets/img/categories/cek_gonder.jpg';
$img_med = $settings['menu_pharmacy_img'] ?? 'assets/img/categories/medical.jpg';
$img_hotel = $settings['menu_hotels_img'] ?? 'assets/img/categories/hotels.jpg';
$img_rest = $settings['menu_restaurants_img'] ?? 'assets/img/categories/restaurants.jpg';

$stmt_menus = $pdo->prepare("SELECT * FROM custom_menus WHERE district_id = ? AND is_active = 1 ORDER BY sort_order ASC");
$stmt_menus->execute([$district_id]);
$custom_menus = $stmt_menus->fetchAll();

$stmt_lb = $pdo->prepare("SELECT * FROM live_broadcasts WHERE (district_id = ? OR district_id IS NULL OR district_id = 0) AND is_active = 1 ORDER BY sort_order ASC, id DESC");
$stmt_lb->execute([$district_id]);
$live_broadcasts = $stmt_lb->fetchAll();

// Kaplıca özel verisini çek (Menü resmi ve isim için)
$stmt_kp = $pdo->prepare("SELECT id, name, name_en, image_main, slogan, slogan_en, lat, lng FROM places WHERE category = 'HotSpring' AND district_id = ? LIMIT 1");
$stmt_kp->execute([$district_id]);
$kaplica_menu = $stmt_kp->fetch();

// Kuruyemiş pazarı özel verisini çek (Mesafe bilgisi için)
$stmt_ky = $pdo->prepare("SELECT id, lat, lng FROM places WHERE category = 'Kuruyemis' AND district_id = ? LIMIT 1");
$stmt_ky->execute([$district_id]);
$kuruyemis_data = $stmt_ky->fetch();

// BÜTÜN BANNERLARI TEK BİR LİSTEDE TOPLA (SIRALAMA İÇİN)
$all_banners = [];

// 1. Canlı Yayınlar
foreach ($live_broadcasts as $lb) {
    $all_banners[] = [
        'type' => 'live',
        'id' => $lb['id'],
        'title' => ($is_en && !empty($lb['title_en'])) ? $lb['title_en'] : $lb['title'],
        'image' => $lb['image'],
        'url' => '../live.php?id=' . $lb['id'] . '&slug=' . $slug,
        'sort_order' => intval($lb['sort_order'] ?? 0),
        'lat' => $lb['lat'] ?? null,
        'lng' => $lb['lng'] ?? null
    ];
}

// 2. Özel Menüler
foreach ($custom_menus as $m) {
    $m_url = $m['place_id'] ? ("../place_detail.php?id=" . $m['place_id']) : ($m['target_url'] ?? '#');
    $all_banners[] = [
        'type' => 'custom',
        'id' => $m['id'],
        'title' => ($is_en && !empty($m['name_en'])) ? $m['name_en'] : $m['name_tr'],
        'image' => $m['image'],
        'url' => $m_url,
        'icon' => $m['icon'] ?: 'fa-link',
        'sort_order' => intval($m['sort_order'] ?? 0),
        'lat' => $m['lat'] ?? null,
        'lng' => $m['lng'] ?? null
    ];
}

// 3. Özel İlçe Sayfası (Kaplıca)
if ($kaplica_menu) {
    $kp_title = ($is_en) ? ($kaplica_menu['name_en'] ?? $settings['hero_title_en'] ?? 'District Guide') : ($kaplica_menu['name'] ?? $settings['hero_title_tr'] ?? 'Çermik Kaplıcaları');
    $kp_slogan = ($is_en) ? ($kaplica_menu['slogan_en'] ?? $settings['hero_desc_en'] ?? '') : ($kaplica_menu['slogan'] ?? $settings['hero_desc_tr'] ?? '');
    
    $all_banners[] = [
        'type' => 'special',
        'title' => $kp_title,
        'slogan' => $kp_slogan,
        'image' => $kaplica_menu['image_main'] ?? $settings['hero_image'] ?? 'assets/img/categories/kaplica.jpg',
        'url' => 'kaplica.php',
        'sort_order' => intval($settings['hero_sort_order'] ?? 999), // Ayarlarda varsa oradan al, yoksa en sonda
        'lat' => $kaplica_menu['lat'] ?? null,
        'lng' => $kaplica_menu['lng'] ?? null
    ];
}

// Hepsini sırala
usort($all_banners, function($a, $b) {
    return $a['sort_order'] - $b['sort_order'];
});
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $settings['site_name'] ?? 'Çermik Rehberi'; ?> - ROTAREHBER</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=2.0">
    <?php include '../includes/pwa_meta.php'; ?>
</head>
<body data-page-context="Genel Çermik Rehberi" data-district-slug="cermik">
<?php include '../includes/splash_screen.php'; ?>

<?php include '../includes/theme_bg.php'; ?>

<div id="app">
    <div class="sidebar-overlay" onclick="app.toggleSidebar()"></div>
    <div class="sidebar-menu" id="sidebar-menu">
        <div class="sidebar-profile">
            <div class="mayor-img-container">
                <img src="<?php echo resolve_image_path($settings['mayor_image'] ?? 'assets/img/mayor/baskan.png'); ?>" alt="Belediye Başkanı" onerror="this.style.display='none'">
            </div>
            <div class="mayor-info">
                <h3><?php echo htmlspecialchars($settings['mayor_name'] ?? 'Başkan'); ?></h3>
                <span><?php echo htmlspecialchars($is_en ? ($settings['mayor_title_en'] ?? $settings['mayor_title'] ?? 'Mayor') : ($settings['mayor_title'] ?? 'Belediye Başkanı')); ?></span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="javascript:void(0)" class="sidebar-item" onclick="app.navigateTo('home'); app.toggleSidebar();">
                <i class="fa-solid fa-house"></i>
                <span><?php echo __('home'); ?></span>
            </a>
            <div class="sidebar-divider" style="margin: 10px 0; border-color: rgba(255,255,255,0.05);"></div>
            
            <a href="javascript:void(0)" class="sidebar-item" onclick="app.switchTab('services', this); app.toggleSidebar();">
                <i class="fa-solid fa-hand-holding-heart"></i>
                <span><?php echo __('service_tab'); ?></span>
            </a>
            <a href="javascript:void(0)" class="sidebar-item" onclick="app.toggleAnnouncementsModal(); app.toggleSidebar();">
                <i class="fa-solid fa-bullhorn"></i>
                <span><?php echo __('announcements'); ?></span>
            </a>
            <a href="javascript:void(0)" class="sidebar-item" onclick="app.switchTab('events', this); app.toggleSidebar();">
                <i class="fa-solid fa-calendar-star"></i>
                <span><?php echo __('event_tab'); ?></span>
            </a>

            <!-- Municipal Guide (Belediye Rehberi) Entries -->
            <div class="sidebar-divider" style="margin: 10px 0; border-color: rgba(255,255,255,0.05);"></div>
            <p style="padding: 0 20px; font-size: 0.7rem; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 5px;"><?php echo __('belediye_rehberi'); ?></p>
            <?php 
            $stmt_guide = $pdo->prepare("SELECT id, title, title_en FROM municipal_guide WHERE (district_id = ? OR district_id = 0 OR district_id IS NULL) AND parent_id IS NULL ORDER BY sort_order ASC");
            $stmt_guide->execute([$district_id]);
            $guide_items = $stmt_guide->fetchAll();
            foreach ($guide_items as $gi):
                $gi_title = ($is_en && !empty($gi['title_en'])) ? $gi['title_en'] : $gi['title'];
            ?>
                <a href="municipal_guide_detail.php?id=<?php echo $gi['id']; ?>" class="sidebar-item">
                    <i class="fa-solid fa-book-atlas"></i>
                    <span><?php echo htmlspecialchars($gi_title); ?></span>
                </a>
            <?php endforeach; ?>


        </nav>
        
        <?php if (!empty($settings['site_phone']) || !empty($settings['site_email']) || !empty($settings['site_address'])): ?>
        <div class="sidebar-contact" style="padding: 12px 20px; font-size: 0.8rem; color: var(--text-secondary); border-top: 1px solid rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.05); margin: auto 0 0 0; line-height: 1.4; background: rgba(0,0,0,0.1);">
            <?php if (!empty($settings['site_address'])): ?>
            <div style="margin-bottom: 8px; display: flex; gap: 10px;"><i class="fa-solid fa-location-dot" style="margin-top: 3px; color: var(--secondary); width: 14px; text-align: center;"></i> <span style="flex: 1;"><?php echo nl2br(htmlspecialchars($settings['site_address'])); ?></span></div>
            <?php endif; ?>
            <?php if (!empty($settings['site_phone'])): ?>
            <div style="margin-bottom: 8px; display: flex; gap: 10px; align-items: center;"><i class="fa-solid fa-phone" style="color: var(--secondary); width: 14px; text-align: center;"></i> <a href="tel:<?php echo htmlspecialchars($settings['site_phone']); ?>" style="color: inherit; text-decoration: none; flex: 1;"><?php echo htmlspecialchars($settings['site_phone']); ?></a></div>
            <?php endif; ?>
            <?php if (!empty($settings['site_email'])): ?>
            <div style="display: flex; gap: 10px; align-items: center;"><i class="fa-solid fa-envelope" style="color: var(--secondary); width: 14px; text-align: center;"></i> <a href="mailto:<?php echo htmlspecialchars($settings['site_email']); ?>" style="color: inherit; text-decoration: none; flex: 1;"><?php echo htmlspecialchars($settings['site_email']); ?></a></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="sidebar-social">
            <?php if (!empty($settings['facebook_link'])): ?>
                <a href="<?php echo $settings['facebook_link']; ?>" target="_blank"><i class="fa-brands fa-facebook"></i></a>
            <?php endif; ?>
            <?php if (!empty($settings['instagram_link'])): ?>
                <a href="<?php echo $settings['instagram_link']; ?>" target="_blank"><i class="fa-brands fa-instagram"></i></a>
            <?php endif; ?>
            <?php if (!empty($settings['twitter_link'])): ?>
                <a href="<?php echo $settings['twitter_link']; ?>" target="_blank"><i class="fa-brands fa-x-twitter"></i></a>
            <?php endif; ?>
            <?php if (!empty($settings['youtube_link'])): ?>
                <a href="<?php echo $settings['youtube_link']; ?>" target="_blank"><i class="fa-brands fa-youtube"></i></a>
            <?php endif; ?>
        </div>

        <div class="sidebar-lang-switcher" style="display:none;"></div>
    </div>

    <header class="header">
        <div class="header-icons" style="flex: 1; justify-content: flex-start;">
            <div id="weather-widget">
                <i class="fa-solid fa-cloud" id="weather-icon"></i>
                <span id="weather-temp">--°</span>
            </div>
            <i class="fa-solid fa-bell" id="bell-icon"></i>
        </div>
        <div class="header-title-wrap" style="flex: 2;">
            <img src="<?php echo !empty($settings['site_logo']) ? resolve_image_path($settings['site_logo']) : 'assets/logo.png'; ?>" class="header-logo" alt="Logo" onerror="this.src='../assets/img/logo/logo.png'; this.onerror=null;">
            <h1><?php echo $settings['site_name'] ?? $current_district['name']; ?></h1>
        </div>
        <div class="sidebar-toggle" onclick="app.toggleSidebar()" style="flex: 1; display: flex; justify-content: flex-end; cursor: pointer;">
            <i class="fa-solid fa-bars" style="font-size: 1.5rem;"></i>
        </div>
    </header>

    <main id="main-content">
        <section id="explore" class="tab-content active section animate-in">
            <div style="margin-bottom: 25px;">
                <!-- DİNAMİK BANNER LİSTESİ (CANLI, ÖZEL MENÜ, ANA BANNER) -->
                <?php foreach($all_banners as $b): ?>
                    <?php if ($b['type'] === 'live'): ?>
                    <div class="category-item hero-banner" 
                         onclick="window.location.href='<?php echo htmlspecialchars($b['url']); ?>'" 
                         style="width: 100%; height: 160px; position: relative; overflow: hidden; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); margin-bottom: 20px;"
                         data-lat="<?php echo $b['lat']; ?>" 
                         data-lng="<?php echo $b['lng']; ?>" 
                         data-id="<?php echo $b['id']; ?>" 
                         data-type="live">
                        <div class="category-icon" style="background-image: url('<?php echo htmlspecialchars(resolve_image_path($b['image'] ?: 'assets/img/og-default.jpg')); ?>'); width: 100%; height: 100%; background-size: cover; background-position: center; filter: brightness(0.6);"></div>
                        <div style="position: absolute; top:15px; right:15px; z-index: 2; display: flex; align-items: center; gap: 5px; background: #ff4757; color: white; padding: 4px 10px; border-radius: 5px; font-size: 0.75rem; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                            <i class="fa-solid fa-circle" style="font-size: 0.4rem; animation: pulse 2s infinite;"></i> CANLI
                        </div>
                        <div style="position: absolute; top:15px; left:15px; z-index: 2; display: flex; align-items: center; gap: 4px; background: rgba(0,0,0,0.2); color: rgba(255,255,255,0.7); padding: 2px 5px; border-radius: 8px; font-size: 0.45rem; font-weight: 500; backdrop-filter: blur(2px);">
                            <span class="distance-info"></span>
                        </div>
                        <div style="position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: white; padding: 20px;">
                            <i class="fa-solid fa-play-circle" style="font-size: 2.5rem; opacity: 0.8; margin-bottom: 10px; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"></i>
                            <h3 style="font-size: 1.4rem; margin-bottom: 5px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo htmlspecialchars($b['title']); ?></h3>
                        </div>
                    </div>
                    <?php elseif ($b['type'] === 'custom'): ?>
                    <div class="category-item hero-banner" 
                         onclick="window.location.href='<?php echo htmlspecialchars($b['url']); ?>'" 
                         style="width: 100%; height: 160px; position: relative; overflow: hidden; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); margin-bottom: 20px;"
                         <?php if(!empty($b['lat']) && !empty($b['lng'])): ?>
                         data-lat="<?php echo $b['lat']; ?>" 
                         data-lng="<?php echo $b['lng']; ?>" 
                         data-id="<?php echo $b['id']; ?>" 
                         data-type="custom_menu"
                         <?php endif; ?>>
                        <div class="category-icon" style="background-image: url('<?php echo htmlspecialchars(resolve_image_path($b['image'] ?: '')); ?>'); width: 100%; height: 100%; background-size: cover; background-position: center !important; filter: brightness(0.6); <?php if(empty($b['image'])) echo 'background: var(--primary-gradient);'; ?>"></div>
                        <?php if(!empty($b['lat']) && !empty($b['lng'])): ?>
                        <div style="position: absolute; bottom:15px; left:20px; z-index: 2; display: flex; align-items: center; gap: 5px; background: rgba(0,0,0,0.5); color: white; padding: 4px 10px; border-radius: 15px; font-size: 0.7rem; font-weight: 600; backdrop-filter: blur(5px);">
                            <span class="distance-info"></span>
                        </div>
                        <?php endif; ?>
                        <div style="position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: white; padding: 20px;">
                            <?php if (empty($b['image'])): ?>
                            <i class="fa-solid <?php echo htmlspecialchars($b['icon'] ?? 'fa-link'); ?>" style="font-size: 2.5rem; opacity: 0.8; margin-bottom: 10px; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"></i>
                            <?php endif; ?>
                            <h3 style="font-size: 1.4rem; margin-bottom: 5px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo htmlspecialchars($b['title']); ?></h3>
                        </div>
                    </div>
                    <?php elseif ($b['type'] === 'special'): ?>
                    <div class="category-item hero-banner" onclick="window.location.href='<?php echo $b['url']; ?>'" 
                         style="width: 100%; height: 160px; position: relative; overflow: hidden; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); margin-bottom: 20px;"
                         <?php if(!empty($b['lat']) && !empty($b['lng'])): ?>
                         data-lat="<?php echo $b['lat']; ?>" 
                         data-lng="<?php echo $b['lng']; ?>" 
                         data-type="place"
                         <?php endif; ?>>
                        <div class="category-icon" style="background-image: url('<?php echo htmlspecialchars(resolve_image_path($b['image'])); ?>'); width: 100%; height: 100%; background-size: cover; background-position: center !important; filter: brightness(0.7);"></div>
                        <?php if(!empty($b['lat']) && !empty($b['lng'])): ?>
                        <div style="position: absolute; bottom:15px; left:20px; z-index: 2; display: flex; align-items: center; gap: 5px; background: rgba(0,0,0,0.5); color: white; padding: 4px 10px; border-radius: 15px; font-size: 0.7rem; font-weight: 600; backdrop-filter: blur(5px);">
                            <span class="distance-info"></span>
                        </div>
                        <?php endif; ?>
                        <div style="position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: white; padding: 20px;">
                            <h3 style="font-size: 1.4rem; margin-bottom: 5px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo htmlspecialchars($b['title']); ?></h3>
                            <?php if (!empty($b['slogan'])): ?>
                            <p style="font-size: 0.8rem; opacity: 0.9; max-width: 80%; line-height: 1.4;"><?php echo htmlspecialchars($b['slogan']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <h2 style="margin-bottom: 5px; font-size: 0.9rem; opacity: 0.8; font-weight: 400;"><?php echo htmlspecialchars(($is_en ? ($settings['explore_desc_en'] ?? '') : ($settings['explore_desc'] ?? ''))); ?></h2>
            </div>

            <div class="category-grid">
                <div class="category-item" onclick="app.navigateTo('Historical')">
                    <div class="category-icon" style="background-image: url('<?php echo resolve_image_path($img_hist); ?>');"></div>
                    <span><i class="fa-solid fa-landmark"></i> <?php echo ($is_en ? ($settings['menu_historical_en'] ?? __('historical_places')) : ($settings['menu_historical_tr'] ?? __('historical_places'))); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('Nature')">
                    <div class="category-icon" style="background-image: url('<?php echo resolve_image_path($img_nature); ?>');"></div>
                    <span><i class="fa-solid fa-leaf"></i> <?php echo ($is_en ? ($settings['menu_nature_en'] ?? __('nature_parks')) : ($settings['menu_nature_tr'] ?? __('nature_parks'))); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('ParkAndGarden')">
                    <div class="category-icon" style="background-image: url('<?php echo resolve_image_path($img_parks); ?>');"></div>
                    <span><i class="fa-solid fa-tree"></i> <?php echo ($is_en ? ($settings['menu_parks_en'] ?? __('park_bahce')) : ($settings['menu_parks_tr'] ?? __('park_bahce'))); ?></span>
                </div>
                <?php if ($district_id == 3): ?>
                <div class="category-item" onclick="app.navigateTo('kuruyemis')" style="position: relative;"
                     <?php if($kuruyemis_data && !empty($kuruyemis_data['lat'])): ?>
                     data-lat="<?php echo $kuruyemis_data['lat']; ?>" 
                     data-lng="<?php echo $kuruyemis_data['lng']; ?>" 
                     data-id="<?php echo $kuruyemis_data['id']; ?>" 
                     data-type="place"
                     <?php endif; ?>>
                    <?php if($kuruyemis_data && !empty($kuruyemis_data['lat'])): ?>
                    <div style="position: absolute; top:5px; right:5px; z-index: 2; display: flex; align-items: center; gap: 4px; background: rgba(0,0,0,0.6); color: #fff; padding: 2px 6px; border-radius: 8px; font-size: 0.6rem; font-weight: bold; line-height:1;">
                        <span class="distance-info"></span>
                    </div>
                    <?php endif; ?>
                    <div class="category-icon" style="background-image: url('<?php echo resolve_image_path($img_kuruyemis); ?>');"></div>
                    <span><i class="fa-solid fa-store"></i> <?php echo ($is_en ? ($settings['menu_kuruyemis_en'] ?? __('kuruyemis')) : ($settings['menu_kuruyemis_tr'] ?? __('kuruyemis'))); ?></span>
                </div>
                <?php endif; ?>
                <div class="category-item" onclick="app.navigateTo('cek_gonder')">
                    <div class="category-icon" style="background-image: url('<?php echo resolve_image_path($img_cek); ?>');"></div>
                    <span><i class="fa-solid fa-paper-plane"></i> <?php echo ($is_en ? ($settings['menu_cek_gonder_en'] ?? __('cek_gonder')) : ($settings['menu_cek_gonder_tr'] ?? __('cek_gonder'))); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('pharmacy')">
                    <div class="category-icon" style="background-image: url('<?php echo resolve_image_path($img_med); ?>');"></div>
                    <span><i class="fa-solid fa-hospital"></i> <?php echo ($is_en ? ($settings['menu_pharmacy_en'] ?? __('pharmacy_hospital')) : ($settings['menu_pharmacy_tr'] ?? __('pharmacy_hospital'))); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('hotel')">
                    <div class="category-icon" style="background-image: url('<?php echo resolve_image_path($img_hotel); ?>');"></div>
                    <span><i class="fa-solid fa-hotel"></i> <?php echo ($is_en ? ($settings['menu_hotels_en'] ?? __('hotels')) : ($settings['menu_hotels_tr'] ?? __('hotels'))); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('restaurant')">
                    <div class="category-icon" style="background-image: url('<?php echo resolve_image_path($img_rest); ?>');"></div>
                    <span><i class="fa-solid fa-utensils"></i> <?php echo ($is_en ? ($settings['menu_restaurants_en'] ?? __('restaurants')) : ($settings['menu_restaurants_tr'] ?? __('restaurants'))); ?></span>
                </div>
            </div>
        </section>
        <section id="events" class="tab-content section"><h2><?php echo __('event_tab'); ?></h2><div id="events-list"></div></section>
        <section id="services" class="tab-content section"><h2><?php echo __('service_tab'); ?></h2><div id="services-list"></div></section>
        <section id="profile" class="tab-content section"><h2><?php echo __('profile_tab'); ?></h2><div id="auth-panel"></div></section>
    </main>

    <?php include '../includes/bottom_nav.php'; ?>
    <?php include '../includes/auth_modal.php'; ?>
    
    <!-- Announcements Modal -->
    <div id="announcements-modal" class="modal">
        <div class="modal-content modal-fullscreen animate-in">
            <div class="modal-close" onclick="app.toggleAnnouncementsModal()">
                <i class="fa-solid fa-times"></i>
            </div>
            <h2><?php echo __('announcements'); ?></h2>
            <div class="modal-body" id="announcements-list-modal">
                <!-- Populated by JS -->
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/turkey_data.js"></script>
<script>
    // Adjust paths for app.js
    window.API_BASE = '../api/';
    window.ASSETS_BASE = '../assets/';
</script>
<script src="../assets/js/app.js?v=7.0"></script>
<script>
    app.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
</script>
</body>
</html>
