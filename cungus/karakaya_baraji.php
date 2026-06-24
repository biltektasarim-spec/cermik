<?php 
require_once '../config.php'; 
$district_id = 5; // Cungus is forcefully 5
$settings = get_settings($pdo, $district_id);

function resolve_image_path($path) {
    if (!$path) return '';
    if (strpos($path, 'http') === 0) return $path;
    if (strpos($path, 'assets/') === 0 || strpos($path, 'uploads/') === 0) {
        if (file_exists(__DIR__ . '/' . $path)) return $path;
        return '../' . $path;
    }
    return $path;
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Karakaya Barajı verisini çek (Admin panelindeki özel kayıt: ID 26)
$stmt = $pdo->prepare("SELECT * FROM places WHERE id = 26 LIMIT 1");
$stmt->execute();
$h = $stmt->fetch();

if (!$h) {
    die("Karakaya Barajı verisi bulunamadı.");
}

if ($h && $_SESSION['lang'] === 'en') {
    if (!empty($h['name_en'])) $h['name'] = $h['name_en'];
    if (!empty($h['description_en'])) $h['description'] = $h['description_en'];
    if (!empty($h['hastaliklar_en'])) $h['hastaliklar'] = $h['hastaliklar_en'];
    if (!empty($h['slogan_en'])) $h['slogan'] = $h['slogan_en'];
    if (!empty($h['heading_hastaliklar_en'])) $h['heading_hastaliklar_tr'] = $h['heading_hastaliklar_en'];
}

$sec_heading = (!empty($h['heading_hastaliklar_tr'])) ? htmlspecialchars($h['heading_hastaliklar_tr']) : __('economy_contribution');

$is_en = ($_SESSION['lang'] === 'en');

$display_title = $h ? htmlspecialchars($h['name']) : ($is_en ? ($settings['hero_title_en'] ?? 'Karakaya Dam') : ($settings['hero_title_tr'] ?? 'Karakaya Barajı'));
$display_subtitle = ($h && !empty($h['slogan'])) ? htmlspecialchars($h['slogan']) : __('kaplica_subtitle');
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
        .kaplica-header {
            height: 300px;
            background-size: cover;
            background-position: center !important;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 20px;
        }
        .info-menu {
            display: flex;
            flex-direction: column;
            gap: 15px;
            padding: 20px;
        }
        .info-item {
            background: var(--card-bg);
            padding: 20px;
            border-radius: var(--radius);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(255,255,255,0.05);
        }
        
    </style>
    <!-- Pannellum (360 Viewer) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css"/>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
    <!-- Swiper (Image Slider) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
</head>
<body data-page-context="<?php echo $display_title; ?>" data-district-slug="cungus">
<?php include '../includes/theme_bg.php'; ?>
<div id="app">


    <header class="header">
        <a href="index.php" class="home-link">
            <i class="fa-solid fa-house"></i> <?php echo __('home'); ?>
        </a>
        <h1><?php echo $settings['site_name'] ?? 'Çüngüş Belediyesi'; ?></h1>
    </header>



    <?php if ($h && $h['panorama_360']): ?>
        <div class="kaplica-header" style="height: 400px; padding: 0; position: relative; overflow: hidden;">
            <?php 
            $p360 = $h['panorama_360'];
            $isEmbed = (strpos($p360, 'insta360.com') !== false || strpos($p360, '<iframe') !== false);
            
            if ($isEmbed):
            ?>
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
                <div class="kaplica-header" style="height: 400px; padding: 0; position: relative; overflow: hidden;">
                    <div id="panorama-header" style="width: 100%; height: 100%;"></div>
                    <script>
                        pannellum.viewer('panorama-header', {
                            "type": "equirectangular",
                            "panorama": "<?php echo resolve_image_path($h['panorama_360']); ?>",
                            "autoLoad": true,
                            "autoRotate": -2
                        });
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

                <?php 
                $gallery = json_decode($h['image_gallery'], true);
                if ($gallery && is_array($gallery)): 
                ?>
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
                        <div class="collapsible-content">
                            <p style="font-size: 1rem; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($h['description'])); ?></p>
                        </div>
                        <?php if (isset($h['description']) && strlen($h['description']) > 200): ?>
                            <button class="read-more-btn" onclick="app.toggleCollapsible(this)">
                                <span><?php echo __('read_more'); ?></span> <i class="fa-solid fa-chevron-down"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card animate-in" style="margin-top: 25px; border-top: 4px solid #ff6666; background: rgba(0,0,0,0.2);">
                    <h3 style="margin-bottom: 15px; color: #ff6666;"><i class="fa-solid fa-chart-line"></i> <?php echo $sec_heading; ?></h3>
                    <div class="collapsible-wrapper">
                        <div class="collapsible-content" style="background: rgba(255,102,102,0.1); padding: 20px; border-radius: 12px; font-size: 1.1rem; line-height: 1.6; color: #ff8888;">
                            <?php echo $h['hastaliklar'] ? nl2br(htmlspecialchars($h['hastaliklar'])) : __('no_info_entered'); ?>
                        </div>
                        <?php if (isset($h['hastaliklar']) && strlen($h['hastaliklar']) > 150): ?>
                            <button class="read-more-btn" onclick="app.toggleCollapsible(this)">
                                <span><?php echo __('read_more'); ?></span> <i class="fa-solid fa-chevron-down"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <button id="checkin-btn" class="btn btn-primary" onclick="app.handleCheckIn(<?php echo $h['id']; ?>, 'place')" style="background: linear-gradient(45deg, #00c9ff, #92fe9d); color: #000; font-weight: 700; width: 100%;">
                        <i class="fa-solid fa-location-crosshairs"></i> Ben Buradayım! (Check-in)
                    </button>
                </div>


                <?php 
                // Today's Approved Visitors
                $stmt_visitors = $pdo->prepare("
                    SELECT u.first_name, u.last_name, u.profile_image 
                    FROM check_ins c 
                    JOIN users u ON c.user_id = u.id 
                    WHERE c.target_id = ? AND c.target_type = 'place' AND c.status = 'APPROVED' AND DATE(c.created_at) = CURDATE()
                    ORDER BY c.created_at DESC LIMIT 20
                ");
                $stmt_visitors->execute([$h['id']]);
                $visitors = $stmt_visitors->fetchAll();
                
                if ($visitors): ?>
                <div class="card animate-in" style="margin-top: 20px;">
                    <h3><i class="fa-solid fa-users"></i> <?php echo __('today_visitors'); ?></h3>
                    <div style="display: flex; gap: 10px; overflow-x: auto; padding: 10px 0;">
                        <?php foreach ($visitors as $v): ?>
                        <div style="text-align: center; min-width: 60px;">
                            <?php 
                            $v_avatar = $v['profile_image'] ?: 'assets/img/default-avatar.png';
                            $v_img_src = resolve_image_path($v_avatar);
                            ?>
                            <img src="<?php echo $v_img_src; ?>" style="width: 50px; height: 50px; border-radius: 50%; border: 2px solid var(--primary); object-fit: cover;">
                            <p style="font-size: 0.65rem; color: var(--text-secondary); margin-top: 5px;">
                                <?php echo htmlspecialchars($v['first_name'] . ' ' . mb_substr($v['last_name'], 0, 1, 'UTF-8') . '.'); ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php 
                // Ziyaret Bilgisi
                $target_id = $h['id'];
                $target_type = 'place';
                $target_name = $h['name'];
                include '../includes/visit_display.php';
                ?>
        <?php else: ?>
            <div class="card animate-in">
                <p><?php echo __('kaplica_load_error'); ?></p>
            </div>
        <?php endif; ?>

        <?php 
        $widget_lat = $h['lat'] ?? '';
        $widget_lng = $h['lng'] ?? '';
        $widget_name = $display_title;
        include '../includes/traffic_widget.php';
        ?>

        <?php include '../includes/checkin_stats.php'; ?>
    </div>

    <script src="../assets/js/app.js?v=7.0"></script>
    <script>
        app.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
</div>
    <!-- Bottom Navigation -->
    <?php include '../includes/bottom_nav.php'; ?>
</body>
</html>
