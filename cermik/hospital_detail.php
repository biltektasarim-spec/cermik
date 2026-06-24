<?php
require_once '../config.php';
// District identification (hardcoded for subfolder consistency or derived from session)
$district_id = 3; 
$settings = get_settings($pdo, $district_id);
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT h.*, d.slug as district_slug FROM hospitals h JOIN districts d ON h.district_id = d.id WHERE h.id = ?");
$stmt->execute([$id]);
$hospital = $stmt->fetch();

if (!$hospital) {
    header("Location: pharmacy.php");
    exit;
}

// Localization Logic
$is_en = ($current_lang === 'en');
$display_name = ($is_en && !empty($hospital['name_en'])) ? $hospital['name_en'] : $hospital['name'];
$display_desc = ($is_en && !empty($hospital['description_en'])) ? $hospital['description_en'] : $hospital['description'];

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
    <title><?php echo $display_name; ?> - <?php echo __('health_guide'); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Pannellum (360 Viewer) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css"/>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js"></script>

    <style>
        .pnlm-container { border-radius: 15px; overflow: hidden; }
        .back-btn {
            position: absolute; top: 20px; left: 20px; z-index: 10;
            background: rgba(0,0,0,0.4); width: 40px; height: 40px; 
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; color: white !important; font-size: 1.2rem;
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body data-page-context="<?php echo $display_name; ?>" data-district-slug="<?php echo $hospital['district_slug']; ?>">
<?php include '../includes/theme_bg.php'; ?>
<div id="app">
    <header class="header">
        <a href="index.php" class="home-link">
            <i class="fa-solid fa-house"></i> <?php echo __('home'); ?>
        </a>
        <h1><?php echo __('health_guide'); ?></h1>
    </header>

    <div style="position: relative;">
        <a href="javascript:history.back()" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
        
        <?php if ($hospital['panorama_360']): ?>
            <div style="height: 400px; position: relative; overflow: hidden;">
                <div id="panorama" style="width: 100%; height: 100%;"></div>
                <script>
                    pannellum.viewer('panorama', {
                        "type": "equirectangular",
                        "panorama": "<?php echo resolve_image_path($hospital['panorama_360']); ?>",
                        "autoLoad": true,
                        "compass": true,
                        "text": "<?php echo $display_name; ?>"
                    });
                </script>
                <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 20px; background: linear-gradient(transparent, var(--app-bg)); pointer-events: none;">
                    <h1 style="margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo $display_name; ?></h1>
                </div>
            </div>
        <?php else: ?>
            <div style="height: 280px; background: url('<?php echo resolve_image_path($hospital['image_main'] ?: 'assets/img/categories/medical_bg.jpg'); ?>'); background-size: cover; background-position: center; position: relative;">
                <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0.7));"></div>
                <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 20px;">
                    <h1 style="margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo $display_name; ?></h1>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <main class="section animate-in" style="padding-top: 10px;">
        <div class="card animate-in">
            <h3><?php echo __('about'); ?></h3>
            <p><?php echo nl2br(htmlspecialchars($display_desc ?: __('no_details_available'))); ?></p>
        </div>

        <div class="card">
            <h3><i class="fa-solid fa-location-dot"></i> <?php echo __('location_info'); ?></h3>
            <div class="card animate-in" style="margin-top: 20px; text-align: center;" data-lat="<?php echo $hospital['lat']; ?>" data-lng="<?php echo $hospital['lng']; ?>" data-id="<?php echo $hospital['id']; ?>" data-type="hospital">
                <span class="distance-info" style="font-weight: bold; color: var(--secondary); font-size: 1.1rem;">
                    <i class="fa-solid fa-spinner fa-spin"></i> <?php echo __('calculating_distance'); ?>
                </span>
            </div>
            <br>
            <button class="btn btn-primary" style="margin-top: 15px;" onclick="window.open('https://www.google.com/maps?q=<?php echo $hospital['lat']; ?>,<?php echo $hospital['lng']; ?>')"><?php echo __('get_directions'); ?></button>
        </div>
    </main>
</div>
<script src="../assets/js/app.js?v=7.0"></script>
<?php include '../includes/bottom_nav.php'; ?>
</body>
</html>
