<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM hospitals WHERE id = ?");
$stmt->execute([$id]);
$h = $stmt->fetch();

if (!$h) die("Kayıt bulunamadı.");
$admin_restricted_district = intval($_SESSION['admin_district_id'] ?? 0);
if ($admin_restricted_district > 0 && $h['district_id'] != $admin_restricted_district) {
    die("Yetkisiz erişim.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $name_en = $_POST['name_en'];
    $description = $_POST['description'];
    $description_en = $_POST['description_en'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $panorama_360 = $_POST['panorama_360'];
    
    $image_main = $h['image_main'];
    if (isset($_FILES['image_main']) && $_FILES['image_main']['error'] == 0) {
        $ext = pathinfo($_FILES['image_main']['name'], PATHINFO_EXTENSION);
        $filename = "hospital_" . time() . "." . $ext;
        move_uploaded_file($_FILES['image_main']['tmp_name'], "../uploads/" . $filename);
        $image_main = "uploads/" . $filename;
    }

    $stmt = $pdo->prepare("UPDATE hospitals SET name = ?, name_en = ?, description = ?, description_en = ?, lat = ?, lng = ?, panorama_360 = ?, image_main = ? WHERE id = ?");
    $stmt->execute([$name, $name_en, $description, $description_en, $lat, $lng, $panorama_360, $image_main, $id]);
    header("Location: hospital_pharmacy.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hastane Düzenle - Çermik</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <header>
            <h1>Hastane Düzenle</h1>
            <a href="hospital_pharmacy.php" class="btn">Geri Dön</a>
        </header>

        <main class="page-content">
            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Hastane Adı (TR)</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($h['name']); ?>" required style="width:100%; padding:10px;">
                    </div>
                    <div class="form-group">
                        <label>Hastane Adı (EN)</label>
                        <input type="text" name="name_en" value="<?php echo htmlspecialchars($h['name_en'] ?? ''); ?>" style="width:100%; padding:10px;">
                    </div>
                    <div class="form-group">
                        <label>Açıklama (TR)</label>
                        <textarea name="description" style="width:100%; padding:10px; height:100px;"><?php echo htmlspecialchars($h['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Açıklama (EN)</label>
                        <textarea name="description_en" style="width:100%; padding:10px; height:100px;"><?php echo htmlspecialchars($h['description_en'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Ana Resim</label>
                        <?php if ($h['image_main']): ?>
                            <img src="../<?php echo $h['image_main']; ?>" style="width:100px; display:block; margin-bottom:10px;">
                        <?php endif; ?>
                        <input type="file" name="image_main" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>360 Derece Görsel Linki</label>
                        <input type="text" name="panorama_360" value="<?php echo htmlspecialchars($h['panorama_360']); ?>" placeholder="https://..." style="width:100%; padding:10px;">
                    </div>
                    
                    <label>Konum Seçin</label>
                    <div id="map" style="height: 300px; margin-bottom: 20px; border-radius: 10px;"></div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <input type="text" name="lat" id="lat" value="<?php echo $h['lat']; ?>">
                        <input type="text" name="lng" id="lng" value="<?php echo $h['lng']; ?>">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top:20px;">Güncelle</button>
                </form>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var lat = <?php echo $h['lat'] ?: '38.1384'; ?>;
        var lng = <?php echo $h['lng'] ?: '39.4475'; ?>;
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
