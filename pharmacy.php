<?php
require_once 'config.php';

// Botu çalıştırarak nöbetçi eczaneleri güncelle (2 saatlik cache kontrollü)
require_once __DIR__ . '/api/fetch_pharmacies.php';
fetchPharmacies();

// Nöbetçi Eczaneleri Çek
$pharmacies = $pdo->query("SELECT * FROM pharmacies WHERE is_on_duty = 1 ORDER BY name ASC")->fetchAll();

// Hastaneleri Çek
$hospitals = $pdo->query("SELECT * FROM hospitals ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('pharmacy_hospital'); ?> - <?php echo $settings['site_name'] ?? 'Çermik'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .badge-duty { background: #e53e3e; color: white; padding: 4px 10px; border-radius: 10px; font-size: 0.70rem; font-weight: 700; margin-bottom: 10px; display: inline-block; }
        .hospital-card { border-left: 5px solid var(--primary); }
        .panorama-btn { background: var(--glass-bg); color: white; padding: 10px; border-radius: 10px; display: block; text-align: center; margin-top: 10px; text-decoration: none; font-size: 0.9rem; }
    </style>
</head>
<body data-page-context="Sağlık Kuruluşları">
<?php include 'includes/theme_bg.php'; ?>
<div id="app">
    <header class="header">
        <a href="index.php" class="home-link">
            <i class="fa-solid fa-house"></i> <?php echo __('home'); ?>
        </a>
        <h1><?php echo __('health_guide'); ?></h1>
    </header>

    <!-- Nöbetçi Eczane Banner Section -->
    <?php if (!empty($pharmacies)): ?>
        <div class="animate-in" style="background: linear-gradient(135deg, #e53e3e, #b83232); color: white; padding: 25px; text-align: center; border-radius: 0 0 30px 30px; margin-bottom: 25px; box-shadow: 0 10px 20px rgba(229, 62, 62, 0.2);">
            <div style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; opacity: 0.9; margin-bottom: 5px;"><?php echo str_replace('.', $settings['site_name'] ?? 'Çermik', __('on_duty_today')); ?></div>
            <h1 style="margin: 0; font-size: 1.8rem; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"><?php echo htmlspecialchars(html_entity_decode($pharmacies[0]['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?></h1>
            <p style="margin: 10px 0 0; font-weight: 500; font-size: 1.1rem; opacity: 1;"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($pharmacies[0]['phone']); ?></p>
        </div>
    <?php endif; ?>

    <main class="section animate-in">
        
        <?php if (!empty($pharmacies)): ?>
            <h2 style="margin-top: 0;"><i class="fa-solid fa-house-medical"></i> <?php echo __('pharmacy_details'); ?></h2>
            <div id="pharmacy-list" class="menu-list">
                <?php foreach ($pharmacies as $p): ?>
                    <div class="card animate-in menu-item-bg" style="background-image: url('assets/img/categories/medical.jpg');" data-lat="<?php echo $p['lat']; ?>" data-lng="<?php echo $p['lng']; ?>">
                        <div class="menu-card-overlay"></div>
                        <div style="position: relative; z-index: 1;">
                            <span class="badge-duty"><?php echo __('on_duty_badge'); ?></span>
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div>
                                    <h2 style="color: #e53e3e; text-shadow: 0 2px 4px rgba(0,0,0,0.5); font-size: 1.2rem; margin-top: 5px;"><?php echo htmlspecialchars(html_entity_decode($p['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8')); ?></h2>
                                    <p style="margin: 5px 0; color: white; text-shadow: 0 1px 3px rgba(0,0,0,0.5);"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($p['phone']); ?></p>
                                    <p style="font-size: 0.8rem; color: rgba(255,255,255,0.85);"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($p['address']); ?></p>
                                    <span class="distance-info" style="font-weight: bold; color: var(--secondary); font-size: 0.8rem; text-shadow: 0 1px 2px rgba(0,0,0,0.5);"></span>
                                </div>
                                <button class="btn btn-primary" style="padding: 10px;" onclick="window.open('tel:<?php echo $p['phone']; ?>')"><i class="fa-solid fa-phone"></i></button>
                            </div>
                            <button class="btn btn-primary" style="margin-top: 10px; width: 100%;" onclick="window.open('https://www.google.com/maps/dir/?api=1&destination=<?php echo $p['lat']; ?>,<?php echo $p['lng']; ?>')"><?php echo __('directions'); ?></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h2 style="margin-top: 30px;"><i class="fa-solid fa-hospital"></i> <?php echo __('hospitals_label'); ?></h2>
        <?php if (!empty($hospitals)): ?>
            <div id="hospital-list" class="menu-list">
                <?php foreach ($hospitals as $h): ?>
                    <div class="card hospital-card animate-in menu-item-bg" style="background-image: url('<?php echo $h['image_main'] ?: 'assets/img/categories/medical_bg.jpg'; ?>');" data-lat="<?php echo $h['lat']; ?>" data-lng="<?php echo $h['lng']; ?>">
                        <div class="menu-card-overlay"></div>
                        <div style="position: relative; z-index: 1;">
                            
                            <h3 onclick="location.href='hospital_detail.php?id=<?php echo $h['id']; ?>'" style="cursor:pointer; color: var(--secondary); text-shadow: 0 2px 4px rgba(0,0,0,0.5); font-size: 1.2rem;"><?php echo htmlspecialchars($h['name']); ?></h3>
                            
                            <div style="display: flex; gap: 10px; margin-top: 15px;">
                                <span class="distance-info" style="font-weight: bold; color: var(--secondary); font-size: 0.8rem; text-shadow: 0 1px 2px rgba(0,0,0,0.5);"></span>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px;">
                                <button class="btn btn-primary" onclick="window.open('https://www.google.com/maps/dir/?api=1&destination=<?php echo $h['lat']; ?>,<?php echo $h['lng']; ?>')"><?php echo __('directions'); ?></button>
                                <?php if ($h['panorama_360']): ?>
                                    <a href="hospital_detail.php?id=<?php echo $h['id']; ?>" class="panorama-btn" style="background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.2);"><i class="fa-solid fa-vr-cardboard"></i> <?php echo __('pano_360_view'); ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p><?php echo __('no_hospital_found'); ?></p>
        <?php endif; ?>

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
