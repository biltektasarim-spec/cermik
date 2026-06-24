<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $name_en = $_POST['name_en'] ?? '';
    $description = $_POST['description'];
    $description_en = $_POST['description_en'] ?? '';
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $panorama_360 = $_POST['panorama_360'];
    $admin_restricted_district = intval($_SESSION['admin_district_id'] ?? 0);
    $district_id = $admin_restricted_district > 0 ? $admin_restricted_district : (int)($_POST['district_id'] ?? 0);
    
    $image_main = "";
    if (isset($_FILES['image_main']) && $_FILES['image_main']['error'] == 0) {
        $ext = pathinfo($_FILES['image_main']['name'], PATHINFO_EXTENSION);
        $filename = "hospital_" . time() . "." . $ext;
        move_uploaded_file($_FILES['image_main']['tmp_name'], "../uploads/" . $filename);
        $image_main = "uploads/" . $filename;
    }

    $stmt = $pdo->prepare("INSERT INTO hospitals (district_id, name, name_en, description, description_en, lat, lng, panorama_360, image_main) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$district_id, $name, $name_en, $description, $description_en, $lat, $lng, $panorama_360, $image_main]);
    header("Location: hospital_pharmacy.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Hastane Ekle - Çermik</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <header>
            <h1>Yeni Hastane Ekle</h1>
            <a href="hospital_pharmacy.php" class="btn">Geri Dön</a>
        </header>

        <main class="page-content">
            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <?php
                    $admin_restricted_district = intval($_SESSION['admin_district_id'] ?? 0);
                    if ($admin_restricted_district === 0): // Sadece Super Admin ilçe seçebilir
                    ?>
                    <div class="form-group">
                        <label>İlçe</label>
                        <select name="district_id" style="width:100%; padding:10px;">
                            <?php
                            $dists = $pdo->query("SELECT id, name FROM districts WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
                            foreach ($dists as $dist):
                            ?>
                            <option value="<?php echo $dist['id']; ?>"><?php echo htmlspecialchars($dist['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Hastane Adı (TR)</label>
                        <input type="text" name="name" required style="width:100%; padding:10px;">
                    </div>
                    <div class="form-group">
                        <label>Hastane Adı (EN)</label>
                        <input type="text" name="name_en" style="width:100%; padding:10px;">
                    </div>
                    <div class="form-group">
                        <label>Açıklama (TR)</label>
                        <textarea name="description" style="width:100%; padding:10px; height:100px;"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Açıklama (EN)</label>
                        <textarea name="description_en" style="width:100%; padding:10px; height:100px;"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Ana Resim</label>
                        <input type="file" name="image_main" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>360 Derece Görsel Linki (Foto360)</label>
                        <input type="text" name="panorama_360" placeholder="https://..." style="width:100%; padding:10px;">
                    </div>
                    
                    <label>Konum Seçin (Tıkla veya Sürükle)</label>
                    <div id="map" style="height: 300px; margin-bottom: 20px; border-radius: 10px;"></div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Enlem (Lat)</label>
                            <input type="text" name="lat" id="lat" value="38.1384">
                        </div>
                        <div class="form-group">
                            <label>Boylam (Lng)</label>
                            <input type="text" name="lng" id="lng" value="39.4475">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top:20px;">Kaydet</button>
                </form>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var map = L.map('map').setView([38.1384, 39.4475], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        var marker = L.marker([38.1384, 39.4475], {draggable: true}).addTo(map);

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
    </script>
</body>
</html>
