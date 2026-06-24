<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';


// İlçe listesini çek (Süper admin için)
$districts = [];
if ($is_super_admin) {
    try {
        $districts = $pdo->query("SELECT id, name FROM districts ORDER BY name ASC")->fetchAll();
    } catch (Exception $e) {}
}

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: businesses.php"); exit; }

$business = $pdo->prepare("SELECT * FROM businesses WHERE id = ?");
$business->execute([$id]);
$business = $business->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $business_name = $_POST['business_name'];
    $business_name_en = $_POST['business_name_en'];
    $description = $_POST['description'];
    $description_en = $_POST['description_en'];
    $category = $_POST['category'];
    $username = $_POST['username'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $contact_info = $_POST['contact_info'];
    $phone = $_POST['phone'] ?? '';
    $district_id = $is_super_admin ? intval($_POST['district_id'] ?? $business['district_id']) : $admin_district_id;
    $order_enabled = isset($_POST['order_enabled']) ? 1 : 0;
    $order_link = $_POST['order_link'] ?? '';

    $image_main = $business['image_main'];

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
    
    // Otel Extra Bilgileri - sadece admin oda ve havuz bilgisini günceller
    // Wi-Fi, Kahvaltı, Öğle/Akşam Yemeği işletme panelinden yönetilir
    $hotel_info = null;
    if ($category === 'Hotel') {
        // Mevcut hotel_info'yu al, sadece admin alanlarını güncelle
        $existingStmt = $pdo->prepare("SELECT hotel_info FROM businesses WHERE id = ?");
        $existingStmt->execute([$id]);
        $existing = $existingStmt->fetchColumn();
        $existingInfo = json_decode($existing ?? '{}', true) ?: [];
        
        // Admin sadece Oda Sayısı ve Havuz Tipini günceller
        $existingInfo['Oda Sayısı'] = $_POST['hotel_rooms'] ?? ($existingInfo['Oda Sayısı'] ?? '');
        $existingInfo['Havuz Tipi'] = $_POST['hotel_pool'] ?? ($existingInfo['Havuz Tipi'] ?? '');
        
        $hotel_info = json_encode($existingInfo, JSON_UNESCAPED_UNICODE);
    }

    // SQL Query Construction
    $params = [
        $district_id, $business_name, $business_name_en, $category, 
        $username, $lat, $lng, $contact_info, $description, 
        $description_en, $phone, $image_main, $order_enabled, $order_link
    ];
    
    $query_parts = [
        "district_id = ?", "business_name = ?", "business_name_en = ?", "category = ?",
        "username = ?", "lat = ?", "lng = ?", "contact_info = ?", "description = ?",
        "description_en = ?", "phone = ?", "image_main = ?", "order_enabled = ?", "order_link = ?"
    ];

    if (!empty($_POST['password'])) {
        $query_parts[] = "password = ?";
        $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    if ($hotel_info !== null) {
        $query_parts[] = "hotel_info = ?";
        $params[] = $hotel_info;
    }

    $sql = "UPDATE businesses SET " . implode(", ", $query_parts) . " WHERE id = ?";
    $params[] = $id;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        header("Location: business_edit.php?id=$id&msg=updated");
        exit;
    } catch (Exception $e) {
        error_log('[REHBER business_edit] ' . $e->getMessage());
        $form_error = 'Kaydetme sırasında bir hata oluştu: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İşletme Düzenle - Çermik Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input[type="text"], input[type="password"], select, textarea {
            width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;
        }
        .btn-gps { background: #38a169; color: white; padding: 0.5rem 1rem; border-radius: 5px; border: none; cursor: pointer; margin-bottom: 10px; font-size: 0.8rem; }
        #map { height: 300px; border-radius: 10px; margin-bottom: 15px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <header>
            <h1>İşletme Düzenle</h1>
            <a href="businesses.php" class="btn">Geri Dön</a>
        </header>

        <main class="page-content">
            <?php if (isset($_GET['msg'])): ?>
                <div style="background: #e8f5e9; padding: 10px; margin-bottom: 10px; border-radius: 5px; color: #2e7d32;">Bilgiler güncellendi.</div>
            <?php endif; ?>
            <?php if (!empty($form_error)): ?>
                <div style="background: #ffebee; border: 1px solid #ef5350; padding: 10px; margin-bottom: 15px; border-radius: 5px; color: #c62828;">
                    <strong>Hata:</strong> <?php echo htmlspecialchars($form_error); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($is_super_admin): ?>
                    <div class="form-group">
                        <label>İlçe</label>
                        <select name="district_id" required>
                            <?php foreach ($districts as $d): ?>
                                <option value="<?php echo $d['id']; ?>" <?php echo $d['id'] == $business['district_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($d['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label>İşletme Görseli</label>
                        <?php if ($business['image_main']): ?>
                            <div style="margin-bottom: 10px;">
                                <img src="../<?php echo $business['image_main']; ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image_main_file" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>İşletme Adı (TR)</label>
                        <input type="text" name="business_name" value="<?php echo htmlspecialchars($business['business_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>İşletme Adı (EN)</label>
                        <input type="text" name="business_name_en" value="<?php echo htmlspecialchars($business['business_name_en'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category">
                            <option value="Restaurant" <?php echo $business['category'] == 'Restaurant' ? 'selected' : ''; ?>>Restoran</option>
                            <option value="Hotel" <?php echo $business['category'] == 'Hotel' ? 'selected' : ''; ?>>Otel</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Giriş Kullanıcı Adı</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($business['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Yeni Şifre (Boş bırakılırsa değişmez)</label>
                        <input type="password" name="password">
                    </div>
                    
                    <label>Konum Seçin (Sürükle veya Haritaya Tıkla)</label>
                    <div id="map"></div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Enlem (Lat)</label>
                            <input type="text" name="lat" id="lat" value="<?php echo $business['lat']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Boylam (Lng)</label>
                            <input type="text" name="lng" id="lng" value="<?php echo $business['lng']; ?>">
                        </div>
                    </div>

                    <button type="button" class="btn-gps" onclick="getLocation()"><i class="fa-solid fa-location-crosshairs"></i> Mevcut Konumumu Al</button>

                    <div class="form-group">
                        <label>İletişim / Adres Bilgisi (TR)</label>
                        <textarea name="contact_info"><?php echo htmlspecialchars($business['contact_info']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Telefon Numarası</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($business['phone'] ?? ''); ?>" placeholder="örn: 05xx xxx xx xx">
                    </div>
                    <div class="form-group">
                        <label>Hakkında / Açıklama (TR)</label>
                        <textarea name="description" rows="4"><?php echo htmlspecialchars($business['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>About / Description (EN)</label>
                        <textarea name="description_en" rows="4"><?php echo htmlspecialchars($business['description_en'] ?? ''); ?></textarea>
                    </div>

                    <!-- Sipariş Ayarları (Sadece Restaurant için) -->
                    <div id="restaurant-orders" style="<?php echo $business['category'] == 'Restaurant' ? '' : 'display:none;'; ?>">
                        <hr style="margin: 1rem 0; border-color: rgba(0,0,0,0.1);">
                        <h4 style="margin-bottom: 1rem; color: #555;"><i class="fa-solid fa-utensils"></i> Sipariş Ayarları</h4>
                        <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" name="order_enabled" id="order_enabled" style="width: auto;" <?php echo $business['order_enabled'] ? 'checked' : ''; ?>>
                            <label for="order_enabled" style="margin-bottom: 0;">Sipariş Butonu Aktif</label>
                        </div>
                        <div class="form-group">
                            <label>Sipariş Linki (Getir, Yemeksepeti vb.)</label>
                            <input type="text" name="order_link" value="<?php echo htmlspecialchars($business['order_link'] ?? ''); ?>" placeholder="https://...">
                        </div>
                    </div>

                    <!-- Otel Özellikleri -->
                    <?php 
                    $hi = json_decode($business['hotel_info'] ?? '{}', true) ?: [];
                    ?>
                    <div id="hotel-extras" style="<?php echo $business['category'] == 'Hotel' ? '' : 'display:none;'; ?>">
                        <hr style="margin: 1rem 0; border-color: rgba(0,0,0,0.1);">
                        <h4 style="margin-bottom: 1rem; color: #555;"><i class="fa-solid fa-hotel"></i> Otel Özellikleri (Temel)</h4>
                        <p style="font-size: 0.8rem; color: #888; margin-bottom: 1rem;">
                            <i class="fa-solid fa-circle-info"></i> 
                            Wi-Fi, Kahvaltı, Öğle ve Akşam yemeği seçenekleri işletme kendi panelinden yönetebilir.
                        </p>
                        <div class="form-group">
                            <label>Oda Sayısı</label>
                            <input type="text" name="hotel_rooms" value="<?php echo htmlspecialchars($hi['Oda Sayısı'] ?? ''); ?>" placeholder="örn: 45">
                        </div>
                        <div class="form-group">
                            <label>Havuz Tipi</label>
                            <input type="text" name="hotel_pool" value="<?php echo htmlspecialchars($hi['Havuz Tipi'] ?? ''); ?>" placeholder="örn: Termal, Kapalı, Açık">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Değişiklikleri Kaydet</button>
                </form>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    var lat = <?php echo $business['lat'] ?: '38.1384'; ?>;
    var lng = <?php echo $business['lng'] ?: '39.4475'; ?>;

    var map = L.map('map').setView([lat, lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    var marker = L.marker([lat, lng], {draggable: true}).addTo(map);

    marker.on('dragend', function(e) {
        var position = marker.getLatLng();
        document.getElementById('lat').value = position.lat;
        document.getElementById('lng').value = position.lng;
    });

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        document.getElementById('lat').value = e.latlng.lat;
        document.getElementById('lng').value = e.latlng.lng;
    });

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var nLat = position.coords.latitude;
                var nLng = position.coords.longitude;
                document.getElementById('lat').value = nLat;
                document.getElementById('lng').value = nLng;
                marker.setLatLng([nLat, nLng]);
                map.setView([nLat, nLng], 15);
            });
        }
    }
    </script>
    <script>
    // Kategori değişiminde otel panelini göster/gizle
    document.querySelector('select[name="category"]').addEventListener('change', function() {
        var hotelExtras = document.getElementById('hotel-extras');
        var restaurantOrders = document.getElementById('restaurant-orders');
        
        if (hotelExtras) {
            hotelExtras.style.display = (this.value === 'Hotel') ? 'block' : 'none';
        }
        if (restaurantOrders) {
            restaurantOrders.style.display = (this.value === 'Restaurant') ? 'block' : 'none';
        }
    });
    </script>
</body>
</html>
