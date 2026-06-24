<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

// İlçe listesini çek (Süper admin için)
$districts = [];
if ($is_super_admin) {
    $districts = $pdo->query("SELECT id, name FROM districts ORDER BY name ASC")->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $business_name = $_POST['business_name'];
    $business_name_en = $_POST['business_name_en'] ?? '';
    $category = $_POST['category'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $contact_info = $_POST['contact_info'];
    $description = $_POST['description'] ?? '';
    $description_en = $_POST['description_en'] ?? '';
    $district_id = $is_super_admin ? intval($_POST['district_id'] ?? 0) : $admin_district_id;
    $order_enabled = isset($_POST['order_enabled']) ? 1 : 0;
    $order_link = $_POST['order_link'] ?? '';

    $image_main = null;

    // Resim Yükleme İşlemi
    $target_dir = "../uploads/businesses/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    if (isset($_FILES['image_main_file']) && $_FILES['image_main_file']['error'] == 0) {
        $file_ext = pathinfo($_FILES['image_main_file']['name'], PATHINFO_EXTENSION);
        $new_filename = 'biz_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
        if (move_uploaded_file($_FILES['image_main_file']['tmp_name'], $target_dir . $new_filename)) {
            $image_main = 'uploads/businesses/' . $new_filename;
        }
    }

    try {
        $sql = "INSERT INTO businesses (district_id, business_name, business_name_en, category, username, password, lat, lng, contact_info, description, description_en, image_main, order_enabled, order_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$district_id, $business_name, $business_name_en, $category, $username, $password, $lat, $lng, $contact_info, $description, $description_en, $image_main, $order_enabled, $order_link]);

        header("Location: businesses.php?msg=added");
        exit;
    } catch (Exception $e) {
        error_log('[REHBER business_add] ' . $e->getMessage());
        $add_error = 'Ekleme sırasında bir hata oluştu: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni İşletme Ekle - Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <header>
            <h1>Yeni İşletme Ekle</h1>
            <a href="businesses.php" class="btn">Vazgeç</a>
        </header>

        <main class="page-content">
            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($is_super_admin): ?>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">İlçe Seçimi</label>
                        <select name="district_id" required style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;">
                            <option value="">İlçe Seçin...</option>
                            <?php foreach ($districts as $d): ?>
                                <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">İşletme Görseli</label>
                        <input type="file" name="image_main_file" accept="image/*" style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">İşletme Adı (TR)</label>
                        <input type="text" name="business_name" required style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">İşletme Adı (EN)</label>
                        <input type="text" name="business_name_en" style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Kategori</label>
                        <select name="category" style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;">
                            <option value="Restaurant">Restoran</option>
                            <option value="Hotel">Otel</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Giriş Kullanıcı Adı</label>
                        <input type="text" name="username" required style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Giriş Şifresi</label>
                        <input type="password" name="password" required style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                    
                    <button type="button" class="btn" style="background: #38a169; color: white; padding: 0.5rem 1rem; border-radius: 5px; border: none; cursor: pointer; margin-bottom: 10px; font-size: 0.8rem;" onclick="getLocation()"><i class="fa-solid fa-location-crosshairs"></i> Mevcut Konumumu Al</button>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Enlem (Lat)</label>
                            <input type="text" name="lat" id="lat" style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;">
                        </div>
                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Boylam (Lng)</label>
                            <input type="text" name="lng" id="lng" style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;">
                        </div>
                    </div>
                    <div id="restaurant-orders" style="display: none;">
                        <hr style="margin: 1rem 0; border: 0.5px solid #eee;">
                        <h4 style="margin-bottom: 1rem; color: #555;"><i class="fa-solid fa-utensils"></i> Sipariş Ayarları</h4>
                        <div class="form-group" style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" name="order_enabled" id="order_enabled" style="width: auto;" checked>
                            <label for="order_enabled" style="margin-bottom: 0; font-weight: 600;">Sipariş Butonu Aktif</label>
                        </div>
                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Sipariş Linki (Getir, Yemeksepeti vb.)</label>
                            <input type="text" name="order_link" placeholder="https://..." style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">İletişim / Adres Bilgisi (TR)</label>
                        <textarea name="contact_info" style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd; height: 60px;"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Hakkında / Açıklama (TR)</label>
                        <textarea name="description" rows="4" style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;"></textarea>
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">About / Description (EN)</label>
                        <textarea name="description_en" rows="4" style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">İşletmeyi Kaydet</button>
                </form>
            </div>
        </main>
    </div>

    <script>
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('lat').value = position.coords.latitude;
                document.getElementById('lng').value = position.coords.longitude;
            });
        }
    }
    
    // Kategori değişiminde sipariş panelini göster/gizle
    document.querySelector('select[name="category"]').addEventListener('change', function() {
        var div = document.getElementById('restaurant-orders');
        if (div) {
            div.style.display = (this.value === 'Restaurant') ? 'block' : 'none';
        }
    });
    
    // Sayfa açılışında kontrol et
    window.addEventListener('load', function() {
        var category = document.querySelector('select[name="category"]').value;
        var div = document.getElementById('restaurant-orders');
        if (div) div.style.display = (category === 'Restaurant') ? 'block' : 'none';
    });
    </script>
</body>
</html>
