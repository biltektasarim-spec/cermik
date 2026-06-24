<?php
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Sadece Super Admin girebilir
if ($_SESSION['admin_role'] !== 'SUPER_ADMIN') {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

function createSlug($string) {
    $search = ['ş', 'Ş', 'ı', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'ç', 'Ç'];
    $replace = ['s', 'S', 'i', 'I', 'g', 'G', 'u', 'U', 'o', 'O', 'c', 'C'];
    $string = str_replace($search, $replace, $string);
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

function recursiveCopy($src, $dst, $new_slug, $new_id, $yatay_menu_slug, $yatay_menu_title) {
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recursiveCopy($src . '/' . $file, $dst . '/' . $file, $new_slug, $new_id, $yatay_menu_slug, $yatay_menu_title);
            } else {
                // If the file is kaplica.php, we rename it to the new horizontal menu slug
                $dest_file = $file;
                if ($file === 'kaplica.php') {
                    $dest_file = $yatay_menu_slug . '.php';
                }

                $content = file_get_contents($src . '/' . $file);
                
                // --- Global Replacements for automation ---
                // Replace district_id
                $content = str_replace(['$district_id = 3;', 'district_id = 3', 'district_id=3'], ["\$district_id = $new_id;", "district_id = $new_id", "district_id=$new_id"], $content);
                
                // Replace slug in data attributes and variables
                $content = str_replace(['data-district-slug="cermik"', "'cermik'", '"cermik"'], ['data-district-slug="'.$new_slug.'"', "'$new_slug'", '"'.$new_slug.'"'], $content);

                // Specific Replacements for Main Files
                if ($file === 'index.php') {
                    // Update old patterns if they exist
                    $content = str_replace("app.navigateTo('hotspring')", "window.location.href='$yatay_menu_slug.php'", $content);
                    $content = str_replace("'Kaplıcalar Diyarı'", "'$yatay_menu_title'", $content);
                    // Update new dynamic banner url (which uses 'url' => 'kaplica.php')
                    $content = str_replace("'url' => 'kaplica.php'", "'url' => '$yatay_menu_slug.php'", $content);
                }

                if ($file === 'kaplica.php') {
                    // Sadece linkleri ve dosya isimlerini güncelle, metinleri veritabanından çeksin ki İngilizce dosyaları bozulmasın
                    $content = str_replace('Cermik Kaplicasi', $yatay_menu_slug, $content);
                }

                file_put_contents($dst . '/' . $dest_file, $content);
            }
        }
    }
    closedir($dir);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $lat_raw = trim($_POST['lat'] ?? '');
    $lng_raw = trim($_POST['lng'] ?? '');
    
    // Virgülleri noktaya çevirip float'a zorluyoruz
    $lat = $lat_raw !== '' ? floatval(str_replace(',', '.', $lat_raw)) : null;
    $lng = $lng_raw !== '' ? floatval(str_replace(',', '.', $lng_raw)) : null;
    $mayor_name = trim($_POST['mayor_name'] ?? '');
    $mayor_title = trim($_POST['mayor_title'] ?? '');
    $yatay_menu_title = trim($_POST['yatay_menu_title'] ?? '');
    
    if (empty($slug)) {
        $slug = createSlug($name);
    }
    
    if (empty($yatay_menu_title)) {
        $yatay_menu_title = "Yatay Menü";
    }
    $yatay_menu_slug = createSlug($yatay_menu_title);

    if ($name && $slug) {
        $check = $pdo->prepare("SELECT id FROM districts WHERE slug = ?");
        $check->execute([$slug]);
        if ($check->fetch()) {
            $error = "Bu slug ile zaten bir ilçe kayıtlı.";
        } else {
            try {
                $pdo->beginTransaction();
                
                $insert = $pdo->prepare("INSERT INTO districts (name, slug, lat, lng, is_active, mayor_name, mayor_title) VALUES (?, ?, ?, ?, 1, ?, ?)");
                $insert->execute([$name, $slug, $lat, $lng, $mayor_name, $mayor_title]);
                $new_district_id = $pdo->lastInsertId();
                
                // Klasör oluşturma ve kopyalama işlemi
                $base_dir = dirname(__DIR__); // /REHBER
                $source_dir = $base_dir . '/cermik';
                $target_dir = $base_dir . '/' . $slug;
                
                if (file_exists($target_dir)) {
                    // Eğer klasör manuel oluşturulmuşsa hata verme, sadece DB kaydını yap.
                    $pdo->commit();
                    $success = "İlçe veritabanına başarıyla kaydedildi! ($slug) klasörü zaten mevcut olduğu için kopyalama atlandı.";
                } else if (!file_exists($source_dir)) {
                    $error = "Şablon klasörü (cermik) bulunamadı.";
                    $pdo->rollBack();
                } else {
                    recursiveCopy($source_dir, $target_dir, $slug, $new_district_id, $yatay_menu_slug, $yatay_menu_title);
                    $pdo->commit();
                    $success = "İlçe başarıyla oluşturuldu! Klasör ve dosyalar otomatik olarak ayarlandı.";
                }
                
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = "Bir hata oluştu: " . $e->getMessage();
            }
        }
    } else {
        $error = "Lütfen ilçe adı ve slug (veya boş bırakın) girin.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni İlçe Ekle - S.Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-group margin-bottom: 15px; 
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>Yeni İlçe Ekle (Otomatik Kurulum)</h1>
                <p style="color: var(--text-muted);">Bu sistem ile veritabanı kaydı ve klasör/dosya kopyalama işlemleri otomatik olarak yapılır.</p>
            </div>
            <div class="header-right">
                <a href="districts_manage.php" class="btn" style="background:#7f8c8d; color:white;"><i class="fa-solid fa-arrow-left"></i> İlçelere Dön</a>
            </div>
        </header>

        <main class="page-content">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?> <br><br>
                    <a href="districts_manage.php" class="btn btn-primary">İlçe Listesine Dön</a>
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="card" style="max-width: 600px; margin: 0 auto; background: #fdfdfd; border-top: 4px solid #2ecc71;">
                    <h2>İlçe Bilgileri</h2>
                    <form method="POST">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label>İlçe Adı *</label>
                            <input type="text" name="name" required placeholder="Örn: Ergani">
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label>URL Slug (Otomatik oluşturulması için boş bırakın)</label>
                            <input type="text" name="slug" placeholder="Örn: ergani">
                        </div>

                        <div class="form-group" style="margin-bottom: 15px; background: rgba(0, 201, 255, 0.1); padding: 15px; border-radius: 8px; border: 1px solid rgba(0, 201, 255, 0.3);">
                            <label style="color: #008cb3;">Yatay Menü (Eski 'Kaplıca' Alanı) Başlığı *</label>
                            <span style="font-size: 0.8rem; color: #666; display: block; margin-bottom: 8px;">Çermik'teki 'Kaplıcalar Diyarı' gibi bu ilçeye has ana (yatay) menünün ismini belirleyin. Dosya bu isme göre oluşturulacaktır.</span>
                            <input type="text" name="yatay_menu_title" required placeholder="Örn: Tarihi Makam Dağı">
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label>Enlem (Latitude)</label>
                            <input type="text" name="lat" placeholder="Örn: 38.267">
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label>Boylam (Longitude)</label>
                            <input type="text" name="lng" placeholder="Örn: 39.761">
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label>Belediye Başkanı Adı</label>
                            <input type="text" name="mayor_name" placeholder="Örn: Ahmet Yılmaz">
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label>Belediye Başkanı Unvanı</label>
                            <input type="text" name="mayor_title" placeholder="Örn: Ergani Belediye Başkanı">
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 12px;"><i class="fa-solid fa-cogs"></i> İlçeyi Otomatik Kur</button>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>

</body>
</html>
