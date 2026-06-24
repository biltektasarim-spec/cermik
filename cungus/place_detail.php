<?php
require_once '../config.php';
$district_id = $_SESSION['district_id'] ?? ($_COOKIE['district_id'] ?? 0);
$settings = get_settings($pdo, $district_id);
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT p.*, d.slug as district_slug FROM places p JOIN districts d ON p.district_id = d.id WHERE p.id = ?");
$stmt->execute([$id]);
$place = $stmt->fetch();

if (!$place) {
    header("Location: index.php");
    exit;
}

// ── Ziyaret Takibi Temizliği (Logic include içindedir)
$current_user_id = $_SESSION['user_id'] ?? null;

// Localization Logic
$is_en = ($current_lang === 'en');
$display_name = ($is_en && !empty($place['name_en'])) ? $place['name_en'] : $place['name'];
$display_desc = ($is_en && !empty($place['description_en'])) ? $place['description_en'] : $place['description'];

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
    <title><?php echo $display_name; ?> - <?php echo __('belediye_rehberi'); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Pannellum (360 Viewer) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css"/>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
    <!-- Swiper (Image Slider) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

    <style>
        .pnlm-container { border-radius: 15px; overflow: hidden; }
        .pnlm-load-button { background-color: var(--primary) !important; border-radius: 50% !important; }
        
        .swiper { width: 100%; height: 200px; border-radius: 15px; margin-top: 15px; }
        .swiper-slide { background-size: cover; background-position: center; border-radius: 15px; }
        .swiper-pagination-bullet-active { background: var(--primary); }

        .back-btn {
            position: absolute; top: 20px; left: 20px; z-index: 10;
            background: rgba(0,0,0,0.4); width: 40px; height: 40px; 
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; color: white !important; font-size: 1.2rem;
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body data-page-context="<?php echo $display_name; ?>" data-district-slug="<?php echo $place['district_slug']; ?>">
<?php include '../includes/theme_bg.php'; ?>
<div id="app">
    <header class="header">
        <a href="index.php" class="home-link">
            <i class="fa-solid fa-house"></i> <?php echo __('home'); ?>
        </a>
        <h1><?php echo $settings['site_name'] ?? 'Çüngüş'; ?></h1>
    </header>

    <div style="position: relative;">
        <a href="javascript:history.back()" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
        
        <?php if ($place['panorama_360']): ?>
            <?php if (strpos($place['panorama_360'], 'insta360.com') !== false): ?>
                <!-- Insta360 Iframe Embed -->
                <div style="height: 400px; overflow: hidden; position: relative;">
                    <?php 
                    $p360 = $place['panorama_360'];
                    $separator = (strpos($p360, '?') === false) ? '?' : '&';
                    $embed_url = $p360 . $separator . "help=0&gui=0&brand=0&title=0&share=0&logo=0";
                    ?>
                    <iframe src="<?php echo $embed_url; ?>" width="100%" height="550px" frameborder="0" allowfullscreen allow="accelerometer; gyroscope; magnetometer; vr" style="margin-top: -140px; border: none;"></iframe>
                    <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 20px; background: linear-gradient(transparent, var(--app-bg)); pointer-events: none;">
                        <h1 style="margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo $display_name; ?></h1>
                    </div>
                </div>
            <?php else: ?>
                <!-- Pannellum Viewer -->
                <div style="height: 400px; position: relative; overflow: hidden;">
                    <div id="panorama" style="width: 100%; height: 100%;"></div>
                    <script>
                        pannellum.viewer('panorama', {
                            "type": "equirectangular",
                            "panorama": "<?php echo resolve_image_path($place['panorama_360']); ?>",
                            "autoLoad": true,
                            "compass": true,
                            "text": "<?php echo $display_name; ?>"
                        });
                    </script>
                    <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 20px; background: linear-gradient(transparent, var(--app-bg)); pointer-events: none;">
                        <h1 style="margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo $display_name; ?></h1>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <?php
            // Fallback: sadece gerçek image_main varsa göster
            $coverImg = null;
            if ($place['image_main'] && strpos($place['image_main'], 'default.jpg') === false) {
                $coverImg = $place['image_main'];
            }
            ?>
            <?php if ($coverImg): ?>
                <div style="height: 280px; background: url('<?php echo resolve_image_path($coverImg); ?>'); background-size: cover; background-position: center; position: relative;">
            <?php else: ?>
                <div class="place-bg-<?php echo $place['id']; ?>" style="height: 280px; background-size: cover; background-position: center; position: relative;">
            <?php endif; ?>
                <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0.7));"></div>
                <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 20px;">
                    <h1 style="margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo $display_name; ?></h1>
                </div>
            </div>
        <?php endif; ?>

        <?php 
        $gallery = json_decode($place['image_gallery'], true);
        if ($gallery && is_array($gallery)): 
        ?>
            <!-- Swiper -->
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    <?php foreach ($gallery as $img): ?>
                        <div class="swiper-slide" style="background-image: url('<?php echo resolve_image_path($img); ?>');"></div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
            <script>
                var swiper = new Swiper(".mySwiper", {
                    pagination: { el: ".swiper-pagination", dynamicBullets: true },
                    autoplay: { delay: 3000, disableOnInteraction: false },
                    effect: "cards",
                    grabCursor: true,
                });
            </script>
        <?php endif; ?>
    </div>

    <main class="section animate-in" style="padding-top: 10px;">
        <div style="display: flex; gap: 10px; margin-bottom: 20px;">
            <span class="badge" style="background: var(--primary); padding: 5px 12px; border-radius: 20px; font-size: 0.8rem;"><?php echo $place['category'] == 'Hospital' ? __('pharmacy_hospital') : ($place['category'] == 'Pharmacy' ? __('pharmacy_hospital') : $place['category']); ?></span>
            <?php if ($place['qr_code_path']): ?>
                <span class="badge" style="background: #28a745; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem;"><i class="fa-solid fa-qrcode"></i> <?php echo __('qr_available'); ?></span>
            <?php endif; ?>
        </div>

        <div class="card animate-in">
            <h3><?php echo __('about'); ?></h3>
            <div class="collapsible-wrapper">
                <div class="collapsible-content">
                    <p><?php echo nl2br(htmlspecialchars($display_desc)); ?></p>
                </div>
                <?php if (strlen($display_desc) > 250): ?>
                    <button class="read-more-btn" onclick="app.toggleCollapsible(this)">
                        <span><?php echo __('read_more'); ?></span> <i class="fa-solid fa-chevron-down"></i>
                    </button>
                <?php endif; ?>
            </div>
            
            <div style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
                <button id="checkin-btn" class="btn btn-primary" onclick="app.handleCheckIn(<?php echo $place['id']; ?>, 'place')" style="background: linear-gradient(45deg, #00c9ff, #92fe9d); color: #000; font-weight: 700; width: 100%;">
                    <i class="fa-solid fa-location-crosshairs"></i> <?php echo __('check_in_btn'); ?>
                </button>
            </div>
        </div>




        <?php 
        // Hastane ve Eczane hariç tut
        $isHealth = ($place['category'] === 'Hospital' || $place['category'] === 'Pharmacy' || $place['category'] === 'Hastane' || $place['category'] === 'Eczane');
        if (!$isHealth) {
            $target_id = $place['id'];
            $target_type = 'place';
            $target_name = $place['name'];
            include '../includes/visit_display.php';
            
        }
        ?>

        <?php if ($place['qr_code_path']): ?>
        <div class="card animate-in" style="text-align: center; margin-top: 20px;">
            <h3><?php echo __('place_qr'); ?></h3>
            <img src="<?php echo resolve_image_path($place['qr_code_path']); ?>" style="width: 150px; border-radius: 10px; background: white; padding: 10px; margin-top: 10px;">
        </div>
        <?php endif; ?>

        <div class="card">
            <h3><i class="fa-solid fa-location-dot"></i> <?php echo __('location_info'); ?></h3>
            <div class="card animate-in" style="margin-top: 20px; text-align: center;" data-lat="<?php echo $place['lat']; ?>" data-lng="<?php echo $place['lng']; ?>" data-id="<?php echo $place['id']; ?>" data-type="place" data-category="<?php echo $place['category']; ?>">
                <span class="distance-info" style="font-weight: bold; color: var(--secondary); font-size: 1.1rem;">
                    <i class="fa-solid fa-spinner fa-spin"></i> <?php echo __('calculating_distance'); ?>
                </span>
            </div>
            <br>
            <button class="btn btn-primary" style="margin-top: 15px;" onclick="window.open('https://www.google.com/maps?q=<?php echo $place['lat']; ?>,<?php echo $place['lng']; ?>')"><?php echo __('get_directions'); ?></button>
        </div>

        <?php 
        $widget_lat = $place['lat'];
        $widget_lng = $place['lng'];
        $widget_name = $display_name;
        include '../includes/traffic_widget.php';
        ?>

        <?php if (!$isHealth) include '../includes/checkin_stats.php'; ?>
    </main>


</div>
    <script src="../assets/js/app.js?v=7.0"></script>
    <script>
        app.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
    <!-- Auth Modal -->
    <?php include '../includes/auth_modal.php'; ?>
    <!-- Bottom Navigation -->
    <?php include '../includes/bottom_nav.php'; ?>
</body>
</html>
