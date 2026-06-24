<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM pharmacies WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) die("Kayıt bulunamadı.");
$admin_restricted_district = intval($_SESSION['admin_district_id'] ?? 0);
if ($admin_restricted_district > 0 && $p['district_id'] != $admin_restricted_district) {
    die("Yetkisiz erişim.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $is_on_duty = isset($_POST['is_on_duty']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE pharmacies SET name = ?, phone = ?, address = ?, lat = ?, lng = ?, is_on_duty = ? WHERE id = ?");
    $stmt->execute([$name, $phone, $address, $lat, $lng, $is_on_duty, $id]);
    header("Location: hospital_pharmacy.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eczane Düzenle - Çermik</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <header>
            <h1>Eczane Düzenle</h1>
            <a href="hospital_pharmacy.php" class="btn">Geri Dön</a>
        </header>

        <main class="page-content">
            <div class="card">
                <form method="POST">
                    <div class="form-group">
                        <label>Eczane Adı</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($p['name']); ?>" required style="width:100%; padding:10px;">
                    </div>
                    <div class="form-group">
                        <label>Telefon</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($p['phone']); ?>" style="width:100%; padding:10px;">
                    </div>
                    <div class="form-group">
                        <label>Adres</label>
                        <textarea name="address" style="width:100%; padding:10px; height:80px;"><?php echo htmlspecialchars($p['address']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_on_duty" <?php echo $p['is_on_duty'] ? 'checked' : ''; ?>> Şu an Nöbetçi
                        </label>
                    </div>
                    
                    <label>Konum Seçin</label>
                    <div id="map" style="height: 300px; margin-bottom: 20px; border-radius: 10px;"></div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <input type="text" name="lat" id="lat" value="<?php echo $p['lat']; ?>">
                        <input type="text" name="lng" id="lng" value="<?php echo $p['lng']; ?>">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top:20px;">Güncelle</button>
                </form>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var lat = <?php echo $p['lat'] ?: '38.1384'; ?>;
        var lng = <?php echo $p['lng'] ?: '39.4475'; ?>;
        var map = L.map('map').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        var marker = L.marker([lat, lng], {draggable: true}).addTo(map);
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
