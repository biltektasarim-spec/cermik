<?php
require_once '../config.php';
$district_id = $_SESSION['district_id'] ?? ($_COOKIE['district_id'] ?? 0);
$settings = get_settings($pdo, $district_id);
$category = $_GET['category'] ?? 'Historical';
$is_en = (isset($current_lang) && $current_lang === 'en');

$title = 'Mekanlar';
if ($category == 'Nature') $title = $is_en ? ($settings['menu_nature_en'] ?? __('nature_parks')) : ($settings['menu_nature_tr'] ?? __('nature_parks'));
elseif ($category == 'ParkAndGarden') $title = $is_en ? ($settings['menu_parks_en'] ?? __('park_bahce')) : ($settings['menu_parks_tr'] ?? __('park_bahce'));
elseif ($category == 'Historical') $title = $is_en ? ($settings['menu_historical_en'] ?? __('historical_places')) : ($settings['menu_historical_tr'] ?? __('historical_places'));
elseif ($category == 'Businesses') $title = $is_en ? ($settings['menu_businesses_en'] ?? __('businesses')) : ($settings['menu_businesses_tr'] ?? __('businesses'));

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
    <title>Çüngüş Rehberi - <?php echo $title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .add-place-form { background: var(--glass-bg); padding: 20px; border-radius: var(--radius); margin-bottom: 30px; border: 1px solid var(--secondary); display: none; }
        .add-place-form.active { display: block; }
        input, textarea { width: 100%; padding: 12px; margin-bottom: 10px; border-radius: 10px; border: 1px solid var(--glass-bg); background: var(--card-bg); color: white; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 0.9rem; color: var(--text-secondary); }
    </style>
</head>
<body data-page-context="<?php echo $title; ?>" data-district-slug="cungus">
<?php include '../includes/splash_screen.php'; ?>
<?php include '../includes/theme_bg.php'; ?>
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
            if ($category == 'Businesses') {
                 $stmt = $pdo->prepare("SELECT * FROM businesses WHERE district_id = ? ORDER BY name ASC");
                 $stmt->execute([$district_id]);
                 $items = $stmt->fetchAll();
                 $item_type = 'business';
            } else {
                 $stmt = $pdo->prepare("SELECT * FROM places WHERE category = ? AND district_id = ? ORDER BY name ASC");
                 $stmt->execute([$category, $district_id]);
                 $items = $stmt->fetchAll();
                 $item_type = 'place';
            }

            if (isset($current_lang) && $current_lang === 'en') {
                foreach ($items as &$p) {
                    if (!empty($p['name_en'])) $p['name'] = $p['name_en'];
                    if (!empty($p['description_en'])) $p['description'] = $p['description_en'];
                }
                unset($p);
            }

            if ($items) {
                echo "<div class='menu-list'>";
                foreach ($items as $item) {
                    $img = $item['image_main'] ?? '';
                    $has_img = (!empty($img) && $img != 'assets/img/project_default.jpg' && $img != 'https://via.placeholder.com/400x200?text=');
                    
                    $card_style = "";
                    $inner_html = "";
                    
                    if ($has_img) {
                        $img = resolve_image_path($img);
                        $card_style = "background-image: url('{$img}'); background-size: cover; background-position: center;";
                    } else {
                        // Color block fallback based on category
                        $colors = [
                            'Historical' => 'linear-gradient(135deg, #5c3a21, #8b4513)',
                            'Nature' => 'linear-gradient(135deg, #228B22, #006400)',
                            'ParkAndGarden' => 'linear-gradient(135deg, #2E8B57, #1e5631)',
                            'Businesses' => 'linear-gradient(135deg, #2c3e50, #000000)'
                        ];
                        $grad = $colors[$category] ?? 'linear-gradient(135deg, #444, #111)';
                        $card_style = "background: {$grad};";
                    }

                    echo "
                    <div class='card animate-in menu-item-bg' onclick=\"app.navigateTo('{$item_type}', {$item['id']})\" data-lat=\"{$item['lat']}\" data-lng=\"{$item['lng']}\" data-id=\"{$item['id']}\" data-type=\"{$item_type}\" 
                         style=\"margin-bottom:15px; min-height:120px; {$card_style} border:none; display:flex; flex-direction:column; justify-content:flex-end; position:relative; overflow:hidden;\">
                        <div class='menu-card-overlay' style='background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.2) 60%, transparent 100%);'></div>
                        <div style=\"padding:15px; display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 1;\">
                            <div>
                                <h3 style=\"margin: 0; font-size: 1.1rem; font-weight: 700; color:white; text-shadow: 0 2px 4px rgba(0,0,0,0.8);\">" . htmlspecialchars($item['name']) . "</h3>
                                <div style=\"display: flex; gap: 10px; font-size: 0.8rem; color: rgba(255,255,255,0.8); font-weight:500;\">
                                    <span class=\"distance-info\"><i class=\"fa-solid fa-spinner fa-spin\"></i></span>
                                    <span><i class='fa-solid fa-info-circle'></i> " . __('details') . "</span>
                                </div>
                            </div>
                            <i class=\"fa-solid fa-chevron-right\" style=\"color: var(--secondary); font-size: 1.2rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));\"></i>
                        </div>
                    </div>";
                }
                echo "</div>";
            } else {
                echo "<p>Henüz kayıtlı içerik bulunmamaktadır.</p>";
            }
            ?>
        </div>
    </main>
</div>

    <!-- Scripts -->
    <script src="../assets/js/app.js?v=7.0"></script>
    <script>
        app.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
    <!-- Bottom Navigation -->
    <?php include '../includes/bottom_nav.php'; ?>
</body>
</html>
