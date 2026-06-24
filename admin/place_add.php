<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

$default_cat = isset($_GET['cat']) ? $_GET['cat'] : 'Historical';

// Ekleme İşlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $name_en = $_POST['name_en'] ?? '';
    $category = $_POST['category'];
    $description = $_POST['description'];
    $description_en = $_POST['description_en'] ?? '';
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $ai_context = $_POST['ai_context'];

    $panorama_360 = $_POST['panorama_360'];
    $image_gallery = $_POST['image_gallery'];

    $hastaliklar = $_POST['hastaliklar'];
    $hastaliklar_en = $_POST['hastaliklar_en'] ?? '';

    $image_main = 'assets/img/project_default.jpg';
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
        $sql = "INSERT INTO places (district_id, name, name_en, category, description, description_en, hastaliklar, hastaliklar_en, lat, lng, ai_context, image_main, panorama_360, image_gallery) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$admin_district_id, $name, $name_en, $category, $description, $description_en, $hastaliklar, $hastaliklar_en, $lat, $lng, $ai_context, $image_main, $panorama_360, $image_gallery]);

        $new_id = $pdo->lastInsertId();
        header("Location: place_edit.php?id=$new_id&msg=added");
        exit;
    } catch (Exception $e) {
        die("HATA (Ekleme): " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Mekan Ekle - Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input[type="text"], input[type="number"], select, textarea {
            width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;
        }
        textarea { height: 150px; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>Yeni Mekan Ekle</h1>
                <p style="color: var(--text-muted);">Sisteme yeni bir mekan veya park ekleyin.</p>
            </div>
            <div class="header-right">
                <a href="index.php" class="btn" style="border: 1px solid #ddd;">Vazgeç</a>
            </div>
        </header>

        <main class="page-content">
            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Ana Görsel</label>
                        <input type="file" name="image_main_file" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>Mekan Adı (TR)</label>
                        <input type="text" name="name" placeholder="Örn: Haburman Köprüsü" required>
                    </div>
                    <div class="form-group">
                        <label>Mekan Adı (EN)</label>
                        <input type="text" name="name_en" placeholder="Örn: Haburman Bridge">
                    </div>
                    
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category">
                            <option value="Historical" <?php echo $default_cat == 'Historical' ? 'selected' : ''; ?>>Tarihi</option>
                            <option value="Nature" <?php echo $default_cat == 'Nature' ? 'selected' : ''; ?>>Doğa</option>
                            <option value="Park" <?php echo $default_cat == 'Park' ? 'selected' : ''; ?>>Park</option>
                            <option value="ParkAndGarden" <?php echo $default_cat == 'ParkAndGarden' ? 'selected' : ''; ?>>Park ve Bahçeler</option>
                            <option value="HotSpring" <?php echo $default_cat == 'HotSpring' ? 'selected' : ''; ?>>Kaplıca</option>
                            <?php if ($admin_district_id == 3 || $admin_district_id == 0): ?>
                            <option value="Kuruyemis" <?php echo $default_cat == 'Kuruyemis' ? 'selected' : ''; ?>>Kuruyemiş Pazarı</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Açıklama / Tarihçe (TR)</label>
                        <textarea name="description" placeholder="Mekan hakkında detaylı bilgi..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Açıklama / Tarihçe (EN)</label>
                        <textarea name="description_en" placeholder="English description..."></textarea>
                    </div>

                    <div class="form-group" id="hastaliklar-group">
                        <label>İyi Geldiği Hastalıklar (TR) - Sadece Kaplıca</label>
                        <textarea name="hastaliklar" placeholder="Örn: Romatizma, Cilt hastalıkları..." style="height: 100px;"></textarea>
                        <br>
                        <label>İyi Geldiği Hastalıklar (EN)</label>
                        <textarea name="hastaliklar_en" placeholder="e.g. Rheumatism, Skin diseases..." style="height: 100px;"></textarea>
                    </div>

                    <button type="button" class="btn" style="background: #38a169; color: white; margin-bottom: 10px; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;" onclick="getLocation()">
                        <i class="fa-solid fa-location-crosshairs"></i> Mevcut Konumumu Al
                    </button>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Enlem (Lat)</label>
                            <input type="text" name="lat" placeholder="38.13...">
                        </div>
                        <div class="form-group">
                            <label>Boylam (Lng)</label>
                            <input type="text" name="lng" placeholder="39.45...">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>AI Bağlamı (AI Context)</label>
                        <textarea name="ai_context" placeholder="AI'nın bu mekan hakkında bilmesi gereken özel notlar..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>360 Derece Panorama URL / Yolu</label>
                        <input type="text" name="panorama_360" placeholder="Örn: uploads/360/mekan.jpg">
                    </div>

                    <div class="form-group">
                        <label>Resim Galerisi (JSON Formatında)</label>
                        <textarea name="image_gallery" style="height: 80px;" placeholder='["img1.jpg", "img2.jpg"]'></textarea>
                        <small style="color: #666;">Not: Şimdilik manuel JSON (["resim1.jpg", "resim2.jpg"]) olarak giriniz.</small>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Mekanı Kaydet</button>
                </form>
            </div>
        </main>
    </div>

                    <script>
                    function checkCategory() {
                        const cat = document.getElementsByName('category')[0].value;
                        const hospGroup = document.getElementById('hastaliklar-group');
                        if (cat === 'HotSpring') {
                            hospGroup.style.display = 'block';
                        } else {
                            hospGroup.style.display = 'none';
                        }
                    }

                    function getLocation() {
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(function(position) {
                                document.getElementsByName('lat')[0].value = position.coords.latitude;
                                document.getElementsByName('lng')[0].value = position.coords.longitude;
                            });
                        }
                    }

                    // Initial check
                    document.addEventListener('DOMContentLoaded', checkCategory);
                    document.getElementsByName('category')[0].addEventListener('change', checkCategory);
                    </script>
</body>
</html>
