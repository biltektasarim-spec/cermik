<?php
require_once '../config.php';
$category = 'Hotel';
$district_id = $_SESSION['district_id'] ?? ($_COOKIE['district_id'] ?? 0);
$settings = get_settings($pdo, $district_id);
$stmt_s = $pdo->prepare("SELECT value FROM settings WHERE name = 'menu_businesses_tr' AND (district_id = ? OR district_id IS NULL) ORDER BY district_id DESC LIMIT 1");
$stmt_s->execute([$district_id]);
$val_tr = $stmt_s->fetchColumn() ?: __('hotels');
$stmt_en = $pdo->prepare("SELECT value FROM settings WHERE name = 'menu_businesses_en' AND (district_id = ? OR district_id IS NULL) ORDER BY district_id DESC LIMIT 1");
$stmt_en->execute([$district_id]);
$val_en = $stmt_en->fetchColumn() ?: __('hotels');
$title = (isset($current_lang) && $current_lang === 'en') ? $val_en : $val_tr;

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
    <title>Çüngüş Rehberi - <?php echo $title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body data-page-context="<?php echo $title; ?>" data-district-slug="cungus">
<?php include '../includes/theme_bg.php'; ?>
<div id="app">
    <header class="header">
        <a href="index.php" class="home-link">
            <i class="fa-solid fa-house"></i> <?php echo __('home'); ?>
        </a>
        <h1><?php echo $title; ?></h1>
    </header>

    <main class="section">
        <div id="business-list" class="menu-list">
            <?php
            $stmt = $pdo->prepare("SELECT * FROM businesses WHERE category = ? AND district_id = ? ORDER BY business_name ASC");
            $stmt->execute([$category, $district_id]);
            $businesses = $stmt->fetchAll();

            if (isset($current_lang) && $current_lang === 'en') {
                foreach ($businesses as &$b) {
                    if (!empty($b['business_name_en'])) $b['business_name'] = $b['business_name_en'];
                }
                unset($b);
            }

            if ($businesses) {
                foreach ($businesses as $b) {
                    $bg = $b['image_main'] ?: 'assets/img/categories/hotels_bg.jpg';
                    $bg = resolve_image_path($bg);
                    echo "
                    <div class='card animate-in menu-item-bg' style=\"background-image: url('{$bg}');\" onclick=\"app.navigateTo('business', {$b['id']})\" data-lat=\"{$b['lat']}\" data-lng=\"{$b['lng']}\">
                        <div class='menu-card-overlay'></div>
                        <div style='display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 1;'>
                            <div>
                                <h3 style='margin-bottom: 5px; text-shadow: 0 2px 4px rgba(0,0,0,0.5); font-size: 1.1rem; font-weight: 700;'>" . htmlspecialchars($b['business_name']) . "</h3>
                                <div style='display: flex; gap: 10px; font-size: 0.8rem; color: rgba(255,255,255,0.85); font-weight: 500;'>
                                    <span class='distance-info' style='color:var(--secondary); font-weight:700; text-shadow: 0 1px 2px rgba(0,0,0,0.5);'></span>
                                    <span><i class='fa-solid fa-info-circle'></i> " . __('details') . "</span>
                                </div>
                            </div>
                            <i class='fa-solid fa-chevron-right' style='color: var(--secondary); font-size: 1.2rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));'></i>
                        </div>
                    </div>
";
                }
            } else {
                echo "<p>" . __('no_accommodation_found') . "</p>";
            }
            ?>
        </div>
    </main>

    <script src="../assets/js/app.js?v=7.0"></script>
    <script>
        app.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
</div>
    <!-- Bottom Navigation -->
    <?php include '../includes/bottom_nav.php'; ?>
</body>
</html>
