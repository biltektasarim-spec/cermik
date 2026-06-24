<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['business_id'])) {
    header("Location: login.php");
    exit;
}

$business_id = $_SESSION['business_id'];

// Ürün Ekleme İşlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    csrf_verify();
    $name  = trim($_POST['name']  ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $desc  = trim($_POST['desc']  ?? '');
    $image_path = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_error = '';
        if (!validate_uploaded_image($_FILES['image'], $upload_error)) {
            $error = $upload_error;
        } else {
            $dir = "../uploads/products/";
            if (!is_dir($dir)) {
                if (!@mkdir($dir, 0755, true)) {
                    $error = "Yükleme dizini oluşturulamadı: " . $dir;
                }
            }
            
            if (!isset($error)) {
                if (!is_writable($dir)) {
                    $error = "Yükleme dizini yazılabilir değil: " . $dir . ". Lütfen klasör izinlerini (CHMOD 755 veya 777) kontrol edin.";
                } else {
                    $filename = safe_upload_filename($_FILES['image']);
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $dir . $filename)) {
                        $image_path = "uploads/products/" . $filename;
                    } else {
                        $error = "Dosya taşınırken bir hata oluştu. Sunucu izinlerini kontrol edin.";
                    }
                }
            }
        }
    }

    if (!isset($error)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO products (business_id, name, price, description, image_path) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$business_id, $name, $price, $desc, $image_path]);
            header('Location: index.php?msg=added');
            exit;
        } catch (PDOException $e) {
            error_log('[REHBER product add] ' . $e->getMessage());
            $error = 'Veritabanı hatası.';
        }
    }
}

// Konum ve Panorama Güncelleme İşlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_location'])) {
    csrf_verify();
    $lat = (float)($_POST['lat'] ?? 0);
    $lng = (float)($_POST['lng'] ?? 0);
    $panorama_360 = trim($_POST['panorama_360'] ?? '');
    // Panorama URL güvenlik kontrolü
    if ($panorama_360 !== '' && !filter_var($panorama_360, FILTER_VALIDATE_URL)) {
        $panorama_360 = '';
    }
    $phone = trim($_POST['phone'] ?? '');
    $stmt = $pdo->prepare('UPDATE businesses SET lat = ?, lng = ?, panorama_360 = ?, phone = ? WHERE id = ?');
    $stmt->execute([$lat, $lng, $panorama_360, $phone, $business_id]);
    header('Location: index.php?msg=updated');
    exit;
}

// Otel Bilgisi Güncelleme İşlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_hotel_info'])) {
    csrf_verify();
    $currStmt = $pdo->prepare('SELECT hotel_info FROM businesses WHERE id = ?');
    $currStmt->execute([$business_id]);
    $currInfo = json_decode($currStmt->fetchColumn() ?? '{}', true) ?: [];

    $allowed_parking = ['Ücretsiz', 'Ücretli', 'Yok'];
    $allowed_pool    = ['Var', 'Yok'];
    $allowed_ac      = ['Tüm Odalarda', 'Yok'];

    $info = [
        'Yıldız Sayısı' => $_POST['stars']   ?? ($currInfo['Yıldız Sayısı'] ?? ''),
        'Oda Sayısı'   => trim($_POST['rooms']   ?? ($currInfo['Oda Sayısı']   ?? '')),
        'Wifi'         => trim($_POST['wifi']    ?? ($currInfo['Wifi']         ?? '')),
        'Otopark'      => in_array($_POST['parking'] ?? '', $allowed_parking) ? $_POST['parking'] : ($currInfo['Otopark'] ?? ''),
        'Kahvaltı'    => isset($_POST['breakfast_cb']) ? 'Dahil' : 'Dahil Değil',
        'Havuz'        => in_array($_POST['pool'] ?? '', $allowed_pool) ? $_POST['pool'] : ($currInfo['Havuz'] ?? ''),
        'Klima'        => in_array($_POST['ac']  ?? '', $allowed_ac)   ? $_POST['ac']  : ($currInfo['Klima'] ?? ''),
        'Öğle Yemeği'  => isset($_POST['lunch_cb'])    ? 'Dahil' : '',
        'Akşam Yemeği' => isset($_POST['dinner_cb'])   ? 'Dahil' : '',
        'Wi-Fi'        => isset($_POST['wifi_cb'])     ? 'Var'    : '',
    ];
    $info_json = json_encode($info, JSON_UNESCAPED_UNICODE);
    $pdo->prepare('UPDATE businesses SET hotel_info = ? WHERE id = ?')->execute([$info_json, $business_id]);
    header('Location: index.php?msg=updated');
    exit;
}

// Çalışma Saatleri Güncelleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_working_hours'])) {
    csrf_verify();
    $days  = isset($_POST['days']) ? array_filter(array_map('intval', $_POST['days']), function($d) { return $d >= 0 && $d <= 6; }) : [];
    $open  = preg_match('/^\d{2}:\d{2}$/', $_POST['open_time']  ?? '') ? $_POST['open_time']  : '09:00';
    $close = preg_match('/^\d{2}:\d{2}$/', $_POST['close_time'] ?? '') ? $_POST['close_time'] : '22:00';
    $wh = json_encode(['days' => array_values($days), 'open' => $open, 'close' => $close], JSON_UNESCAPED_UNICODE);
    $pdo->prepare('UPDATE businesses SET working_hours = ? WHERE id = ?')->execute([$wh, $business_id]);
    header('Location: index.php?msg=updated');
    exit;
}

// Ürün Silme İşlemi (POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product_id'])) {
    csrf_verify();
    $p_id = safe_id($_POST['delete_product_id']);
    if ($p_id > 0) {
        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ? AND business_id = ?');
        $stmt->execute([$p_id, $business_id]);
    }
    header('Location: index.php');
    exit;
}

// Ürünleri Çek
$stmt = $pdo->prepare("SELECT * FROM products WHERE business_id = ?");
$stmt->execute([$business_id]);
$products = $stmt->fetchAll();

// İşletme Bilgilerini Çek
$stmt = $pdo->prepare("SELECT * FROM businesses WHERE id = ?");
$stmt->execute([$business_id]);
$business_data = $stmt->fetch();

// Analitik Verilerini Çek
$month_start = date('Y-m-01 00:00:00');
$year_start  = date('Y-01-01 00:00:00');

function getStat($pdo, $bid, $type, $start) {
    $s = $pdo->prepare("SELECT COUNT(*) FROM business_stats WHERE business_id = ? AND event_type = ? AND created_at >= ?");
    $s->execute([$bid, $type, $start]);
    return (int)$s->fetchColumn();
}

$v_m = getStat($pdo, $business_id, 'view', $month_start);
$v_y = getStat($pdo, $business_id, 'view', $year_start);
$d_m = getStat($pdo, $business_id, 'direction', $month_start);
$d_y = getStat($pdo, $business_id, 'direction', $year_start);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İşletme Paneli - <?php echo $_SESSION['business_name']; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .product-item { background: var(--card-bg); padding: 15px; border-radius: 15px; margin-bottom: 15px; display: flex; gap: 15px; align-items: center; border: 1px solid rgba(255,255,255,0.05); }
        .product-img { width: 80px; height: 80px; border-radius: 10px; object-fit: cover; }
        .form-add { margin-bottom: 30px; background: var(--glass-bg); padding: 20px; border-radius: var(--radius); }
        input, textarea, select { width: 100%; padding: 12px; margin-bottom: 10px; border-radius: 10px; border: 1px solid var(--glass-bg); background: var(--card-bg); color: white; }
        #map { height: 300px; border-radius: 15px; margin-bottom: 15px; }
        .btn-delete { color: #f56565; cursor: pointer; border: none; background: transparent; }
        /* Analitik Kartları */
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; }
        .stat-card { background: var(--glass-bg); padding: 15px; border-radius: 20px; border: 1px solid rgba(0, 201, 255, 0.2); text-align: center; }
        .stat-card i { font-size: 1.5rem; color: var(--secondary); margin-bottom: 10px; }
        .stat-card .val { font-size: 1.8rem; font-weight: 800; display: block; }
        .stat-card .lbl { font-size: 0.75rem; opacity: 0.7; }
        /* Çalışma Saatleri */
        .day-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; margin-bottom: 15px; }
        .day-box { text-align: center; cursor: pointer; }
        .day-box input[type=checkbox] { display: none; }
        .day-box label {
            display: block; padding: 8px 4px; border-radius: 10px;
            background: rgba(255,255,255,0.07); font-size: 0.7rem; font-weight: 700;
            cursor: pointer; border: 2px solid transparent; transition: all 0.2s;
        }
        .day-box input:checked + label {
            background: var(--secondary); color: #000;
            border-color: var(--secondary); box-shadow: 0 0 10px rgba(255,200,0,0.4);
        }
        .time-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .time-row label { font-size: 0.8rem; display: block; margin-bottom: 4px; opacity: 0.8; }
        .time-row input[type=time] { padding: 10px; color-scheme: dark; }
    </style>
</head>
<body>
<div id="app">
    <header class="header">
        <h1><?php echo e($_SESSION['business_name']); ?></h1>
        <a href="logout.php" style="color: var(--text-secondary);"><i class="fa-solid fa-right-from-bracket"></i> Çıkış</a>
    </header>

    <main class="section">
        
        <!-- Performans Özet -->
        <h2 style="margin-bottom: 15px;"><i class="fa-solid fa-chart-line" style="color: var(--secondary);"></i> Performans Özet</h2>
        <div class="stats-grid animate-in">
            <div class="stat-card">
                <i class="fa-solid fa-eye"></i>
                <span class="val"><?php echo $v_m; ?></span>
                <span class="lbl">Aylık Görüntülenme</span>
                <hr style="opacity:0.1; margin:10px 0;">
                <small style="opacity: 0.6; font-size: 0.7rem;">Yıllık: <?php echo $v_y; ?></small>
            </div>
            <div class="stat-card">
                <i class="fa-solid fa-route"></i>
                <span class="val"><?php echo $d_m; ?></span>
                <span class="lbl">Aylık Yol Tarifi</span>
                <hr style="opacity:0.1; margin:10px 0;">
                <small style="opacity: 0.6; font-size: 0.7rem;">Yıllık: <?php echo $d_y; ?></small>
            </div>
        </div>
        
        <div class="card animate-in">
            <h3><i class="fa-solid fa-location-dot"></i> Konum Bilginiz</h3>
            <p style="font-size: 0.8rem; opacity: 0.7; margin-bottom: 10px;">Harita üzerinden işletmenizin konumunu işaretleyerek güncelleyebilirsiniz.</p>
            <div id="map"></div>
            <form method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="update_location" value="1">
                <input type="hidden" name="lat" id="lat" value="<?php echo $business_data['lat']; ?>">
                <input type="hidden" name="lng" id="lng" value="<?php echo $business_data['lng']; ?>">
                
                <div style="margin-top: 15px;">
                    <label style="font-size: 0.85rem; display: block; margin-bottom: 5px;"><i class="fa-solid fa-vr-cardboard"></i> 360 Derece Görünüm (Insta360 Linki)</label>
                    <input type="text" name="panorama_360" value="<?php echo htmlspecialchars($business_data['panorama_360']); ?>" placeholder="https://s.insta360.com/p/...">
                </div>

                <div style="margin-top: 15px;">
                    <label style="font-size: 0.85rem; display: block; margin-bottom: 5px;"><i class="fa-solid fa-phone"></i> Telefon Numarası</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($business_data['phone'] ?? ''); ?>" placeholder="05xx xxx xx xx">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Değişiklikleri Kaydet</button>
            </form>
        </div>

        <?php if ($business_data['category'] == 'Hotel'): ?>
        <div class="card animate-in" style="margin-top: 20px;">
            <h3><i class="fa-solid fa-hotel"></i> Otel Bilgilerini Düzenle</h3>
            <?php $h_info = json_decode($business_data['hotel_info'], true) ?: []; ?>
            <form method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="update_hotel_info" value="1">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label style="font-size: 0.8rem;">Yıldız Sayısı</label>
                        <select name="stars">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <option value="<?php echo $i; ?> Yıldız" <?php echo ($h_info['Yıldız Sayısı'] ?? '') == $i.' Yıldız' ? 'selected' : ''; ?>><?php echo $i; ?> Yıldız</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 0.8rem;">Oda Sayısı</label>
                        <input type="text" name="rooms" value="<?php echo $h_info['Oda Sayısı'] ?? ''; ?>" placeholder="Örn: 20 Oda">
                    </div>
                    <div>
                        <label style="font-size: 0.8rem;">Otopark</label>
                        <select name="parking">
                            <option value="Ücretsiz" <?php echo ($h_info['Otopark'] ?? '') == 'Ücretsiz' ? 'selected' : ''; ?>>Ücretsiz</option>
                            <option value="Üretli" <?php echo ($h_info['Otopark'] ?? '') == 'Üretli' ? 'selected' : ''; ?>>Üretli</option>
                            <option value="Yok" <?php echo ($h_info['Otopark'] ?? '') == 'Yok' ? 'selected' : ''; ?>>Yok</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 0.8rem;">Havuz</label>
                        <select name="pool">
                            <option value="Var" <?php echo ($h_info['Havuz'] ?? '') == 'Var' ? 'selected' : ''; ?>>Var</option>
                            <option value="Yok" <?php echo ($h_info['Havuz'] ?? '') == 'Yok' ? 'selected' : ''; ?>>Yok</option>
                        </select>
                    </div>
                    <div style="grid-column: span 2;">
                        <label style="font-size: 0.8rem;">Klima</label>
                        <select name="ac">
                            <option value="Tüm Odalarda" <?php echo ($h_info['Klima'] ?? '') == 'Tüm Odalarda' ? 'selected' : ''; ?>>Tüm Odalarda</option>
                            <option value="Yok" <?php echo ($h_info['Klima'] ?? '') == 'Yok' ? 'selected' : ''; ?>>Yok</option>
                        </select>
                    </div>
                </div>
                
                <!-- Checkbox Tesisler -->
                <div style="margin-top: 15px; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 12px;">
                    <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 12px;">
                        <i class="fa-solid fa-list-check" style="color: var(--secondary);"></i> Tesisler
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.9rem;">
                            <input type="checkbox" name="wifi_cb" value="1" 
                                   <?php echo !empty($h_info['Wi-Fi']) ? 'checked' : ''; ?>
                                   style="width:auto; margin:0; accent-color: var(--secondary);">
                            <i class="fa-solid fa-wifi" style="color:var(--secondary);"></i> Wi-Fi
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.9rem;">
                            <input type="checkbox" name="breakfast_cb" value="1" 
                                   <?php echo (!empty($h_info['Kahvaltı']) && $h_info['Kahvaltı'] != 'Dahil Değil') ? 'checked' : ''; ?>
                                   style="width:auto; margin:0; accent-color: var(--secondary);">
                            <i class="fa-solid fa-mug-hot" style="color:var(--secondary);"></i> Kahvaltı
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.9rem;">
                            <input type="checkbox" name="lunch_cb" value="1" 
                                   <?php echo !empty($h_info['Öğle Yemeği']) ? 'checked' : ''; ?>
                                   style="width:auto; margin:0; accent-color: var(--secondary);">
                            <i class="fa-solid fa-sun" style="color:var(--secondary);"></i> Öğle Yemeği
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:0.9rem;">
                            <input type="checkbox" name="dinner_cb" value="1" 
                                   <?php echo !empty($h_info['Akşam Yemeği']) ? 'checked' : ''; ?>
                                   style="width:auto; margin:0; accent-color: var(--secondary);">
                            <i class="fa-solid fa-moon" style="color:var(--secondary);"></i> Akşam Yemeği
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 15px;">Otel Bilgilerini Kaydet</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Çalışma Saatleri Bölümü (Restaurant ve Hotel) -->
        <?php if ($business_data['category'] == 'Restaurant' || $business_data['category'] == 'Hotel'): ?>
        <?php
            $wh = json_decode($business_data['working_hours'] ?? '{}', true) ?: [];
            $wh_days  = $wh['days']  ?? [];
            $wh_open  = $wh['open']  ?? '09:00';
            $wh_close = $wh['close'] ?? '22:00';
            $day_names = ['Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt'];
        ?>
        <div class="card animate-in" style="margin-top: 20px;">
            <h3><i class="fa-solid fa-clock" style="color: var(--secondary);"></i> Çalışma Saatleri</h3>
            <p style="font-size: 0.8rem; opacity: 0.7; margin-bottom: 15px;">İşletmenizin açık olduğu günleri ve saatleri belirleyin.</p>
            <form method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="update_working_hours" value="1">
                
                <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 10px;">
                    <i class="fa-solid fa-calendar-days" style="color: var(--secondary);"></i> Açık Günler
                </label>
                <div class="day-grid">
                    <?php foreach ($day_names as $di => $dn): ?>
                    <div class="day-box">
                        <input type="checkbox" name="days[]" value="<?php echo $di; ?>" id="day_<?php echo $di; ?>"
                               <?php echo in_array($di, $wh_days) ? 'checked' : ''; ?>>
                        <label for="day_<?php echo $di; ?>"><?php echo $dn; ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="time-row">
                    <div>
                        <label><i class="fa-solid fa-door-open"></i> Açılış Saati</label>
                        <input type="time" name="open_time" value="<?php echo htmlspecialchars($wh_open); ?>">
                    </div>
                    <div>
                        <label><i class="fa-solid fa-door-closed"></i> Kapanış Saati</label>
                        <input type="time" name="close_time" value="<?php echo htmlspecialchars($wh_close); ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 5px;">
                    <i class="fa-solid fa-floppy-disk"></i> Çalışma Saatlerini Kaydet
                </button>
            </form>
        </div>
        <?php endif; ?>

        <hr style="margin: 30px 0; opacity: 0.1;">

        <div class="form-add animate-in">
            <h3><i class="fa-solid fa-plus"></i> Yeni <?php echo $business_data['category'] == 'Restaurant' ? 'Yemek' : 'Hizmet'; ?> Ekle</h3>
            <form method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="add_product" value="1">
                <input type="text" name="name" placeholder="Adı" required maxlength="200">
                <input type="number" step="0.01" min="0" name="price" placeholder="Fiyat" required>
                <textarea name="desc" placeholder="Açıklama (opsiyonel)" maxlength="1000"></textarea>
                <label style="font-size: 0.8rem; display: block; margin-bottom: 5px;">Görsel Seçin:</label>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                <button type="submit" class="btn btn-primary">Ekle</button>
            </form>
        </div>

        <h2>Mevcut Ürünleriniz</h2>
        <div id="product-list">
            <?php foreach ($products as $p): ?>
                <div class="product-item animate-in">
                    <img src="../<?php echo $p['image_path'] ?? 'assets/img/placeholder_food.jpg'; ?>" class="product-img" onerror="this.src='https://via.placeholder.com/150?text=Gorsel+Yok'">
                    <div style="flex: 1;">
                        <h4 style="margin: 0;"><?php echo e($p['name']); ?></h4>
                        <small style="color: var(--text-secondary);"><?php echo e($p['description']); ?></small>
                        <div style="font-weight: 700; color: var(--secondary); margin-top: 5px;"><?php echo number_format($p['price'], 2); ?> TL</div>
                    </div>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Silmek istediğinize emin misiniz?')">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="delete_product_id" value="<?php echo (int)$p['id']; ?>">
                        <button type="submit" class="btn-delete"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var lat = <?php echo $business_data['lat'] ?? '38.1384'; ?>;
        var lng = <?php echo $business_data['lng'] ?? '39.4475'; ?>;
        
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
    </script>
</div>
</body>
</html>
