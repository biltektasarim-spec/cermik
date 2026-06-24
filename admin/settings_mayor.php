<?php

require_once '../config.php';
$page = 'settings_mayor';

// Auto-migration (Run setup logic if table doesn't exist)
try {
    $pdo->query("SELECT 1 FROM settings LIMIT 1");
} catch (Exception $e) {
    include '../setup_settings.php';
}

$msg = "";
$district_id = intval($_SESSION['admin_district_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mayor_name     = $_POST['mayor_name'] ?? '';
    $mayor_title    = $_POST['mayor_title'] ?? '';
    $mayor_title_en = $_POST['mayor_title_en'] ?? '';
    
    // Update Name & Title (TR)
    if ($district_id > 0) {
        $stmt = $pdo->prepare("UPDATE districts SET mayor_name = ?, mayor_title = ?, mayor_title_en = ? WHERE id = ?");
        $stmt->execute([$mayor_name, $mayor_title, $mayor_title_en, $district_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE name = 'mayor_name'");
        $stmt->execute([$mayor_name]);
        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE name = 'mayor_title'");
        $stmt->execute([$mayor_title]);
        
        // Update English Title - INSERT OR UPDATE
        $checkEN = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE name = 'mayor_title_en'");
        $checkEN->execute();
        if ($checkEN->fetchColumn() > 0) {
            $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE name = 'mayor_title_en'");
        } else {
            $stmt = $pdo->prepare("INSERT INTO settings (name, value) VALUES ('mayor_title_en', ?)");
        }
        $stmt->execute([$mayor_title_en]);
    }

    // Handle Image Upload
    if (!empty($_FILES['mayor_image']['name'])) {
        $target_dir = "../assets/img/mayors/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = strtolower(pathinfo($_FILES["mayor_image"]["name"], PATHINFO_EXTENSION));
        $base_filename = $district_id > 0 ? "district_" . $district_id : "baskan";
        $filename = $base_filename . "." . $file_ext;
        $target_file = $target_dir . $filename;
        
        // Önceki olası tüm uzantılardaki dosyaları sil
        $possible_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        foreach ($possible_exts as $ext) {
            $old_file = $target_dir . $base_filename . "." . $ext;
            if (file_exists($old_file)) {
                @unlink($old_file);
            }
        }
        
        if (move_uploaded_file($_FILES["mayor_image"]["tmp_name"], $target_file)) {
            $db_image_path = "assets/img/mayors/" . $filename;
            if ($district_id > 0) {
                $stmt = $pdo->prepare("UPDATE districts SET mayor_image = ? WHERE id = ?");
                $stmt->execute([$db_image_path, $district_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE name = 'mayor_image'");
                $stmt->execute([$db_image_path]);
            }
            $msg = "Profil başarıyla güncellendi (Fotoğraf dahil).";
        } else {
            $msg = "Profil bilgileri güncellendi ancak fotoğraf yüklenemedi.";
        }
    } else {
        $msg = "Profil bilgileri başarıyla güncellendi.";
    }
}

// Fetch current values
$settings = [];
if ($district_id > 0) {
    $stmt = $pdo->prepare("SELECT mayor_name, mayor_title, mayor_title_en, mayor_image FROM districts WHERE id = ?");
    $stmt->execute([$district_id]);
    $d_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $settings['mayor_name'] = $d_row['mayor_name'] ?? '';
    $settings['mayor_title'] = $d_row['mayor_title'] ?? '';
    $settings['mayor_title_en'] = $d_row['mayor_title_en'] ?? '';
    $settings['mayor_image'] = $d_row['mayor_image'] ?? 'assets/img/baskan.jpg';
} else {
    $stmt = $pdo->query("SELECT name, value FROM settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['name']] = $row['value'];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Başkan Ayarları - Admin Paneli</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .settings-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); max-width: 600px; margin: 20px auto; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #444; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; }
        .img-container { width: 150px; height: 150px; margin: 0 auto 15px; border-radius: 50%; overflow: hidden; border: 3px solid #eee; }
        .preview-img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <header class="main-header">
                <h2><i class="fa-solid fa-user-tie"></i> Başkan Ayarları</h2>
            </header>

            <div class="content-body">
                <?php if ($msg): ?>
                    <div class="alert alert-success"><?php echo $msg; ?></div>
                <?php endif; ?>

                <div class="settings-card">
                    <form method="POST" enctype="multipart/form-data">
                        <div style="text-align: center; margin-bottom: 30px;">
                            <div class="img-container">
                                <img src="../<?php echo $settings['mayor_image'] ?? 'assets/img/baskan.jpg'; ?>?v=<?php echo time(); ?>" class="preview-img" alt="Başkan">
                            </div>
                            <p style="font-size: 0.8rem; color: #666;">Mevcut Fotoğraf</p>
                        </div>

                        <div class="form-group">
                            <label>Başkanın Adı Soyadı</label>
                            <input type="text" name="mayor_name" value="<?php echo htmlspecialchars($settings['mayor_name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>&#127470;&#127479; Ünvanı (Türkçe)</label>
                            <input type="text" name="mayor_title" value="<?php echo htmlspecialchars($settings['mayor_title'] ?? ''); ?>" required placeholder="örn: Belediye Başkanı">
                        </div>

                        <div class="form-group">
                            <label>&#127468;&#127463; Ünvanı (English title)</label>
                            <input type="text" name="mayor_title_en" value="<?php echo htmlspecialchars($settings['mayor_title_en'] ?? 'Mayor of \u00c7ermik'); ?>" placeholder="e.g. Mayor of \u00c7ermik">
                        </div>

                        <div class="form-group">
                            <label>Yeni Fotoğraf Yükle (Değiştirmek istemiyorsanız boş bırakın)</label>
                            <input type="file" name="mayor_image" accept="image/*">
                        </div>

                        <button type="submit" class="btn-submit" style="width: 100%; padding: 15px; background: #0088cc; color: white; border: none; border-radius: 8px; font-size: 1.1rem; cursor: pointer; font-weight: 600;">
                            <i class="fa-solid fa-save"></i> Ayarları Kaydet
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
