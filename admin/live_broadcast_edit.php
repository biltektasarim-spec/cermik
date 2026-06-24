<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$district_id = intval($_SESSION['admin_district_id'] ?? 0);
$is_super = ($_SESSION['admin_role'] === 'SUPER_ADMIN');

$msg = '';
$err = '';

// Retrieve record
$broadcast = [
    'title'=>'', 'title_en'=>'', 'description'=>'', 'description_en'=>'',
    'facebook_url'=>'', 'youtube_url'=>'', 'lat'=>'', 'lng'=>'',
    'image'=>'', 'sort_order'=>'0', 'is_active'=>1, 'district_id'=>0
];

if ($id > 0) {
    if ($district_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM live_broadcasts WHERE id = ? AND district_id = ?");
        $stmt->execute([$id, $district_id]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM live_broadcasts WHERE id = ?");
        $stmt->execute([$id]);
    }
    $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($fetched) $broadcast = $fetched;
    else { header("Location: live_broadcasts.php"); exit; }
} else {
    $broadcast['district_id'] = $district_id;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $title_en = trim($_POST['title_en']);
    $description = trim($_POST['description']);
    $description_en = trim($_POST['description_en']);
    $facebook_url = trim($_POST['facebook_url']);
    $youtube_url = trim($_POST['youtube_url']);
    $lat = trim($_POST['lat']);
    $lng = trim($_POST['lng']);
    $sort_order = intval($_POST['sort_order']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $input_district_id = $district_id;
    if ($is_super && isset($_POST['district_id'])) {
        $input_district_id = intval($_POST['district_id']);
    }

    $image = $broadcast['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $filename = 'live_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $upload_path = '../uploads/broadcasts/' . $filename;
            
            if (!is_dir('../uploads/broadcasts/')) {
                mkdir('../uploads/broadcasts/', 0777, true);
            }
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                if ($image && file_exists('../' . $image)) unlink('../' . $image);
                $image = 'uploads/broadcasts/' . $filename;
            } else {
                $err = "Görsel yüklenirken sunucu hatası!";
            }
        } else {
            $err = "Geçersiz dosya türü. (Sadece JPG, PNG, WEBP)";
        }
    }

    if (empty($err)) {
        if ($id > 0) {
            $upd = $pdo->prepare("UPDATE live_broadcasts SET title = ?, title_en = ?, description = ?, description_en = ?, facebook_url = ?, youtube_url = ?, lat = ?, lng = ?, image = ?, sort_order = ?, is_active = ?, district_id = ? WHERE id = ?");
            if($upd->execute([$title, $title_en, $description, $description_en, $facebook_url, $youtube_url, $lat, $lng, $image, $sort_order, $is_active, $input_district_id, $id])) {
                header("Location: live_broadcasts.php?success=1");
                exit;
            }
        } else {
            $ins = $pdo->prepare("INSERT INTO live_broadcasts (title, title_en, description, description_en, facebook_url, youtube_url, lat, lng, image, sort_order, is_active, district_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if($ins->execute([$title, $title_en, $description, $description_en, $facebook_url, $youtube_url, $lat, $lng, $image, $sort_order, $is_active, $input_district_id])) {
                header("Location: live_broadcasts.php?success=1");
                exit;
            }
        }
    }
    
    // repopulate
    $broadcast['title'] = $title;
    $broadcast['title_en'] = $title_en;
    $broadcast['description'] = $description;
    $broadcast['description_en'] = $description_en;
    $broadcast['facebook_url'] = $facebook_url;
    $broadcast['youtube_url'] = $youtube_url;
    $broadcast['lat'] = $lat;
    $broadcast['lng'] = $lng;
    $broadcast['sort_order'] = $sort_order;
    $broadcast['is_active'] = $is_active;
    $broadcast['district_id'] = $input_district_id;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $id > 0 ? "Yayını Düzenle" : "Yeni Yayın Ekle"; ?> - Admin</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="header-actions">
                <h1><?php echo $id > 0 ? "Canlı Yayını Düzenle" : "Yeni Canlı Yayın"; ?></h1>
                <a href="live_broadcasts.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Geri</a>
            </div>

            <?php if (!empty($err)) echo "<div class='alert alert-danger'>$err</div>"; ?>

            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    
                    <?php if($is_super): ?>
                    <div class="form-group">
                        <label>Yayının Bağlı Olduğu İlçe / Genel</label>
                        <select name="district_id" class="form-control">
                            <option value="0">Tüm Sistemde (Genel Seçilebilir)</option>
                            <?php 
                            $dists = $pdo->query("SELECT id, name FROM districts ORDER BY name")->fetchAll();
                            foreach($dists as $d) {
                                $sel = ($broadcast['district_id'] == $d['id']) ? 'selected' : '';
                                echo "<option value='{$d['id']}' $sel>{$d['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div style="display:flex; gap:10px;">
                        <div class="form-group" style="flex:1;">
                            <label>Başlık (TR)*</label>
                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($broadcast['title']); ?>" required>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Başlık (EN)</label>
                            <input type="text" name="title_en" class="form-control" value="<?php echo htmlspecialchars($broadcast['title_en']); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Facebook Canlı Yayın URL (İsteğe Bağlı)</label>
                        <input type="url" name="facebook_url" class="form-control" placeholder="https://www.facebook.com/kullanici/videos/..." value="<?php echo htmlspecialchars($broadcast['facebook_url']); ?>">
                    </div>

                    <div class="form-group">
                        <label>YouTube Canlı Yayın URL (İsteğe Bağlı)</label>
                        <input type="url" name="youtube_url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." value="<?php echo htmlspecialchars($broadcast['youtube_url']); ?>">
                    </div>

                    <div style="display:flex; gap:10px;">
                        <div class="form-group" style="flex:1;">
                            <label>Açıklama (TR)</label>
                            <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($broadcast['description']); ?></textarea>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Açıklama (EN)</label>
                            <textarea name="description_en" class="form-control" rows="4"><?php echo htmlspecialchars($broadcast['description_en']); ?></textarea>
                        </div>
                    </div>
                    
                    <div style="display:flex; gap:10px;">
                        <div class="form-group" style="flex:1;">
                            <label>Google Maps Enlem (Latitude)</label>
                            <input type="text" name="lat" class="form-control" placeholder="39.1234..." value="<?php echo htmlspecialchars($broadcast['lat']); ?>">
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Google Maps Boylam (Longitude)</label>
                            <input type="text" name="lng" class="form-control" placeholder="38.5678..." value="<?php echo htmlspecialchars($broadcast['lng']); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Menü İçin Vitrin Resmi / Thumbnail (URL veya Dosya)</label>
                        <?php if(!empty($broadcast['image'])): ?>
                            <div style="margin-bottom:10px;">
                                <img src="../<?php echo htmlspecialchars($broadcast['image']); ?>" style="max-height:100px; border-radius:5px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small style="color:#666;">Yatay menüde veya detay ekranında en üstte görünecek kapak fotoğrafı. (Video otomatik oynarsa kaybolur)</small>
                    </div>

                    <div style="display:flex; gap:10px; align-items:center;">
                        <div class="form-group" style="width:150px;">
                            <label>Menü Sırası</label>
                            <input type="number" name="sort_order" class="form-control" value="<?php echo intval($broadcast['sort_order']); ?>">
                        </div>

                        <div class="form-group" style="padding-top:20px;">
                            <label style="display:flex; align-items:center; cursor:pointer;">
                                <input type="checkbox" name="is_active" value="1" <?php echo $broadcast['is_active'] ? 'checked' : ''; ?> style="width:20px; height:20px; margin-right:10px;">
                                Yayında / Açık
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top:20px;">
                        <i class="fa-solid fa-save"></i> <?php echo $id > 0 ? "Güncelle" : "Kaydet"; ?>
                    </button>
                    
                </form>
            </div>
        </div>
    </div>
</body>
</html>
