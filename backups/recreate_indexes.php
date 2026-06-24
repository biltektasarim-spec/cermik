<?php
$dirs = [__DIR__ . '/cermik', __DIR__ . '/cungus'];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $slug = basename($dir);
    $name = ($slug == 'cermik') ? 'Çermik' : 'Çüngüş';
    
    $index_content = <<<EOD
<?php
require_once '../config.php';

\$slug = '$slug';
\$stmt = \$pdo->prepare("SELECT * FROM districts WHERE slug = ? AND is_active = 1");
\$stmt->execute([\$slug]);
\$current_district = \$stmt->fetch();

if (!\$current_district) {
    header("HTTP/1.0 404 Not Found");
    echo "İlçe bulunamadı.";
    exit;
}
\$district_id = \$current_district['id'];
\$_SESSION['district_id'] = \$district_id;
setcookie('district_id', \$district_id, time() + (86400 * 30), "/");

\$settings = [];
if (isset(\$pdo)) {
    \$stmt = \$pdo->query("SELECT name, value FROM settings");
    while (\$row = \$stmt->fetch()) { \$settings[\$row['name']] = \$row['value']; }
}
\$is_en = (isset(\$_SESSION['lang']) && \$_SESSION['lang'] === 'en');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo \$settings['site_name'] ?? '$name Rehberi'; ?> - ROTAMIZ</title>
    <script>const GOOGLE_CLIENT_ID = "<?php echo GOOGLE_CLIENT_ID; ?>";</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body data-page-context="Genel $name Rehberi" data-district-slug="$slug">

<?php include '../includes/theme_bg.php'; ?>

<div id="app">
    <div class="sidebar-overlay" onclick="app.toggleSidebar()"></div>
    <div class="sidebar-menu" id="sidebar-menu">
        <div class="sidebar-profile">
            <div class="mayor-img-container">
                <img src="../<?php echo \$settings['mayor_image'] ?? 'assets/img/mayor/baskan.png'; ?>" alt="Baskan" onerror="this.style.display='none'">
            </div>
            <div class="profile-info">
                <h3><?php echo \$settings['mayor_name'] ?? 'Başkan'; ?></h3>
                <span><?php echo \$settings['mayor_title'] ?? 'Belediye Başkanı'; ?></span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="../index.php" class="sidebar-item">
                <i class="fa-solid fa-house"></i>
                <span><?php echo __('home'); ?></span>
            </a>
            <div class="sidebar-divider"></div>
            <a href="#" class="sidebar-item" onclick="app.switchTab('services', this); app.toggleSidebar();">
                <i class="fa-solid fa-hand-holding-heart"></i>
                <span><?php echo __('service_tab'); ?></span>
            </a>
            <a href="#" class="sidebar-item" onclick="app.toggleAnnouncementsModal(); app.toggleSidebar();">
                <i class="fa-solid fa-bullhorn"></i>
                <span><?php echo __('announcements'); ?></span>
            </a>
        </nav>
    </div>

    <header class="header">
        <div class="sidebar-toggle" onclick="app.toggleSidebar()"><i class="fa-solid fa-bars"></i></div>
        <div class="header-title-wrap">
            <img src="../assets/img/logo/logo.png" class="header-logo" alt="Logo" onerror="this.style.display='none'">
            <h1><?php echo \$current_district['name']; ?></h1>
        </div>
        <div class="header-icons">
            <div id="weather-widget">
                <i class="fa-solid fa-cloud" id="weather-icon"></i>
                <span id="weather-temp">--°</span>
            </div>
            <i class="fa-solid fa-bell" id="bell-icon"></i>
        </div>
    </header>

    <main id="main-content">
        <section id="explore" class="tab-content active section animate-in">
            <h2>İlçeyi Keşfedin</h2>

            <div class="card animate-in" style="background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.5)), url('../assets/img/categories/kaplica.jpg'); background-size: cover; background-position: center; color: white; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; border: 1px solid rgba(255, 255, 255, 0.2); padding: 25px; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3); position: relative; overflow: hidden;" onclick="app.navigateTo('hotspring')">
                <div style="z-index: 1;">
                    <h3 style="margin:0; font-size: 1.3rem; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo __('hotspring_title'); ?></h3>
                    <p style="font-size: 0.9rem; opacity: 1; margin-top: 8px; font-weight: 500; text-shadow: 0 1px 2px rgba(0,0,0,0.5);"><?php echo __('hotspring_subtitle'); ?></p>
                </div>
                <i class="fa-solid fa-hot-tub-person" style="font-size: 2.5rem; opacity: 0.8; z-index: 1; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"></i>
            </div>

            <div class="category-grid">
                <div class="category-item" onclick="app.navigateTo('historical')">
                    <div class="category-icon" style="background-image: url('../assets/img/categories/historical.jpg');"></div>
                    <span><i class="fa-solid fa-landmark"></i> <?php echo htmlspecialchars(\$is_en ? (\$settings['menu_historical_en'] ?? __('historical_places')) : (\$settings['menu_historical_tr'] ?? __('historical_places'))); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('nature')">
                    <div class="category-icon" style="background-image: url('../assets/img/categories/nature.jpg');"></div>
                    <span><i class="fa-solid fa-leaf"></i> <?php echo htmlspecialchars(\$is_en ? (\$settings['menu_nature_en'] ?? __('nature_parks')) : (\$settings['menu_nature_tr'] ?? __('nature_parks'))); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('parks_gardens')">
                    <div class="category-icon" style="background-image: url('../assets/img/categories/parks.jpg');"></div>
                    <span><i class="fa-brands fa-pagelines"></i> <?php echo htmlspecialchars(\$is_en ? (\$settings['menu_parks_en'] ?? __('park_bahce')) : (\$settings['menu_parks_tr'] ?? __('park_bahce'))); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('pharmacy')">
                    <div class="category-icon" style="background-image: url('../assets/img/categories/medical.jpg');"></div>
                    <span><i class="fa-solid fa-staff-snake"></i> <?php echo htmlspecialchars(\$is_en ? (\$settings['menu_hospital_en'] ?? __('pharmacy_hospital')) : (\$settings['menu_hospital_tr'] ?? __('pharmacy_hospital'))); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('restaurant')">
                    <div class="category-icon" style="background-image: url('../assets/img/categories/restaurants.jpg');"></div>
                    <span><i class="fa-solid fa-utensils"></i> <?php echo htmlspecialchars(\$is_en ? (\$settings['menu_businesses_en'] ?? __('restaurants')) : (\$settings['menu_businesses_tr'] ?? __('restaurants'))); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('hotel')">
                    <div class="category-icon" style="background-image: url('../assets/img/categories/hotels.jpg');"></div>
                    <span><i class="fa-solid fa-bed"></i> <?php echo __('hotels'); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('kuruyemis')">
                    <div class="category-icon" style="background-image: url('../assets/img/categories/kuruyemis.jpg');"></div>
                    <span><i class="fa-solid fa-store"></i> <?php echo __('kuruyemis'); ?></span>
                </div>
                <div class="category-item" onclick="app.navigateTo('cek_gonder')">
                    <div class="category-icon" style="background-image: url('../assets/img/categories/cek_gonder.jpg');"></div>
                    <span><i class="fa-solid fa-paper-plane"></i> <?php echo __('cek_gonder'); ?></span>
                </div>
            </div>
        </section>
        <section id="events" class="tab-content section"><h2>Etkinlikler</h2><div id="events-list"></div></section>
        <section id="services" class="tab-content section"><h2>Hizmetler</h2><div id="services-list"></div></section>
        <section id="profile" class="tab-content section"><h2>Profil</h2><div id="auth-panel"></div></section>
    </main>

    <?php include '../includes/bottom_nav.php'; ?>
    <?php include '../includes/auth_modal.php'; ?>
</div>

<script src="../assets/js/turkey_data.js"></script>
<script>
    window.API_BASE = '../api/';
    window.ASSETS_BASE = '../assets/';
</script>
<script src="../assets/js/app.js?v=7.0"></script>
<script>
    app.isLoggedIn = <?php echo isset(\$_SESSION['user_id']) ? 'true' : 'false'; ?>;
</script>
</body>
</html>
EOD;
    file_put_contents(\$dir . '/index.php', \$index_content);
    echo "Recreated index.php for \$slug\n";
}
echo "Done.\n";
unlink(__FILE__);
?>
