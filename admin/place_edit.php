<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$place = $pdo->prepare("SELECT * FROM places WHERE id = ?");
$place->execute([$id]);
$place = $place->fetch();

if (!$place) {
    echo "Mekan bulunamadı.";
    exit;
}

// Güncelleme İşlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $name_en = $_POST['name_en'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $description_en = $_POST['description_en'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $ai_context = $_POST['ai_context'];
    $popular_score = $_POST['popular_score'];

    $panorama_360 = $_POST['panorama_360'];
    $image_gallery = $_POST['image_gallery'];

    $hastaliklar = $_POST['hastaliklar'];
    $hastaliklar_en = $_POST['hastaliklar_en'];

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
        $sql = "UPDATE places SET name = ?, name_en = ?, category = ?, description = ?, description_en = ?, hastaliklar = ?, hastaliklar_en = ?, lat = ?, lng = ?, ai_context = ?, popular_score = ?, panorama_360 = ?, image_gallery = ?, image_main = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $name_en, $category, $description, $description_en, $hastaliklar, $hastaliklar_en, $lat, $lng, $ai_context, $popular_score, $panorama_360, $image_gallery, $image_main, $id]);

        header("Location: place_edit.php?id=$id&msg=updated");
        exit;
    } catch (Exception $e) {
        die("HATA (Kaydetme): " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mekan Düzenle - <?php echo htmlspecialchars($place['name']); ?></title>
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
                <h1>Mekan Düzenle</h1>
                <p style="color: var(--text-muted);"><?php echo htmlspecialchars($place['name']); ?> detaylarını güncelleyin.</p>
            </div>
            <div class="header-right">
                <a href="<?php echo $place['category'] == 'Historical' ? 'places_historical.php' : 'places_nature.php'; ?>" class="btn" style="border: 1px solid #ddd;">Geri Dön</a>
            </div>
        </header>

        <main class="page-content">
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
                <div style="background: #e8f5e9; color: #2e7d32; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    Mekan başarıyla güncellendi.
                </div>
            <?php endif; ?>

            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Ana Görsel</label>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                            <img src="../<?php echo $place['image_main'] ?: 'assets/img/project_default.jpg'; ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                            <input type="file" name="image_main_file" accept="image/*">
                            <input type="hidden" name="old_image_main" value="<?php echo htmlspecialchars($place['image_main']); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Mekan Adı (TR)</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($place['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Mekan Adı (EN)</label>
                        <input type="text" name="name_en" value="<?php echo htmlspecialchars($place['name_en'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category">
                            <option value="Historical" <?php echo $place['category'] == 'Historical' ? 'selected' : ''; ?>>Tarihi</option>
                            <option value="Nature" <?php echo $place['category'] == 'Nature' ? 'selected' : ''; ?>>Doğa</option>
                            <option value="Park" <?php echo $place['category'] == 'Park' ? 'selected' : ''; ?>>Park</option>
                            <option value="ParkAndGarden" <?php echo $place['category'] == 'ParkAndGarden' ? 'selected' : ''; ?>>Park ve Bahçeler</option>
                            <option value="HotSpring" <?php echo $place['category'] == 'HotSpring' ? 'selected' : ''; ?>>Kaplıca</option>
                            <option value="Kuruyemis" <?php echo $place['category'] == 'Kuruyemis' ? 'selected' : ''; ?>>Kuruyemiş Pazarı</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Açıklama / Tarihçe (TR)</label>
                        <textarea name="description"><?php echo htmlspecialchars($place['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Açıklama / Tarihçe (EN)</label>
                        <textarea name="description_en"><?php echo htmlspecialchars($place['description_en'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group" id="hastaliklar-group">
                        <label>İyi Geldiği Hastalıklar (TR) - Sadece Kaplıca</label>
                        <textarea name="hastaliklar" style="height: 100px;"><?php echo htmlspecialchars($place['hastaliklar']); ?></textarea>
                        <br>
                        <label>İyi Geldiği Hastalıklar (EN)</label>
                        <textarea name="hastaliklar_en" style="height: 100px;"><?php echo htmlspecialchars($place['hastaliklar_en'] ?? ''); ?></textarea>
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
                        <label>AI Bağlamı (AI Context)</label>
                        <textarea name="ai_context" placeholder="AI'nın bu mekan hakkında bilmesi gerekenler..."><?php echo htmlspecialchars($place['ai_context']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Popülerlik Skoru</label>
                        <input type="number" name="popular_score" value="<?php echo $place['popular_score']; ?>">
                    </div>

                    <div class="form-group">
                        <label>360 Derece Panorama URL / Yolu</label>
                        <input type="text" name="panorama_360" value="<?php echo htmlspecialchars($place['panorama_360']); ?>" placeholder="Örn: uploads/360/mekan.jpg">
                    </div>

                    <div class="form-group">
                        <label>Resim Galerisi (JSON Formatında)</label>
                        <textarea name="image_gallery" style="height: 80px;" placeholder='["img1.jpg", "img2.jpg"]'><?php echo htmlspecialchars($place['image_gallery']); ?></textarea>
                        <small style="color: #666;">Not: Şimdilik manuel JSON (["resim1.jpg", "resim2.jpg"]) olarak giriniz.</small>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Değişiklikleri Kaydet</button>
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
