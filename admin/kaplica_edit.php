<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

$edit_district_id = 3;
$admin_restricted_district = intval($_SESSION['admin_district_id'] ?? 0);
if ($admin_restricted_district > 0 && $admin_restricted_district != 3) {
    die("Yetkisiz erişim. Bu sayfa sadece Çermik için geçerlidir.");
}

// Tüm aktif ilçeler (sekme seçimi devre dışı)
$is_super = ($_SESSION['admin_role'] === 'SUPER_ADMIN');
$all_districts = [];

// Çermik için özel durumlar
$is_cermik      = true;
$is_cungus      = false;

// Bu ilçeye ait HotSpring kaydını bul
$stmt = $pdo->prepare("SELECT * FROM places WHERE category = 'HotSpring' AND district_id = ? LIMIT 1");
$stmt->execute([$edit_district_id]);
$place = $stmt->fetch();

if (!$place) {
    $default_name = $is_cungus ? 'Karakaya Barajı' : 'Çermik Kaplıcaları';
    $default_desc = $is_cungus ? 'Karakaya Barajı hakkında bilgi...' : 'Varsayılan açıklama...';
    $default_lat  = $is_cungus ? 38.2756 : 38.1361;
    $default_lng  = $is_cungus ? 39.2803 : 39.4478;
    $pdo->prepare("INSERT INTO places (district_id, name, category, description, hastaliklar, lat, lng, image_main)
                VALUES (?, ?, 'HotSpring', ?, 'Bilgi girilmedi...', ?, ?, 'assets/img/categories/kaplica.jpg')")
        ->execute([$edit_district_id, $default_name, $default_desc, $default_lat, $default_lng]);
    $stmt = $pdo->prepare("SELECT * FROM places WHERE category = 'HotSpring' AND district_id = ? LIMIT 1");
    $stmt->execute([$edit_district_id]);
    $place = $stmt->fetch();
}

$id = $place['id'];

// Güncelleme İşlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name            = $_POST['name'];
    $name_en         = $_POST['name_en'];
    $description     = $_POST['description'];
    $description_en  = $_POST['description_en'];
    $hastaliklar     = $_POST['hastaliklar'];
    $hastaliklar_en  = $_POST['hastaliklar_en'];
    $lat             = $_POST['lat'];
    $lng             = $_POST['lng'];
    $ai_context      = $_POST['ai_context'];
    $slogan          = $_POST['slogan'];
    $slogan_en       = $_POST['slogan_en'];
    $panorama_360    = $_POST['panorama_360'];
    $image_gallery   = $_POST['image_gallery'];
    $heading_hastaliklar_tr = $_POST['heading_hastaliklar_tr'];
    $heading_hastaliklar_en = $_POST['heading_hastaliklar_en'];

    $image_main = $_POST['old_image_main'] ?? '';
    if (isset($_FILES['image_main_file']) && $_FILES['image_main_file']['error'] == 0) {
        $target_dir = "../uploads/places/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_ext = pathinfo($_FILES['image_main_file']['name'], PATHINFO_EXTENSION);
        $new_filename = 'place_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
        if (move_uploaded_file($_FILES['image_main_file']['tmp_name'], $target_dir . $new_filename)) {
            $image_main = 'uploads/places/' . $new_filename;
        }
    }

    try {
        $sql = "UPDATE places SET name = ?, name_en = ?, description = ?, description_en = ?, hastaliklar = ?, hastaliklar_en = ?, lat = ?, lng = ?, ai_context = ?, slogan = ?, slogan_en = ?, panorama_360 = ?, image_gallery = ?, image_main = ?, heading_hastaliklar_tr = ?, heading_hastaliklar_en = ? WHERE id = ?";
        $pdo->prepare($sql)->execute([$name, $name_en, $description, $description_en, $hastaliklar, $hastaliklar_en, $lat, $lng, $ai_context, $slogan, $slogan_en, $panorama_360, $image_gallery, $image_main, $heading_hastaliklar_tr, $heading_hastaliklar_en, $id]);
        header("Location: kaplica_edit.php?district_id={$edit_district_id}&msg=updated");
        exit;
    } catch (Exception $e) {
        die("HATA (Kaydetme): " . $e->getMessage());
    }
}

// İlçeyi çekerek dinamikleştirelim
$stmt_slug = $pdo->prepare("SELECT slug FROM districts WHERE id = ?");
$stmt_slug->execute([$edit_district_id]);
$current_slug = $stmt_slug->fetchColumn() ?: 'cermik';

// Sayfa başlığı ve görsel URL'si dinamik
$is_cermik      = ($edit_district_id == 3);
$is_cungus      = ($edit_district_id == 5);
$page_title     = htmlspecialchars($place['name']) . ' Yönetimi';
$view_url       = '../' . $current_slug . '/';
$hastalikar_label = $is_cermik ? 'İyi Geldiği Hastalıklar' : 'Hakkında / Kapsamlı Bilgi';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input[type="text"], input[type="number"], select, textarea {
            width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;
        }
        textarea { height: 120px; }
        .district-tabs { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
        .district-tab { padding: 8px 16px; border-radius: 8px; text-decoration: none; border: 1px solid #ddd; font-size: 0.85rem; font-weight: 600; color: #555; background: #f9f9f9; }
        .district-tab.active { background: #3b82f6; color: white; border-color: #3b82f6; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1><?php echo $page_title; ?></h1>
                <p style="color: var(--text-muted);">İlçe seçerek ilgili sayfanın içeriğini düzenleyin.</p>
            </div>
            <div class="header-right">
                <a href="<?php echo $view_url; ?>" target="_blank" class="btn" style="border: 1px solid #ddd;">Sayfayı Görüntüle</a>
            </div>
        </header>

        <main class="page-content">

            <!-- İlçe Sekmeleri Kaldırıldı -->
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
                <div style="background: #e8f5e9; color: #2e7d32; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    Çermik Kaplıcaları bilgileri başarıyla güncellendi.
                </div>
            <?php endif; ?>

            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Kapak Görseli (Yatay)</label>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                            <img src="../<?php echo $place['image_main'] ?: 'assets/img/project_default.jpg'; ?>" style="width: 150px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                            <input type="file" name="image_main_file" accept="image/*">
                            <input type="hidden" name="old_image_main" value="<?php echo htmlspecialchars($place['image_main']); ?>">
                        </div>
                        <small style="color:#777;">Panorama (360) resmi yoksa bu görsel en üstte arka plan olarak kullanılacaktır.</small>
                    </div>
                    <div class="form-group">
                        <label>Sayfa Başlığı (TR)</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($place['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Sayfa Başlığı (EN)</label>
                        <input type="text" name="name_en" value="<?php echo htmlspecialchars($place['name_en'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Slogan / Alt Başlık (TR)</label>
                        <input type="text" name="slogan" value="<?php echo htmlspecialchars($place['slogan'] ?? ''); ?>" placeholder="Örn: Dünyaca ünlü şifalı sularımız...">
                    </div>

                    <div class="form-group">
                        <label>Slogan / Alt Başlık (EN)</label>
                        <input type="text" name="slogan_en" value="<?php echo htmlspecialchars($place['slogan_en'] ?? ''); ?>" placeholder="Example: Our world-famous healing waters...">
                        <small style="color:#777;">Çermik Kaplıcaları sayfasının ana görseli üzerindeki açıklama yazısı.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Tarihçe ve Genel Bilgi (TR)</label>
                        <textarea name="description"><?php echo htmlspecialchars($place['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Tarihçe ve Genel Bilgi (EN)</label>
                        <textarea name="description_en"><?php echo htmlspecialchars($place['description_en'] ?? ''); ?></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label>Hastalıklar/Bilgi Bölüm Başlığı (TR)</label>
                            <input type="text" name="heading_hastaliklar_tr" value="<?php echo htmlspecialchars($place['heading_hastaliklar_tr'] ?? ($is_cermik ? 'İyi Geldiği Hastalıklar' : 'Ülke Ekonomisine Katkısı')); ?>" placeholder="Örn: İyi Geldiği Hastalıklar">
                        </div>
                        <div class="form-group">
                            <label>Hastalıklar/Bilgi Bölüm Başlığı (EN)</label>
                            <input type="text" name="heading_hastaliklar_en" value="<?php echo htmlspecialchars($place['heading_hastaliklar_en'] ?? ($is_cermik ? 'Diseases Well For' : 'Contribution to Country Economy')); ?>" placeholder="Example: Diseases Well For">
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?php echo $hastalikar_label; ?> İçeriği (TR)</label>
                        <textarea name="hastaliklar" placeholder="Virgülle ayırarak veya liste şeklinde yazabilirsiniz..."><?php echo htmlspecialchars($place['hastaliklar']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label><?php echo $hastalikar_label; ?> (EN)</label>
                        <textarea name="hastaliklar_en"><?php echo htmlspecialchars($place['hastaliklar_en'] ?? ''); ?></textarea>
                    </div>

                    <button type="button" class="btn" style="background: #38a169; color: white; margin-bottom: 10px; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;" onclick="getLocation()">
                        <i class="fa-solid fa-location-crosshairs"></i> Mevcut Konumumu Al
                    </button>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Enlem (Lat)</label>
                            <input type="text" name="lat" value="<?php echo $place['lat']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Boylam (Lng)</label>
                            <input type="text" name="lng" value="<?php echo $place['lng']; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>AI Bağlamı (AI Rehber Notları)</label>
                        <textarea name="ai_context"><?php echo htmlspecialchars($place['ai_context']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>360 Derece Panorama URL</label>
                        <input type="text" name="panorama_360" value="<?php echo htmlspecialchars($place['panorama_360']); ?>" placeholder="Örn: uploads/360/kaplica.jpg">
                    </div>

                    <div class="form-group">
                        <label>Resim Galerisi (JSON Formatında)</label>
                        <textarea name="image_gallery" style="height: 80px;"><?php echo htmlspecialchars($place['image_gallery']); ?></textarea>
                        <small style="color: #666;">Not: ["resim1.jpg", "resim2.jpg"] formatında yazın.</small>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Ayarları Kaydet</button>
                </form>
            </div>
        </main>
    </div>

    <script>
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementsByName('lat')[0].value = position.coords.latitude;
                document.getElementsByName('lng')[0].value = position.coords.longitude;
            });
        }
    }
    </script>
</body>
</html>
