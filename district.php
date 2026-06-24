<?php
require_once 'config.php';

$stmt = $pdo->query("SELECT name, value FROM settings WHERE name LIKE 'menu_%'");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['name']] = $row['value'];
}
$is_en = ($current_lang === 'en');


$slug = isset($_GET['slug']) ? $_GET['slug'] : 'cermik';
$stmt = $pdo->prepare("SELECT * FROM districts WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$current_district = $stmt->fetch();

$is_en = ($current_lang === 'en');
$settings = get_settings($pdo, $current_district ? $current_district['id'] : 0);

if (!$current_district) {
    header("HTTP/1.0 404 Not Found");
    echo "İlçe bulunamadı.";
    exit;
}

// Redirect to specialized subdirectory if it exists
if (is_dir(__DIR__ . '/' . $slug)) {
    header("Location: " . $slug . "/");
    exit;
}
$district_id = $current_district['id'];
$_SESSION['district_id'] = $district_id; // Set session for APIs
setcookie('district_id', $district_id, time() + (86400 * 30), "/");

// Override mayor settings with district specific ones if available
if (!empty($current_district['mayor_image'])) $settings['mayor_image'] = $current_district['mayor_image'];
if (!empty($current_district['mayor_name'])) $settings['mayor_name'] = $current_district['mayor_name'];
if (!empty($current_district['mayor_title'])) $settings['mayor_title'] = $current_district['mayor_title'];
if (!empty($current_district['mayor_title_en'])) $settings['mayor_title_en'] = $current_district['mayor_title_en'];

// FEATURING: Custom Menus & Live Broadcasts
$stmt_cm = $pdo->prepare("SELECT * FROM custom_menus WHERE district_id = ? AND is_active = 1 ORDER BY sort_order ASC");
$stmt_cm->execute([$district_id]);
$custom_menus = $stmt_cm->fetchAll();

$stmt_lb = $pdo->prepare("SELECT * FROM live_broadcasts WHERE (district_id = ? OR district_id IS NULL OR district_id = 0) AND is_active = 1 ORDER BY sort_order ASC, id DESC");
$stmt_lb->execute([$district_id]);
$live_broadcasts = $stmt_lb->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $settings['site_name'] ?? 'Çermik Rehberi'; ?> - Akıllı Şehir Rehberi</title>
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Style -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body data-page-context="Genel Çermik Rehberi" data-district-slug="<?php echo $slug; ?>" data-select-city="<?php echo __('select_city_placeholder'); ?>" data-select-district="<?php echo __('select_district_placeholder'); ?>">

<?php include 'includes/theme_bg.php'; ?>

<div id="app">
    <!-- Sidebar Overlay & Menu -->
    <div class="sidebar-overlay" onclick="app.toggleSidebar()"></div>
    <div class="sidebar-menu" id="sidebar-menu">
        <div class="sidebar-profile">
            <div class="mayor-img-container">
                <img src="<?php echo htmlspecialchars($settings['mayor_image'] ?? 'assets/img/mayor/baskan.png'); ?>" alt="Belediye Başkanı" onerror="this.style.display='none'">
            </div>
            <div class="mayor-info">
                <h3><?php echo htmlspecialchars($settings['mayor_name'] ?? 'Başkan'); ?></h3>
                <span><?php echo htmlspecialchars($is_en ? ($settings['mayor_title_en'] ?? $settings['mayor_title'] ?? 'Mayor') : ($settings['mayor_title'] ?? 'Belediye Başkanı')); ?></span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="index.php" class="sidebar-item">
                <i class="fa-solid fa-house"></i>
                <span><?php echo __('home'); ?></span>
            </a>
            
            <div class="sidebar-divider"></div>
            <div class="sidebar-section-title"><?php echo __('belediyemiz'); ?></div>
            
            <a href="#" class="sidebar-item" onclick="app.switchTab('services', this); app.toggleSidebar();">
                <i class="fa-solid fa-hand-holding-heart"></i>
                <span><?php echo __('service_tab'); ?></span>
            </a>
            <a href="#" class="sidebar-item" onclick="app.toggleAnnouncementsModal(); app.toggleSidebar();">
                <i class="fa-solid fa-bullhorn"></i>
                <span><?php echo __('announcements'); ?></span>
            </a>
            <!-- Bize Yazın removed -->
            
            <div class="sidebar-divider"></div>
            <div class="sidebar-section-title"><?php echo __('belediye_rehberi'); ?></div>
            <div id="municipal-guide-menu">
                <!-- Populated by JS -->
                <div style="padding: 10px 25px; font-size: 0.8rem; color: var(--text-secondary);"><?php echo __('loading'); ?>...</div>
            </div>

            <div class="sidebar-divider"></div>
            <div class="sidebar-section-title"><?php echo __('change_lang'); ?></div>
            <div style="padding: 10px 25px; display: flex; gap: 10px;">
                <a href="?lang=tr" style="flex: 1; padding: 10px; background: <?php echo $current_lang == 'tr' ? 'var(--secondary)' : 'rgba(255,255,255,0.05)'; ?>; border-radius: 10px; text-decoration: none; color: <?php echo $current_lang == 'tr' ? 'white' : 'var(--text-secondary)'; ?>; text-align: center; border: 1px solid rgba(255,255,255,0.1); font-size: 0.8rem; font-weight: 600;">
                    TR
                </a>
                <a href="?lang=en" style="flex: 1; padding: 10px; background: <?php echo $current_lang == 'en' ? 'var(--secondary)' : 'rgba(255,255,255,0.05)'; ?>; border-radius: 10px; text-decoration: none; color: <?php echo $current_lang == 'en' ? 'white' : 'var(--text-secondary)'; ?>; text-align: center; border: 1px solid rgba(255,255,255,0.1); font-size: 0.8rem; font-weight: 600;">
                    EN
                </a>
            </div>
        </nav>
        
        <div style="padding: 20px; margin-top: auto;">
            <p style="font-size: 0.7rem; text-align: center; opacity: 0.5;"><?php echo $settings['site_name'] ?? 'Çermik Belediye Başkanlığı'; ?><br>© 2026</p>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="header-icons">
            <!-- Hava Durumu Widget -->
            <div id="weather-widget" title="Çermik Anlık Hava Durumu">
                <i class="fa-solid fa-cloud" id="weather-icon"></i>
                <span id="weather-temp">--°</span>
            </div>
            <i class="fa-solid fa-bell" id="bell-icon"></i>
        </div>
        <a href="index.php" class="header-title-wrap" style="text-decoration: none; color: inherit;">
            <img src="<?php echo htmlspecialchars($slug); ?>/assets/logo.png" 
                 alt="Belediyesi Logo" 
                 class="header-logo" 
                 id="site-logo"
                 onerror="this.src='<?php echo htmlspecialchars($settings['site_logo'] ?? 'assets/img/logo/logo.png'); ?>'; this.onerror=null;">
            <h1 id="site-header-title"><?php echo htmlspecialchars($settings['site_name'] ?? $current_district['name']); ?></h1>
        </a>
        <div class="sidebar-toggle" onclick="app.toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </div>
    </header>

    <!-- Main Content Tabs -->
    <main id="main-content">
        <section id="explore" class="tab-content active section animate-in">
            <!-- LIVE BROADCASTS & CUSTOM MENUS (FULL WIDTH HERO STYLE) -->
            <?php foreach($live_broadcasts as $lb): 
                $lb_title = ($is_en && !empty($lb['title_en'])) ? $lb['title_en'] : $lb['title'];
                $lb_img = !empty($lb['image']) ? $lb['image'] : 'assets/img/og-default.jpg';
                $lb_url = 'live.php?id=' . $lb['id'] . '&slug=' . $slug;
            ?>
            <div class="category-item hero-banner" 
                 onclick="window.location.href='<?php echo htmlspecialchars($lb_url); ?>'" 
                 style="width: 100%; height: 160px; position: relative; overflow: hidden; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); margin-bottom: 20px;"
                 data-lat="<?php echo $lb['lat']; ?>" 
                 data-lng="<?php echo $lb['lng']; ?>" 
                 data-id="<?php echo $lb['id']; ?>" 
                 data-type="live">
                <div class="category-icon" style="background-image: url('<?php echo htmlspecialchars($lb_img); ?>'); width: 100%; height: 100%; background-size: cover; background-position: center; filter: brightness(0.6);"></div>
                <div style="position: absolute; top:15px; right:15px; z-index: 2; display: flex; align-items: center; gap: 5px; background: #ff4757; color: white; padding: 4px 10px; border-radius: 5px; font-size: 0.75rem; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                    <i class="fa-solid fa-circle" style="font-size: 0.4rem; animation: pulse 2s infinite;"></i> CANLI
                </div>
                <div style="position: absolute; top:15px; left:15px; z-index: 2; display: flex; align-items: center; gap: 4px; background: rgba(0,0,0,0.2); color: rgba(255,255,255,0.7); padding: 2px 5px; border-radius: 8px; font-size: 0.45rem; font-weight: 500; backdrop-filter: blur(2px);">
                    <span class="distance-info"></span>
                </div>
                <div style="position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: white; padding: 20px;">
                    <i class="fa-solid fa-play-circle" style="font-size: 2.5rem; opacity: 0.8; margin-bottom: 10px; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"></i>
                    <h3 style="font-size: 1.4rem; margin-bottom: 5px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo htmlspecialchars($lb_title); ?></h3>
                </div>
            </div>
            <?php endforeach; ?>

            <?php foreach($custom_menus as $cm): 
                $cm_title = ($is_en && !empty($cm['name_en'])) ? $cm['name_en'] : $cm['name_tr'];
                if(empty($cm_title)) $cm_title = $cm['menu_title'] ?? '';
                $cm_img = !empty($cm['image']) ? $cm['image'] : '';
                $cm_url = $cm['target_url'] ?? '#';
                $cm_icon = !empty($cm['icon']) ? $cm['icon'] : 'fa-link';
            ?>
            <div class="category-item hero-banner" 
                 onclick="<?php echo ($cm_url !== '#') ? "window.location.href='" . htmlspecialchars($cm_url) . "'" : ''; ?>" 
                 style="width: 100%; height: 160px; position: relative; overflow: hidden; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); margin-bottom: 20px;"
                 <?php if($cm['lat'] && $cm['lng']): ?>
                 data-lat="<?php echo $cm['lat']; ?>" 
                 data-lng="<?php echo $cm['lng']; ?>" 
                 data-id="<?php echo $cm['id']; ?>" 
                 data-type="custom_menu"
                 <?php endif; ?>>
                <div class="category-icon" style="<?php echo $cm_img ? "background-image: url('" . htmlspecialchars($cm_img) . "');" : "background: var(--primary-gradient);"; ?> width: 100%; height: 100%; background-size: cover; background-position: center; filter: brightness(0.6);"></div>
                <?php if($cm['lat'] && $cm['lng']): ?>
                <div style="position: absolute; bottom:8px; left:12px; z-index: 2; display: flex; align-items: center; gap: 4px; background: rgba(0,0,0,0.2); color: rgba(255,255,255,0.7); padding: 2px 6px; border-radius: 10px; font-size: 0.5rem; font-weight: 500; backdrop-filter: blur(2px);">
                    <span class="distance-info"></span>
                </div>
                <?php endif; ?>
                <div style="position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: white; padding: 20px;">
                    <i class="fa-solid <?php echo htmlspecialchars($cm_icon); ?>" style="font-size: 2.5rem; opacity: 0.8; margin-bottom: 10px; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"></i>
                    <h3 style="font-size: 1.4rem; margin-bottom: 5px; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo htmlspecialchars($cm_title); ?></h3>
                </div>
            </div>
            <?php endforeach; ?>

            <?php 
            $slogan = ($current_lang === 'en') 
                      ? ($settings['explore_desc_en'] ?? __('explore_desc')) 
                      : ($settings['explore_desc'] ?? __('explore_desc'));
            ?>
            <h2><?php echo $slogan; ?></h2>
            
            <?php 
            // Use dynamic banner settings if available, otherwise fallback to defaults based on district
            $f_title = ($is_en) ? ($settings['hero_title_en'] ?? '') : ($settings['hero_title_tr'] ?? '');
            $f_desc = ($is_en) ? ($settings['hero_desc_en'] ?? '') : ($settings['hero_desc_tr'] ?? '');
            $f_img = !empty($settings['hero_image']) ? $settings['hero_image'] : '';
            $f_target = !empty($settings['hero_target']) ? $settings['hero_target'] : '';
            
            // Default fallbacks if not set in admin
            if (empty($f_title)) {
                if ($slug === 'cermik') {
                    $f_title = __('hotspring_title');
                    $f_desc = $f_desc ?: __('hotspring_subtitle');
                    $f_img = $f_img ?: 'assets/img/categories/kaplica.jpg';
                    $f_target = 'hotspring';
                } elseif ($slug === 'cungus') {
                    $f_title = "Karakaya Barajı";
                    $f_desc = $f_desc ?: "Doğanın ve teknolojinin muhteşem buluşmasını keşfedin.";
                    $f_img = $f_img ?: 'assets/img/categories/historical.jpg';
                    $f_target = 'historical';
                } else {
                    $f_title = $current_district['name'];
                    $f_desc = $f_desc ?: ($is_en ? 'Explore the beauty of our district.' : 'İlçemizin güzelliklerini keşfedin.');
                    $f_img = $f_img ?: 'assets/img/categories/nature.jpg';
                    $f_target = 'historical';
                }
            }
            $f_target = $f_target ?: 'historical';
            ?>
            
            <div class="card animate-in" style="background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.5)), url('<?php echo $f_img; ?>'); background-size: cover; background-position: center; color: white; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; border: 1px solid rgba(255, 255, 255, 0.2); padding: 25px; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3); position: relative; overflow: hidden;" onclick="app.navigateTo('<?php echo $f_target; ?>')">
                <div style="z-index: 1;">
                    <h3 style="margin:0; font-size: 1.3rem; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo htmlspecialchars($f_title); ?></h3>
                    <p style="font-size: 0.9rem; opacity: 1; margin-top: 8px; font-weight: 500; text-shadow: 0 1px 2px rgba(0,0,0,0.5);"><?php echo htmlspecialchars($f_desc); ?></p>
                </div>
                <i class="fa-solid <?php echo ($f_target === 'hotspring' ? 'fa-hot-tub-person' : 'fa-info-circle'); ?>" style="font-size: 2.5rem; opacity: 0.8; z-index: 1; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"></i>
            </div>



            <div class="category-grid">
                <div class="category-item" onclick="app.navigateTo('historical')">
                    <div class="category-icon" style="background-image: url('assets/img/categories/historical.jpg');"></div>
                    <span><i class="fa-solid fa-landmark"></i> <?php echo htmlspecialchars($is_en ? ($settings['menu_historical_en'] ?? __('historical_places')) : ($settings['menu_historical_tr'] ?? __('historical_places'))); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('nature')">
                    <div class="category-icon" style="background-image: url('assets/img/categories/nature.jpg');"></div>
                    <span><i class="fa-solid fa-leaf"></i> <?php echo htmlspecialchars($is_en ? ($settings['menu_nature_en'] ?? __('nature_parks')) : ($settings['menu_nature_tr'] ?? __('nature_parks'))); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('parks_gardens')">
                    <div class="category-icon" style="background-image: url('assets/img/categories/parks.jpg');"></div>
                    <span><i class="fa-brands fa-pagelines"></i> <?php echo htmlspecialchars($is_en ? ($settings['menu_parks_en'] ?? __('park_bahce')) : ($settings['menu_parks_tr'] ?? __('park_bahce'))); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('pharmacy')">
                    <div class="category-icon" style="background-image: url('assets/img/categories/medical.jpg');"></div>
                    <span><i class="fa-solid fa-staff-snake"></i> <?php echo htmlspecialchars($is_en ? ($settings['menu_hospital_en'] ?? __('pharmacy_hospital')) : ($settings['menu_hospital_tr'] ?? __('pharmacy_hospital'))); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('restaurant')">
                    <div class="category-icon" style="background-image: url('assets/img/categories/restaurants.jpg');"></div>
                    <span><i class="fa-solid fa-utensils"></i> <?php echo htmlspecialchars($is_en ? ($settings['menu_businesses_en'] ?? __('restaurants')) : ($settings['menu_businesses_tr'] ?? __('restaurants'))); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('hotel')">
                    <div class="category-icon" style="background-image: url('assets/img/categories/hotels.jpg');"></div>
                    <span><i class="fa-solid fa-bed"></i> <?php echo __('hotels'); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('kuruyemis')">
                    <div class="category-icon" style="background-image: url('assets/img/categories/kuruyemis.jpg'); filter: brightness(1); object-fit: cover;"></div>
                    <span><i class="fa-solid fa-store"></i> <?php echo __('kuruyemis'); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('cek_gonder')">
                    <div class="category-icon" style="background-image: url('assets/img/categories/cek_gonder.jpg'); filter: brightness(1); object-fit: cover;"></div>
                    <span><i class="fa-solid fa-paper-plane"></i> <?php echo __('cek_gonder'); ?></span>
                </div>
            </div>
        </section>

        <!-- Etkinlikler Tab -->
        <section id="events" class="tab-content section">
            <h2><?php echo __('belediye_etkinlikleri'); ?></h2>
            <div id="events-list">
                <!-- Populated by JS -->
            </div>
        </section>

        <!-- Hizmetler Tab -->
        <section id="services" class="tab-content section">
            <div style="text-align: center; margin-bottom: 25px;">
                <h1 style="font-size: 1.5rem; margin-bottom: 5px; color: var(--primary);"><?php echo $settings['site_name'] ?? 'Çermik Belediye Başkanlığı'; ?></h1>
                <p style="font-size: 0.8rem; color: var(--text-secondary);"><?php echo __('prepared_by'); ?></p>
            </div>

            <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                <button class="btn project-tab active" onclick="app.filterProjects(1, this)" style="background: rgba(0,201,255,0.1); color: var(--secondary); border: 1px solid var(--secondary); flex: 1; padding: 10px;"><?php echo __('completed_projects'); ?></button>
                <button class="btn project-tab" onclick="app.filterProjects(0, this)" style="background: transparent; color: var(--text-secondary); border: 1px solid var(--glass-bg); flex: 1; padding: 10px;"><?php echo __('ongoing_projects'); ?></button>
            </div>

            <div id="services-list" class="animate-in">
                <!-- Populated by JS -->
            </div>
        </section>

        <!-- Profil Tab -->
        <section id="profile" class="tab-content section">
            <h2><?php echo __('my_profile'); ?></h2>
            <div id="auth-panel">
                <div class="card">
                    <h3><?php echo __('login'); ?></h3>
                    <p><?php echo __('auth_login_msg'); ?></p>
                    <button class="btn btn-primary" onclick="app.toggleAuthModal()" style="margin-bottom: 15px;"><?php echo __('login'); ?> / <?php echo __('register'); ?></button>
                    
                    <div style="display: flex; align-items: center; margin-bottom: 15px; width: 80%; margin-left:10%;">
                        <span style="flex:1; border-bottom: 1px solid rgba(255,255,255,0.1);"></span>
                        <span style="margin: 0 10px; font-size: 0.7rem; color: var(--text-secondary);"><?php echo __('or'); ?></span>
                        <span style="flex:1; border-bottom: 1px solid rgba(255,255,255,0.1);"></span>
                    </div>

                    <!-- Direct Google Login in Profile Tab -->
                    <div id="g_id_signin_profile" style="width: 100%; display: flex; justify-content: center; margin-bottom: 10px;"></div>
                    
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const checkGoogle = setInterval(() => {
                            if (typeof google !== 'undefined' && typeof initGoogleAuth === 'function') {
                                clearInterval(checkGoogle);
                                google.accounts.id.renderButton(
                                    document.getElementById("g_id_signin_profile"),
                                    { theme: "outline", size: "large", width: "250", text: "signin_with", shape: "pill" }
                                );
                            }
                        }, 500);
                    });
                    </script>
                </div>
            </div>

            <div class="card" style="border-top: 4px solid var(--secondary);">
                <h3><i class="fa-solid fa-phone-volume"></i> <?php echo __('important_phones'); ?></h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px;">
                    <a href="tel:155" style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 15px; text-decoration: none; color: white; display: flex; flex-direction: column; align-items: center; gap: 5px; border: 1px solid rgba(255,255,255,0.1);">
                        <i class="fa-solid fa-shield-halved" style="color: var(--secondary); font-size: 1.2rem;"></i>
                        <span style="font-size: 0.8rem; font-weight: 600;"><?php echo __('police'); ?></span>
                        <span style="font-size: 0.9rem; font-weight: 700;">155</span>
                    </a>
                    <a href="tel:153" style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 15px; text-decoration: none; color: white; display: flex; flex-direction: column; align-items: center; gap: 5px; border: 1px solid rgba(255,255,255,0.1);">
                        <i class="fa-solid fa-users-gear" style="color: var(--secondary); font-size: 1.2rem;"></i>
                        <span style="font-size: 0.8rem; font-weight: 600;"><?php echo __('municipal_police'); ?></span>
                        <span style="font-size: 0.9rem; font-weight: 700;">153</span>
                    </a>
                    <a href="tel:110" style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 15px; text-decoration: none; color: white; display: flex; flex-direction: column; align-items: center; gap: 5px; border: 1px solid rgba(255,255,255,0.1);">
                        <i class="fa-solid fa-fire-extinguisher" style="color: var(--secondary); font-size: 1.2rem;"></i>
                        <span style="font-size: 0.8rem; font-weight: 600;"><?php echo __('fire_department'); ?></span>
                        <span style="font-size: 0.9rem; font-weight: 700;">110</span>
                    </a>
                    <a href="tel:<?php echo str_replace(' ', '', $settings['site_phone'] ?? '04126116001'); ?>" style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 15px; text-decoration: none; color: white; display: flex; flex-direction: column; align-items: center; gap: 5px; border: 1px solid rgba(255,255,255,0.1);">
                        <i class="fa-solid fa-building-flag" style="color: var(--secondary); font-size: 1.2rem;"></i>
                        <span style="font-size: 0.8rem; font-weight: 600;">Belediye</span>
                        <span style="font-size: 0.9rem; font-weight: 700;"><?php echo $settings['site_phone'] ?? '611 60 01'; ?></span>
                    </a>
                </div>
            </div>

            <div class="card">
                <h3><i class="fa-solid fa-building"></i> <?php echo __('corporate'); ?></h3>
                <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
                    <a href="javascript:void(0)" onclick="app.showPolicy('kvkk')" style="color: var(--text-secondary); text-decoration: none; font-size: 0.9rem; padding: 10px; background: rgba(255,255,255,0.03); border-radius: 10px;"><i class="fa-solid fa-file-contract"></i> <?php echo __('kvkk_text_link'); ?></a>
                    <a href="javascript:void(0)" onclick="app.showPolicy('cookie')" style="color: var(--text-secondary); text-decoration: none; font-size: 0.9rem; padding: 10px; background: rgba(255,255,255,0.03); border-radius: 10px;"><i class="fa-solid fa-cookie-bite"></i> <?php echo __('cookie_policy_link'); ?></a>
                </div>
            </div>

            <!-- Hidden policy texts -->
            <div id="kvkk-content-data" style="display: none;"><?php echo nl2br(htmlspecialchars($settings['kvkk_text'] ?? '')); ?></div>
            <div id="cookie-content-data" style="display: none;"><?php echo nl2br(htmlspecialchars($settings['cookie_policy'] ?? '')); ?></div>
            <div id="policy-labels" style="display: none;" 
                 data-kvkk-title="<?php echo __('kvkk_text_link'); ?>" 
                 data-cookie-title="<?php echo __('cookie_policy_link'); ?>"></div>
        </section>
    </main>

    <!-- Bottom Navigation -->
    <?php include 'includes/bottom_nav.php'; ?>



    <?php include 'includes/auth_modal.php'; ?>

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
    <!-- Policy Modal -->
    <div id="policy-modal" class="modal">
        <div class="modal-content animate-in" style="max-height: 80vh; overflow-y: auto;">
            <div class="modal-close" onclick="app.togglePolicyModal()">
                <i class="fa-solid fa-times"></i>
            </div>
            <h2 id="policy-title">Politika</h2>
            <div id="policy-content" style="line-height: 1.6; color: var(--text-primary); margin-top: 15px;">
                <!-- Content will be injected -->
            </div>
        </div>
    </div>

    <!-- Footer Re-added -->
    <footer class="section" style="margin-top: 50px; border-top: 1px solid var(--glass-bg); padding-top: 40px; text-align: center;">
        
        <div class="social-links" style="font-size: 1.5rem; display: flex; justify-content: center; gap: 20px; margin-bottom: 20px;">
            <a href="<?php echo $settings['facebook_link'] ?? '#'; ?>" target="_blank" style="color: inherit;"><i class="fa-brands fa-facebook"></i></a>
            <a href="<?php echo $settings['instagram_link'] ?? '#'; ?>" target="_blank" style="color: inherit;"><i class="fa-brands fa-instagram"></i></a>
            <a href="<?php echo $settings['youtube_link'] ?? '#'; ?>" target="_blank" style="color: inherit;"><i class="fa-brands fa-youtube"></i></a>
            <a href="<?php echo $settings['twitter_link'] ?? '#'; ?>" target="_blank" style="color: inherit;"><i class="fa-brands fa-twitter"></i></a>
        </div>
        
        <p style="font-size: 0.7rem; color: var(--text-secondary);"><?php echo $settings['copyright_text'] ?? '© 2026 Çermik Rehberi. Tüm hakları saklıdır.'; ?></p>
    </footer>

    <!-- Scripts -->
    <script src="assets/js/turkey_data.js"></script>
    <script src="assets/js/app.js?v=7.0"></script>
</div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof app !== 'undefined') {
                app.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

                <?php if (!empty($_GET['login']) && !isset($_SESSION['user_id'])): ?>
                // Başka sayfadan giriş yap yönlendirmesi - modal aç
                setTimeout(function () {
                    if (typeof app.toggleAuthModal === 'function') {
                        app.toggleAuthModal();
                    }
                }, 300);
                <?php endif; ?>
            }
        });
    </script>
</body>
</html>
