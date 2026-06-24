<?php
require_once '../config.php';
$id = $_GET['id'] ?? 0;
// İşletme bilgilerini çek
$stmt = $pdo->prepare("SELECT b.*, d.slug as district_slug FROM businesses b JOIN districts d ON b.district_id = d.id WHERE b.id = ?");
$stmt->execute([$id]);
$business = $stmt->fetch();

if (!$business) {
    header("Location: index.php");
    exit;
}

// Ürünleri/Menüyü Getir
$stmt = $pdo->prepare("SELECT * FROM products WHERE business_id = ?");
$stmt->execute([$id]);
$products = $stmt->fetchAll();

$is_en = ($current_lang === 'en');
$title = ($is_en && !empty($business['business_name_en'])) ? $business['business_name_en'] : $business['business_name'];
    $display_desc = ($is_en && !empty($business['description_en'])) ? $business['description_en'] : $business['description'];

    function resolve_image_path($path) {
        if (!$path) return '';
        if (strpos($path, 'http') === 0) return $path;
        if (strpos($path, 'assets/') === 0 || strpos($path, 'uploads/') === 0) {
            // Yerel klasörde (cungus/assets veya cungus/uploads) varsa direkt kullan
            if (file_exists(__DIR__ . '/' . $path)) {
                return $path;
            }
            // Yoksa bir üst dizinden (root) çek
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
    <?php include '../includes/pwa_meta.php'; ?>
    <title>Çermik Rehberi - <?php echo $title; ?> - <?php echo __('belediye_rehberi'); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .back-btn {
            position: absolute; top: 20px; left: 20px; z-index: 10;
            background: rgba(0,0,0,0.4); width: 40px; height: 44px; 
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; color: white !important; font-size: 1.2rem;
            backdrop-filter: blur(5px);
        }
        .business-header {
            height: 250px; background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.6)), url('https://via.placeholder.com/600x400?text=<?php echo urlencode($title); ?>');
            background-size: cover; background-position: center; border-radius: 0 0 30px 30px;
            display: flex; flex-direction: column; justify-content: flex-end; padding: 20px; color: white;
        }
        /* Çalışma Saatleri */
        .status-pill {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 16px; border-radius: 30px; font-weight: 800;
            font-size: 0.9rem; letter-spacing: 0.5px;
        }
        .pill-open   { background: rgba(34,197,94,0.18); color: #4ade80; border: 1.5px solid rgba(34,197,94,0.45); }
        .pill-closed { background: rgba(239,68,68,0.18);  color: #f87171; border: 1.5px solid rgba(239,68,68,0.45); }
        .dot-live { width: 9px; height: 9px; border-radius: 50%; display: inline-block; }
        .dot-open   { background: #4ade80; box-shadow: 0 0 6px #4ade80; animation: pulse-green 1.5s infinite; }
        .dot-closed { background: #f87171; }
        @keyframes pulse-green { 0%,100%{opacity:1} 50%{opacity:0.4} }
        .week-table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 0.85rem; }
        .week-table td { padding: 7px 10px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .week-table tr:last-child td { border-bottom: none; }
        .week-table .day-name { font-weight: 600; opacity: 0.85; }
        .week-table .today-row td { background: rgba(255,255,255,0.07); border-radius: 8px; }
        .tag-open   { color: #4ade80; font-weight: 700; }
        .tag-closed-day { color: #f87171; font-weight: 600; font-size: 0.8rem; }
    </style>
</head>
<body data-page-context="<?php echo $title; ?>" data-district-slug="<?php echo $business['district_slug']; ?>" data-business-id="<?php echo $id; ?>">
<?php include '../includes/splash_screen.php'; ?>
<?php include '../includes/theme_bg.php'; ?>
<div id="app">
    <header class="header">
        <a href="index.php" class="home-link">
            <i class="fa-solid fa-house"></i> <?php echo __('home'); ?>
        </a>
        <h1><?php echo __('business_detail_title'); ?></h1>
    </header>

    <div style="position: relative; overflow: hidden;">
        <a href="javascript:history.back()" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
        
        <?php if ($business['panorama_360']): ?>
            <?php if (strpos($business['panorama_360'], 'insta360.com') !== false): ?>
                <!-- Insta360 Iframe Embed -->
                <div style="height: 400px; overflow: hidden; position: relative;">
                    <?php 
                    $p360 = $business['panorama_360'];
                    $separator = (strpos($p360, '?') === false) ? '?' : '&';
                    $embed_url = $p360 . $separator . "help=0&gui=0&brand=0&title=0&share=0&logo=0";
                    ?>
                    <iframe src="<?php echo $embed_url; ?>" width="100%" height="550px" frameborder="0" allowfullscreen allow="accelerometer; gyroscope; magnetometer; vr" style="margin-top: -140px; border: none;"></iframe>
                    <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 20px; background: linear-gradient(transparent, var(--app-bg)); pointer-events: none;">
                        <h1 style="margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo $title; ?></h1>
                        <p style="opacity: 0.9; text-shadow: 0 1px 2px rgba(0,0,0,0.5);"><i class="fa-solid fa-tag"></i> <?php echo $business['category'] == 'Restaurant' ? 'Restoran / Kafe' : 'Otel / Konaklama'; ?></p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Pannellum Viewer (Local 360) -->
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css"/>
                <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
                <div style="height: 400px; position: relative; overflow: hidden;">
                    <div id="panorama" style="width: 100%; height: 100%;"></div>
                    <script>
                        pannellum.viewer('panorama', {
                            "type": "equirectangular",
                            "panorama": "<?php echo $business['panorama_360']; ?>",
                            "autoLoad": true,
                            "compass": true,
                            "text": "<?php echo $title; ?>"
                        });
                    </script>
                    <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 20px; background: linear-gradient(transparent, var(--app-bg)); pointer-events: none;">
                        <h1 style="margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo $title; ?></h1>
                        <p style="opacity: 0.9; text-shadow: 0 1px 2px rgba(0,0,0,0.5);"><i class="fa-solid fa-tag"></i> <?php echo $business['category'] == 'Restaurant' ? 'Restoran / Kafe' : 'Otel / Konaklama'; ?></p>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="business-header" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.6)), url('<?php echo resolve_image_path($business['image_main'] ?: 'assets/img/categories/hotels_bg.jpg'); ?>');">
                <h1 style="margin: 0;"><?php echo $title; ?></h1>
                <p style="opacity: 0.9;"><i class="fa-solid fa-tag"></i> <?php echo $business['category'] == 'Restaurant' ? __('restaurant_cafe') : __('hotel_accommodation'); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <main class="section animate-in">
        <div class="card">
            <h3><?php echo __('about'); ?></h3>
            <p><?php echo nl2br(htmlspecialchars($display_desc ?: ($business['description'] ?? ''))); ?></p>
            
            <?php if (!empty($business['phone'])): ?>
                <div style="margin-top: 15px; display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.05); padding: 12px; border-radius: 12px;">
                    <i class="fa-solid fa-phone" style="color: var(--secondary);"></i>
                    <a href="tel:<?php echo htmlspecialchars($business['phone']); ?>" style="color: #fff; text-decoration: none; font-weight: 600;">
                        <?php echo htmlspecialchars($business['phone']); ?>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($business['order_enabled'] && !empty($business['order_link'])): ?>
                <div style="margin-top: 15px;">
                    <a href="<?php echo htmlspecialchars($business['order_link']); ?>" target="_blank" class="btn btn-order" style="background: linear-gradient(45deg, #ff5722, #ff9800); color: #fff; font-weight: 700; width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px; border-radius: 12px; text-decoration: none; box-shadow: 0 4px 15px rgba(255, 87, 34, 0.3);">
                        <i class="fa-solid fa-shopping-basket"></i> <?php echo __('order_now_btn') ?? 'Sipariş Ver'; ?>
                    </a>
                </div>
            <?php endif; ?>


            <?php 
            $is_hotel_res = ($business['category'] == 'Hotel' || $business['category'] == 'Restaurant' || $business['category'] == 'Otel' || $business['category'] == 'Restoran');
            if (!$is_hotel_res): 
            ?>
            <div style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
                <button id="checkin-btn" class="btn btn-primary" onclick="app.handleCheckIn(<?php echo $business['id']; ?>, 'business')" style="background: linear-gradient(45deg, #00c9ff, #92fe9d); color: #000; font-weight: 700; width: 100%;">
                    <i class="fa-solid fa-location-crosshairs"></i> <?php echo __('check_in_btn'); ?>
                </button>
            </div>
            <?php endif; ?>
        </div>

        <?php
        // ── Çalışma Saatleri Kartı ──────────────────────────────────
        $wh_data = json_decode($business['working_hours'] ?? '{}', true) ?: [];
        $has_wh  = !empty($wh_data) && isset($wh_data['days'], $wh_data['open'], $wh_data['close']);

        if ($has_wh):
            // Türkiye saati (UTC+3) - gmdate ile güvenilir hesaplama
            $now_ts    = time() + (3 * 3600);
            $now_day   = (int)gmdate('w', $now_ts); // 0=Pazar, 1=Pzt...6=Cmt
            $now_min   = (int)gmdate('H', $now_ts) * 60 + (int)gmdate('i', $now_ts);

            $op_parts  = explode(':', $wh_data['open']);
            $cl_parts  = explode(':', $wh_data['close']);
            $open_min  = (int)$op_parts[0] * 60 + (int)($op_parts[1] ?? 0);
            $close_min = (int)$cl_parts[0] * 60 + (int)($cl_parts[1] ?? 0);
            $wh_days   = array_map('intval', $wh_data['days']);

            $day_open = in_array($now_day, $wh_days);
            $time_ok  = ($now_min >= $open_min && $now_min < $close_min);
            $is_open  = $day_open && $time_ok;

            $day_names_full  = ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'];
        ?>
        <div class="card animate-in" style="margin-top: 20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:12px;">
                <h3 style="margin:0;"><i class="fa-solid fa-clock" style="color:var(--secondary);"></i> Çalışma Saatleri</h3>
                <?php if ($is_open): ?>
                <span class="status-pill pill-open">
                    <span class="dot-live dot-open"></span> Şu An Açık
                </span>
                <?php else: ?>
                <span class="status-pill pill-closed">
                    <span class="dot-live dot-closed"></span>
                    <?php echo $day_open ? 'Bugün Kapalı (Mesai Dışı)' : 'Bugün Kapalı'; ?>
                </span>
                <?php endif; ?>
            </div>

            <?php if ($is_open || $day_open): ?>
            <div style="font-size:0.85rem; color:rgba(255,255,255,0.75); margin-bottom:12px;">
                <i class="fa-regular fa-clock"></i>
                Bugün: <strong style="color:#fff;"><?php echo $wh_data['open'] . ' – ' . $wh_data['close']; ?></strong>
            </div>
            <?php endif; ?>

            <table class="week-table">
                <?php for ($d = 1; $d <= 7; $d++):
                    $di = $d % 7; // 1=Pzt...0=Paz
                    $is_today   = ($di === $now_day);
                    $is_day_open = in_array($di, $wh_days);
                ?>
                <tr class="<?php echo $is_today ? 'today-row' : ''; ?>">
                    <td class="day-name">
                        <?php if ($is_today): ?><strong><?php endif; ?>
                        <?php echo $day_names_full[$di]; ?>
                        <?php if ($is_today): ?></strong> <small style="color:var(--secondary);">(Bugün)</small><?php endif; ?>
                    </td>
                    <td style="text-align:right;">
                        <?php if ($is_day_open): ?>
                            <span class="tag-open"><?php echo $wh_data['open'] . ' – ' . $wh_data['close']; ?></span>
                        <?php else: ?>
                            <span class="tag-closed-day">Kapalı</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endfor; ?>
            </table>
        </div>
        <?php endif; // has_wh ?>
        <!-- ────────────────────────────────────────────────────── -->

        <?php if ($products): ?>
        <div class="card animate-in" style="margin-top: 20px;">
            <h3><i class="fa-solid fa-utensils"></i> <?php echo $business['category'] == 'Restaurant' ? __('menu') : __('services'); ?></h3>
            <?php foreach ($products as $p): ?>
                <div style="display: flex; gap: 15px; padding: 15px 0; border-bottom: 1px solid var(--glass-bg);">
                    <?php if ($p['image_path']): ?>
                        <img src="<?php echo resolve_image_path($p['image_path']); ?>" style="width: 70px; height: 70px; border-radius: 10px; object-fit: cover;">
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <strong><?php echo htmlspecialchars(($is_en && !empty($p['name_en'])) ? $p['name_en'] : $p['name']); ?></strong>
                            <span style="font-weight: bold; color: var(--secondary);"><?php echo number_format($p['price'], 2); ?> TL</span>
                        </div>
                        <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 5px;"><?php echo htmlspecialchars(($is_en && !empty($p['description_en'])) ? $p['description_en'] : $p['description']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php 
        $hotel_info = json_decode($business['hotel_info'] ?? '{}', true);
        if ($business['category'] == 'Hotel' && !empty($hotel_info)): 
        ?>
        <div class="card animate-in" style="margin-top: 20px;">
            <h3><i class="fa-solid fa-circle-info"></i> <?php echo __('hotel_info'); ?></h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                <?php 
                $icon_map = [
                    'Oda Sayısı'   => 'fa-door-open',
                    'Havuz Tipi'   => 'fa-person-swimming',
                    'Wi-Fi'        => 'fa-wifi',
                    'Kahvaltı'    => 'fa-mug-hot',
                    'Öğle Yemeği'  => 'fa-sun',
                    'Akşam Yemeği' => 'fa-moon',
                ];
                foreach ($hotel_info as $label => $value): 
                    if (!$value) continue;
                    $icon = $icon_map[$label] ?? 'fa-circle-check';
                ?>
                <div style="background: rgba(255,255,255,0.05); padding: 12px; border-radius: 12px; display:flex; flex-direction:column; gap:4px;">
                    <small style="color: var(--text-secondary); display: block; font-size:0.75rem;">
                        <i class="fa-solid <?php echo $icon; ?>" style="color:var(--secondary); margin-right:4px;"></i>
                        <?php echo htmlspecialchars($label); ?>
                    </small>
                    <span style="font-size: 0.9rem; font-weight:600;"><?php echo htmlspecialchars($value); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php 
        // Hastane, Eczane, Otel ve Restoran hariç tut
        $is_excluded = ($business['category'] === 'Hospital' || $business['category'] === 'Pharmacy' || $business['category'] === 'Hastane' || $business['category'] === 'Eczane' || $is_hotel_res);
        if (!$is_excluded) {
            $target_id = $business['id'];
            $target_type = 'business';
            $target_name = $business['business_name'];
            include '../includes/visit_display.php';
        }
        ?>

        <?php include '../includes/business_stats_display.php'; ?>

        <div class="card" style="margin-top: 20px;">
            <h3><i class="fa-solid fa-location-dot"></i> <?php echo __('location_info'); ?></h3>
            <div id="map" style="height: 200px; border-radius: 15px; margin-top: 15px;"></div>
            <div class="card animate-in" style="margin-top: 20px; text-align: center; background: transparent; border: none; padding: 0;" data-lat="<?php echo $business['lat']; ?>" data-lng="<?php echo $business['lng']; ?>" data-id="<?php echo $business['id']; ?>" data-type="business" data-category="<?php echo $business['category']; ?>">
                <span class="distance-info" style="font-weight: bold; color: var(--secondary); font-size: 1.1rem;">
                    <i class="fa-solid fa-spinner fa-spin"></i> <?php echo __('calculating_distance'); ?>
                </span>
            </div>
            <br>
            <button class="btn btn-primary" style="width: 100%;" data-track-direction="<?php echo $id; ?>" onclick="window.open('https://www.google.com/maps?q=<?php echo $business['lat']; ?>,<?php echo $business['lng']; ?>')"><?php echo __('get_directions'); ?></button>
        </div>

        <?php 
        $widget_lat = $business['lat'];
        $widget_lng = $business['lng'];
        $widget_name = $title;
        include '../includes/traffic_widget.php';
        ?>

        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var lat = <?php echo $business['lat'] ?? 0; ?>;
                var lng = <?php echo $business['lng'] ?? 0; ?>;
                if (lat && lng) {
                    var map = L.map('map').setView([lat, lng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                    L.marker([lat, lng]).addTo(map);
                } else {
                    document.getElementById('map').innerHTML = '<p style="text-align:center; padding: 20px;"><?php echo __('no_location_info'); ?></p>';
                }
            });
        </script>


        <?php 
        $isHealth = ($business['category'] === 'Hospital' || $business['category'] === 'Pharmacy' || $business['category'] === 'Hastane' || $business['category'] === 'Eczane');
        if (!$isHealth && !$is_hotel_res) include '../includes/checkin_stats.php'; 
        ?>
    </main>

    <script src="../assets/js/app.js?v=7.0"></script>
    <script>
        app.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
    </div>
    <!-- Auth Modal -->
    <?php include '../includes/auth_modal.php'; ?>
    <!-- Bottom Navigation -->
    <?php include '../includes/bottom_nav.php'; ?>
</body>
</html>

