<?php 
require_once '../config.php'; 
$district_id = 3; 
$settings = get_settings($pdo, $district_id);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kaplıca verisini çek (Çermik için)
$stmt = $pdo->prepare("SELECT * FROM places WHERE category = 'HotSpring' AND district_id = 3 LIMIT 1");
$stmt->execute();
$h = $stmt->fetch();

if ($h && $_SESSION['lang'] === 'en') {
    if (!empty($h['name_en'])) $h['name'] = $h['name_en'];
    if (!empty($h['description_en'])) $h['description'] = $h['description_en'];
    if (!empty($h['hastaliklar_en'])) $h['hastaliklar'] = $h['hastaliklar_en'];
    if (!empty($h['slogan_en'])) $h['slogan'] = $h['slogan_en'];
    if (!empty($h['heading_hastaliklar_en'])) $h['heading_hastaliklar_tr'] = $h['heading_hastaliklar_en'];
}

$sec_heading = (!empty($h['heading_hastaliklar_tr'])) ? htmlspecialchars($h['heading_hastaliklar_tr']) : __('diseases_well_for');

$display_title = $h ? htmlspecialchars($h['name']) : __('hotspring_title');
$display_subtitle = ($h && !empty($h['slogan'])) ? htmlspecialchars($h['slogan']) : __('kaplica_subtitle');

function resolve_image_path($path) {
    if (!$path) return '';
    if (strpos($path, 'http') === 0) return $path;
    if (strpos($path, 'assets/') === 0 || strpos($path, 'uploads/') === 0) {
        if (file_exists(__DIR__ . '/' . $path)) return $path;
        return '../' . $path;
    }
    return $path;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $display_title; ?> - <?php echo __('belediye_rehberi'); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .kaplica-header { height: 300px; background-size: cover; background-position: center !important; display: flex; flex-direction: column; justify-content: flex-end; padding: 20px; }
        .info-menu { display: flex; flex-direction: column; gap: 15px; padding: 20px; }
        .info-item { background: var(--card-bg); padding: 20px; border-radius: var(--radius); display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(255,255,255,0.05); }
    </style>
    <!-- Pannellum (360 Viewer) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css"/>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
    <!-- Swiper (Image Slider) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <!-- Leaflet (Map) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body data-page-context="<?php echo $display_title; ?>" data-district-slug="cermik">
<?php include '../includes/theme_bg.php'; ?>
<div id="app">
    <header class="header">
        <a href="index.php" class="home-link"><i class="fa-solid fa-house"></i> <?php echo __('home'); ?></a>
        <h1><?php echo $settings['site_name'] ?? 'Çermik Belediyesi'; ?></h1>
    </header>

    <?php if ($h && $h['panorama_360']): ?>
        <?php if (strpos($h['panorama_360'], 'insta360.com') !== false): ?>
            <!-- Insta360 Iframe Embed -->
            <div style="height: 400px; overflow: hidden; position: relative; background: url('<?php echo htmlspecialchars(resolve_image_path($h['image_main'] ?? 'assets/img/categories/kaplica.jpg')); ?>') no-repeat center center; background-size: cover;">
                <?php 
                $p360 = $h['panorama_360'];
                $separator = (strpos($p360, '?') === false) ? '?' : '&';
                $embed_url = $p360 . $separator . "help=0&gui=0&brand=0&title=0&share=0&logo=0";
                ?>
                <iframe src="<?php echo $embed_url; ?>" width="100%" height="550px" frameborder="0" allowfullscreen allow="accelerometer; gyroscope; magnetometer; vr" style="margin-top: -140px; border: none; background: transparent;"></iframe>
                <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 20px; background: linear-gradient(transparent, rgba(10,14,20,1)); pointer-events: none;">
                    <h1 style="margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo $display_title; ?></h1>
                    <p style="margin: 5px 0 0; text-shadow: 0 1px 2px rgba(0,0,0,0.5);"><?php echo $display_subtitle; ?></p>
                </div>
            </div>
        <?php else: ?>
            <!-- Pannellum Viewer -->
            <div class="kaplica-header" style="height: 450px; padding: 0; position: relative; overflow: hidden;">
                <div id="panorama-header" style="width: 100%; height: 100%;"></div>
                <script>
                    pannellum.viewer('panorama-header', { "type": "equirectangular", "panorama": "<?php echo resolve_image_path($h['panorama_360']); ?>", "autoLoad": true, "autoRotate": -2 });
                </script>
                <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 20px; background: linear-gradient(transparent, rgba(10,14,20,1)); pointer-events: none;">
                    <h1 style="margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo $display_title; ?></h1>
                    <p style="margin: 5px 0 0; text-shadow: 0 1px 2px rgba(0,0,0,0.5);"><?php echo $display_subtitle; ?></p>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="kaplica-header" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.8)), url('<?php echo htmlspecialchars(resolve_image_path($h['image_main'] ?? 'assets/img/categories/kaplica.jpg')); ?>');">
            <h1><?php echo $display_title; ?></h1>
            <p><?php echo $display_subtitle; ?></p>
        </div>
    <?php endif; ?>

    <div class="info-menu">
        <?php if ($h): ?>
            <div class="card animate-in" style="margin-bottom: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                    <div>
                        <h2 style="margin: 0; color: var(--secondary);"><i class="fa-solid fa-hot-tub-person"></i> <?php echo htmlspecialchars($h['name']); ?></h2>
                        <div style="display: flex; gap: 10px; font-size: 0.85rem; color: var(--text-secondary); margin-top: 8px;" data-lat="<?php echo $h['lat']; ?>" data-lng="<?php echo $h['lng']; ?>" data-id="<?php echo $h['id']; ?>" data-type="place" data-category="<?php echo $h['category']; ?>">
                            <span class="distance-info" style="color: var(--secondary); font-weight: 600;"></span>
                            <span style="opacity: 0.5;">|</span>
                            <span><i class="fa-solid fa-fire" style="color: #ff9800;"></i> <?php echo (int)$h['popular_score']; ?> <?php echo __('popularity'); ?></span>
                        </div>
                    </div>
                    <button class="btn btn-primary" style="padding: 10px 20px;" onclick="window.open('https://maps.google.com?q=<?php echo $h['lat']; ?>,<?php echo $h['lng']; ?>')"><i class="fa-solid fa-location-arrow"></i> <?php echo __('directions'); ?></button>
                </div>
                <?php $gallery = json_decode($h['image_gallery'], true); if ($gallery && is_array($gallery)): ?>
                    <div class="swiper mySwiper" style="height: 200px; border-radius: 15px; margin-bottom: 15px;">
                        <div class="swiper-wrapper">
                            <?php foreach ($gallery as $img): ?>
                                <div class="swiper-slide" style="background-image: url('<?php echo resolve_image_path($img); ?>'); background-size: cover; background-position: center; border-radius: 15px;"></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                <?php endif; ?>
                <div style="background: rgba(255,255,255,0.03); padding: 15px; border-radius: 10px; margin-top: 15px;">
                    <h3 style="margin-bottom: 10px; color: #ffad33; border-bottom: 1px solid rgba(255,173,51,0.2); padding-bottom: 5px;"><i class="fa-solid fa-book-history"></i> <?php echo __('history_and_info'); ?></h3>
                    <div class="collapsible-wrapper">
                        <div class="collapsible-content"><p style="font-size: 1rem; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($h['description'])); ?></p></div>
                        <?php if (isset($h['description']) && strlen($h['description']) > 200): ?><button class="read-more-btn" onclick="app.toggleCollapsible(this)"><span><?php echo __('read_more'); ?></span> <i class="fa-solid fa-chevron-down"></i></button><?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card animate-in" style="margin-top: 25px; border-top: 4px solid #ff6666;">
                <h3 style="margin-bottom: 15px; color: #ff6666;"><i class="fa-solid fa-briefcase-medical"></i> <?php echo $sec_heading; ?></h3>
                <div class="collapsible-wrapper">
                    <div class="collapsible-content" style="background: rgba(255,102,102,0.1); padding: 20px; border-radius: 12px; font-size: 1.1rem; line-height: 1.6; color: #ff8888;">
                        <?php echo $h['hastaliklar'] ? nl2br(htmlspecialchars($h['hastaliklar'])) : __('no_info_entered'); ?>
                    </div>
                    <?php if (isset($h['hastaliklar']) && strlen($h['hastaliklar']) > 150): ?><button class="read-more-btn" onclick="app.toggleCollapsible(this)"><span><?php echo __('read_more'); ?></span> <i class="fa-solid fa-chevron-down"></i></button><?php endif; ?>
                </div>
            </div>

            <div class="card" style="margin-top: 20px;">
                <h3><i class="fa-solid fa-location-dot"></i> <?php echo __('location_info'); ?></h3>
                <div id="map" style="height: 200px; border-radius: 15px; margin-top: 15px;"></div>
                <div class="card animate-in" style="margin-top: 20px; text-align: center; background: transparent; border: none; padding: 0;" data-lat="<?php echo $h['lat']; ?>" data-lng="<?php echo $h['lng']; ?>" data-id="<?php echo $h['id']; ?>" data-type="place" data-category="<?php echo $h['category']; ?>">
                    <span class="distance-info" style="font-weight: bold; color: var(--secondary); font-size: 1.1rem;">
                        <i class="fa-solid fa-spinner fa-spin"></i> <?php echo __('calculating_distance'); ?>
                    </span>
                </div>
                <br>
                <button class="btn btn-primary" style="width: 100%;" onclick="window.open('https://www.google.com/maps?q=<?php echo $h['lat']; ?>,<?php echo $h['lng']; ?>')"><?php echo __('get_directions'); ?></button>
            </div>

            <?php 
            $widget_lat = $h['lat'];
            $widget_lng = $h['lng'];
            $widget_name = $display_title;
            include '../includes/traffic_widget.php';
            ?>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var lat = <?php echo $h['lat'] ?? 0; ?>;
                    var lng = <?php echo $h['lng'] ?? 0; ?>;
                    if (lat && lng) {
                        var map = L.map('map').setView([lat, lng], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                        L.marker([lat, lng]).addTo(map);
                    } else {
                        document.getElementById('map').innerHTML = '<p style="text-align:center; padding: 20px;"><?php echo __('no_location_info'); ?></p>';
                    }
                });
            </script>
        <?php else: ?>
            <div class="card animate-in"><p><?php echo __('kaplica_load_error'); ?></p></div>
        <?php endif; ?>
    </div>
    <script src="../assets/js/app.js?v=7.1"></script>
    <script>app.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>; new Swiper(".mySwiper", { pagination: { el: ".swiper-pagination", clickable: true } });</script>
</div>
<?php include '../includes/bottom_nav.php'; ?>
</body>
</html>
