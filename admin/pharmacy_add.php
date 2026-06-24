<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $is_on_duty = isset($_POST['is_on_duty']) ? 1 : 0;
    
    // Isolation for regular admins
    $admin_restricted_district = intval($_SESSION['admin_district_id'] ?? 0);
    $district_id = $admin_restricted_district > 0 ? $admin_restricted_district : (int)($_POST['district_id'] ?? 0);

    $stmt = $pdo->prepare("INSERT INTO pharmacies (district_id, name, phone, address, lat, lng, is_on_duty) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$district_id, $name, $phone, $address, $lat, $lng, $is_on_duty]);
    header("Location: hospital_pharmacy.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Eczane Ekle - Çermik</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <header>
            <h1>Yeni Eczane Ekle</h1>
            <a href="hospital_pharmacy.php" class="btn">Geri Dön</a>
        </header>

        <main class="page-content">
            <div class="card">
                <form method="POST">
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
                        <label>Eczane Adı</label>
                        <input type="text" name="name" required style="width:100%; padding:10px;">
                    </div>
                    <div class="form-group">
                        <label>Telefon</label>
                        <input type="text" name="phone" style="width:100%; padding:10px;">
                    </div>
                    <div class="form-group">
                        <label>Adres</label>
                        <textarea name="address" style="width:100%; padding:10px; height:80px;"></textarea>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_on_duty"> Şu an Nöbetçi
                        </label>
                    </div>
                    
                    <label>Konum Seçin</label>
                    <div id="map" style="height: 300px; margin-bottom: 20px; border-radius: 10px;"></div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <input type="text" name="lat" id="lat" value="38.1384">
                        <input type="text" name="lng" id="lng" value="39.4475">
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
            document.getElementById('lat').value = marker.getLatLng().lat;
            document.getElementById('lng').value = marker.getLatLng().lng;
        });
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            document.getElementById('lat').value = e.latlng.lat;
            document.getElementById('lng').value = e.latlng.lng;
        });
    </script>
</body>
</html>
