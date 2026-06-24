<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'includes/pwa_meta.php'; ?>
    <title><?php echo __('kuruyemis_rehberi'); ?> - <?php echo __('belediye_rehberi'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Pannellum (360 Viewer) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css"/>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>
</head>
<body data-page-context="Kuruyemiş">
<?php include 'includes/splash_screen.php'; ?>
<?php include 'includes/theme_bg.php'; ?>
<div id="app">
    <header class="header">
        <a href="index.php" class="home-link">
            <i class="fa-solid fa-house"></i> <?php echo __('home'); ?>
        </a>
        <h1><?php echo $settings['site_name'] ?? 'Çermik'; ?></h1>
    </header>

    <?php
    // Veritabanında kuruyemis tablosu yoksa statik olarak göster
    $kuruyemis_360   = '';   // Daha sonra admin'den eklenecek
    $kuruyemis_lat   = '';
    $kuruyemis_lng   = '';
    $kuruyemis_aciklama = __('kuruyemis_pazari_desc');
    ?>

    <!-- 360 Fotoğraf veya Kapak Görseli -->
    <?php if ($kuruyemis_360 && strpos($kuruyemis_360, 'insta360.com') !== false): ?>
        <div style="height: 400px; overflow: hidden; position: relative;">
            <?php
            $separator = (strpos($kuruyemis_360, '?') === false) ? '?' : '&';
            $embed_url = $kuruyemis_360 . $separator . "help=0&gui=0&brand=0&title=0&share=0&logo=0";
            ?>
            <iframe src="<?php echo $embed_url; ?>" width="100%" height="550px" frameborder="0"
                allowfullscreen allow="accelerometer; gyroscope; magnetometer; vr"
                style="margin-top: -140px; border: none;"></iframe>
            <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 20px;
                background: linear-gradient(transparent, var(--app-bg)); pointer-events: none;">
                <h1 style="margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo __('kuruyemis'); ?></h1>
            </div>
        </div>
    <?php elseif ($kuruyemis_360): ?>
        <div style="height: 400px; position: relative; overflow: hidden;">
            <div id="panorama-kuruyemis" style="width: 100%; height: 100%;"></div>
            <script>
                pannellum.viewer('panorama-kuruyemis', {
                    "type": "equirectangular",
                    "panorama": "<?php echo $kuruyemis_360; ?>",
                    "autoLoad": true,
                    "autoRotate": -2
                });
            </script>
            <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 20px;
                background: linear-gradient(transparent, var(--app-bg)); pointer-events: none;">
                <h1 style="margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo __('kuruyemis'); ?></h1>
            </div>
        </div>
    <?php else: ?>
        <!-- 360 Fotoğraf Henüz Eklenmedi — Kapak Placeholder -->
        <div style="height: 280px; background: linear-gradient(135deg, rgba(139,69,19,0.8), rgba(210,105,30,0.8)),
            url('assets/img/categories/parks.jpg'); background-size: cover; background-position: center;
            position: relative; display: flex; align-items: flex-end;">
            <div style="width: 100%; padding: 20px; background: linear-gradient(transparent, var(--app-bg));">
                <h1 style="margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                    <i class="fa-solid fa-store" style="color: #D2691E;"></i> <?php echo __('kuruyemis'); ?>
                </h1>
                <p style="margin: 5px 0 0; opacity: 0.8; font-size: 0.85rem;">
                    <i class="fa-solid fa-camera-rotate" style="color: #f6ad55;"></i>
                    <?php echo __('pano_360_soon'); ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <main class="section animate-in" style="padding-top: 15px;">

        <!-- Açıklama Kartı -->
        <div class="card animate-in">
            <h3><i class="fa-solid fa-circle-info" style="color: var(--secondary);"></i> <?php echo __('about'); ?></h3>
            <div class="collapsible-wrapper">
                <div class="collapsible-content">
                    <p style="font-size: 1rem; line-height: 1.7; color: var(--text-primary);">
                        <?php echo nl2br(htmlspecialchars($kuruyemis_aciklama)); ?>
                    </p>
                </div>
                <?php if (strlen($kuruyemis_aciklama) > 250): ?>
                    <button class="read-more-btn" onclick="app.toggleCollapsible(this)">
                        <span><?php echo __('read_more'); ?></span> <i class="fa-solid fa-chevron-down"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Konum Bilgisi -->
        <div class="card animate-in">
            <h3><i class="fa-solid fa-location-dot" style="color: var(--secondary);"></i> <?php echo __('location_info'); ?></h3>
            <?php if ($kuruyemis_lat && $kuruyemis_lng): ?>
                <div class="card animate-in" style="margin-top: 15px; text-align: center;"
                    data-lat="<?php echo $kuruyemis_lat; ?>" data-lng="<?php echo $kuruyemis_lng; ?>">
                    <span class="distance-info" style="font-weight: bold; color: var(--secondary); font-size: 1.1rem;">
                        <i class="fa-solid fa-spinner fa-spin"></i> <?php echo __('calculating_distance'); ?>
                    </span>
                </div>
                <br>
                <button class="btn btn-primary" style="margin-top: 10px;"
                    onclick="window.open('https://www.google.com/maps?q=<?php echo $kuruyemis_lat; ?>,<?php echo $kuruyemis_lng; ?>')">
                    <i class="fa-solid fa-location-arrow"></i> <?php echo __('get_directions'); ?>
                </button>
            <?php else: ?>
                <div style="text-align: center; padding: 20px; opacity: 0.6;">
                    <i class="fa-solid fa-map-pin" style="font-size: 2rem; margin-bottom: 10px; color: var(--secondary);"></i>
                    <p><?php echo __('no_location_info'); ?></p>
                    <p style="font-size: 0.75rem; margin-top: 5px;"><?php echo __('will_be_added_from_admin'); ?></p>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <script src="assets/js/app.js?v=7.0"></script>
    <script>
        app.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
</div>
    <!-- Bottom Navigation -->
    <?php include 'includes/bottom_nav.php'; ?>
</body>
</html>
