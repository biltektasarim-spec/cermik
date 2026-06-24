<?php
require_once 'config.php';
$category = $_GET['category'] ?? 'Historical';
$stmt_s = $pdo->query("SELECT name, value FROM settings WHERE name LIKE 'menu_%'");
$settings = [];
while ($row = $stmt_s->fetch()) {
    $settings[$row['name']] = $row['value'];
}
$is_en = (isset($current_lang) && $current_lang === 'en');

$title = 'Mekanlar';
if ($category == 'Nature') $title = $is_en ? ($settings['menu_nature_en'] ?? 'Nature and Parks') : ($settings['menu_nature_tr'] ?? 'Kadim Rotalar');
elseif ($category == 'ParkAndGarden') $title = $is_en ? ($settings['menu_parks_en'] ?? 'Parks and Gardens') : ($settings['menu_parks_tr'] ?? 'Park ve Bahçeler');
elseif ($category == 'Historical') $title = $is_en ? ($settings['menu_historical_en'] ?? 'Historical Places') : ($settings['menu_historical_tr'] ?? 'Tarihi Mekanlar');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Çermik Rehberi - <?php echo $title; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .add-place-form { background: var(--glass-bg); padding: 20px; border-radius: var(--radius); margin-bottom: 30px; border: 1px solid var(--secondary); display: none; }
        .add-place-form.active { display: block; }
        input, textarea { width: 100%; padding: 12px; margin-bottom: 10px; border-radius: 10px; border: 1px solid var(--glass-bg); background: var(--card-bg); color: white; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 0.9rem; color: var(--text-secondary); }
    </style>
</head>
<body data-page-context="<?php echo $title; ?>">
<?php include 'includes/theme_bg.php'; ?>
<div id="app">
    <header class="header">
        <a href="index.php" class="home-link">
            <i class="fa-solid fa-house"></i> Ana Sayfa
        </a>
        <h1><?php echo $title; ?></h1>
    </header>

    <main class="section">
        <!-- Yeni Mekan Ekle Bölümü -->
        <div id="add-form" class="add-place-form animate-in">
            <h3>Yeni Mekan Ekle</h3>
            <form action="api/add_place.php" method="POST">
                <input type="hidden" name="category" value="<?php echo $category; ?>">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Mekan Adı" required>
                    <textarea name="description" placeholder="Detaylı Tarihçe ve Bilgi" rows="3" required></textarea>
                </div>
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="lat" placeholder="Enlem (Lat)" required>
                    <input type="text" name="lng" placeholder="Boylam (Lng)" required>
                </div>
                <textarea name="ai_context" placeholder="AI Karakter Bilgi Notu" rows="2"></textarea>
                
                <h4 style="margin: 10px 0;">Yol Üstü Durağı (Opsiyonel)</h4>
                <input type="text" name="poi_name" placeholder="Durak Adı (Örn: Tarihi Çeşme)">
                <input type="number" name="trigger_radius" placeholder="Yaklaşım Mesafesi (Metre - Örn: 100)" value="100">
                <textarea name="audio_script" placeholder="AI Seslendirme Metni" rows="2"></textarea>
                
                <button type="submit" class="btn btn-primary">Kaydet</button>
            </form>
        </div>
        <div id="archive-list">
            <?php
            $stmt = $pdo->prepare("SELECT * FROM places WHERE category = ? ORDER BY name ASC");
            $stmt->execute([$category]);
            $places = $stmt->fetchAll();

            if (isset($current_lang) && $current_lang === 'en') {
                foreach ($places as &$p) {
                    if (!empty($p['name_en'])) $p['name'] = $p['name_en'];
                    if (!empty($p['description_en'])) $p['description'] = $p['description_en'];
                }
                unset($p);
            }

            if ($places) {
                // Doğa, Tarihi ve Park kategoriler için "Menü" tarzı görünüm
                if ($category == 'Nature' || $category == 'Historical' || $category == 'ParkAndGarden') {
                    echo "<div class='menu-list'>";
                    foreach ($places as $place) {
                        $bg_class = "place-bg-" . $place['id'];
                        echo "
                        <div class='card animate-in menu-item-bg {$bg_class}' onclick=\"app.navigateTo('place', {$place['id']})\" data-lat=\"{$place['lat']}\" data-lng=\"{$place['lng']}\" data-id=\"{$place['id']}\">
                            <div class='menu-card-overlay'></div>
                            <div style=\"display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 1;\">
                                <div>
                                    <h3 style=\"margin-bottom: 5px; text-shadow: 0 2px 4px rgba(0,0,0,0.5); font-size: 1.1rem; font-weight: 700; letter-spacing: 0.3px;\">" . htmlspecialchars($place['name']) . "</h3>
                                    <div style=\"display: flex; gap: 10px; font-size: 0.8rem; color: rgba(255,255,255,0.85); font-weight: 500;\">
                                        <span class=\"distance-info\"><i class=\"fa-solid fa-spinner fa-spin\" style=\"font-size:0.7rem;\"></i></span>
                                        <span><i class=\"fa-solid fa-star\" style=\"color:#f6ad55;\"></i> {$place['popular_score']} Popülerlik</span>
                                    </div>
                                </div>
                                <i class=\"fa-solid fa-chevron-right\" style=\"color: var(--secondary); font-size: 1.2rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));\"></i>
                            </div>
                        </div>";
                    }
                    echo "</div>";
                } else {
                    // Diğer kategoriler için kart görünümü
                    foreach ($places as $place) {
                        echo "
                        <div class='card animate-in' onclick=\"location.href='place_detail.php?id={$place['id']}'\" data-lat=\"{$place['lat']}\" data-lng=\"{$place['lng']}\" data-id=\"{$place['id']}\">
                            <img src='" . ($place['image_main'] ?: 'https://via.placeholder.com/400x200?text='.$place['name']) . "' style='width:100%; border-radius:15px; margin-bottom:12px;' alt='{$place['name']}'>
                            <div style='display:flex; justify-content:space-between; align-items:start;'>
                                <div>
                                    <h3>{$place['name']}</h3>
                                    <p>" . substr($place['description'], 0, 100) . "...</p>
                                </div>
                                <span class='distance-info' style='font-size:0.8rem; font-weight:700; color:var(--secondary); min-width:60px; text-align:right;'></span>
                            </div>
                        </div>";
                    }
                }
            } else {
                echo "<p>Henüz kayıtlı mekan bulunmamaktadır.</p>";
            }
            ?>
        </div>
    </main>
</div>

    <!-- Scripts -->
    <script src="assets/js/app.js?v=7.0"></script>
    <script>
        app.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
    <!-- Bottom Navigation -->
    <?php include 'includes/bottom_nav.php'; ?>
</body>
</html>
