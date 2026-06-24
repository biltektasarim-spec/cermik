<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Settings Save
if (isset($_POST['save_titles'])) {
    csrf_verify();
    $tr = trim($_POST['title_tr'] ?? '');
    $en = trim($_POST['title_en'] ?? '');
    $pdo->prepare("INSERT INTO settings (name, value, district_id) VALUES ('menu_businesses_tr', ?, $admin_district_id) ON DUPLICATE KEY UPDATE value=VALUES(value)")->execute([$tr]);
    $pdo->prepare("INSERT INTO settings (name, value, district_id) VALUES ('menu_businesses_en', ?, $admin_district_id) ON DUPLICATE KEY UPDATE value=VALUES(value)")->execute([$en]);

    $target_dir = "../uploads/categories/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    // Hotel Icon Upload
    if (isset($_FILES['hotel_img']) && $_FILES['hotel_img']['error'] == 0) {
        $file_ext = pathinfo($_FILES['hotel_img']['name'], PATHINFO_EXTENSION);
        $new_filename = 'cat_hotel_' . $admin_district_id . '_' . time() . '.' . $file_ext;
        if (move_uploaded_file($_FILES['hotel_img']['tmp_name'], $target_dir . $new_filename)) {
            $img_path = 'uploads/categories/' . $new_filename;
            $pdo->prepare("INSERT INTO settings (name, value, district_id) VALUES ('menu_hotels_img', ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)")->execute([$img_path, $admin_district_id]);
        }
    }

    // Restaurant Icon Upload
    if (isset($_FILES['rest_img']) && $_FILES['rest_img']['error'] == 0) {
        $file_ext = pathinfo($_FILES['rest_img']['name'], PATHINFO_EXTENSION);
        $new_filename = 'cat_rest_' . $admin_district_id . '_' . time() . '.' . $file_ext;
        if (move_uploaded_file($_FILES['rest_img']['tmp_name'], $target_dir . $new_filename)) {
            $img_path = 'uploads/categories/' . $new_filename;
            $pdo->prepare("INSERT INTO settings (name, value, district_id) VALUES ('menu_restaurants_img', ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)")->execute([$img_path, $admin_district_id]);
        }
    }

    header('Location: businesses.php?msg=saved');
    exit;
}

// Silme işlemi (POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    try {
        csrf_verify();
        $id = safe_id($_POST['delete_id']);
        if ($id > 0) {
            // Debug: Silme öncesi kontrol
            $stmt_check = $pdo->prepare("SELECT id FROM businesses WHERE id = ? AND ($admin_filter)");
            $stmt_check->execute([$id]);
            if (!$stmt_check->fetch()) {
                throw new Exception("İşletme bulunamadı veya bu işlemi yapmaya yetkiniz yok.");
            }

            $stmt = $pdo->prepare("DELETE FROM businesses WHERE id = ? AND ($admin_filter)");
            $stmt->execute([$id]);
        }
        header('Location: businesses.php?msg=deleted');
        exit;
    } catch (PDOException $pe) {
        die("Veritabanı Hatası: " . $pe->getMessage() . " (Kod: " . $pe->getCode() . ")");
    } catch (Exception $e) {
        die("Hata: " . $e->getMessage());
    }
}

$stmt_s = $pdo->prepare("SELECT value FROM settings WHERE name = 'menu_businesses_tr' AND (district_id = ? OR district_id = 0) ORDER BY district_id DESC LIMIT 1");
$stmt_s->execute([$admin_district_id]);
$val_tr = $stmt_s->fetchColumn() ?: 'İşletmeler';

$stmt_en = $pdo->prepare("SELECT value FROM settings WHERE name = 'menu_businesses_en' AND (district_id = ? OR district_id = 0) ORDER BY district_id DESC LIMIT 1");
$stmt_en->execute([$admin_district_id]);
$val_en = $stmt_en->fetchColumn() ?: 'Businesses';

$stmt_h_img = $pdo->prepare("SELECT value FROM settings WHERE name = 'menu_hotels_img' AND (district_id = ? OR district_id = 0) ORDER BY district_id DESC LIMIT 1");
$stmt_h_img->execute([$admin_district_id]);
$current_h_img = $stmt_h_img->fetchColumn() ?: '../assets/img/categories/hotels.jpg';
if (strpos($current_h_img, 'uploads/') === 0) $current_h_img = '../' . $current_h_img;

$stmt_r_img = $pdo->prepare("SELECT value FROM settings WHERE name = 'menu_restaurants_img' AND (district_id = ? OR district_id = 0) ORDER BY district_id DESC LIMIT 1");
$stmt_r_img->execute([$admin_district_id]);
$current_r_img = $stmt_r_img->fetchColumn() ?: '../assets/img/categories/restaurants.jpg';
if (strpos($current_r_img, 'uploads/') === 0) $current_r_img = '../' . $current_r_img;

$month_start = date('Y-m-01 00:00:00');
$stmt_b = $pdo->prepare("
    SELECT b.*, 
    (SELECT COUNT(*) FROM business_stats WHERE business_id = b.id AND event_type = 'view' AND created_at >= ?) as m_views,
    (SELECT COUNT(*) FROM business_stats WHERE business_id = b.id AND event_type = 'direction' AND created_at >= ?) as m_dirs
    FROM businesses b 
    WHERE $admin_filter 
    ORDER BY created_at DESC
");
$stmt_b->execute([$month_start, $month_start]);
$businesses = $stmt_b->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İşletme Yönetimi - Çermik Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>İşletme Yönetimi</h1>
                <p style="color: var(--text-muted);"><?php echo count($businesses); ?> kayıtlı işletme (Otel/Restoran).</p>
            </div>
            <div class="header-right">
                <a href="business_add.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Yeni İşletme Ekle</a>
            </div>
        </header>

        <main class="page-content">
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div style="background: #ffebee; color: #c62828; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    İşletme başarıyla silindi.
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'saved'): ?>
                <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    Başlıklar başarıyla kaydedildi.
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'added'): ?>
                <div style="background: #e8f5e9; padding: 10px; margin-bottom: 20px; border-radius: 5px; color: #2e7d32;">Yeni işletme başarıyla eklendi.</div>
            <?php endif; ?>
            
            <div class="card" style="margin-bottom: 1rem;">
                <form action="" method="POST" enctype="multipart/form-data" style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
                    <?php echo csrf_field(); ?>
                    <div style="flex: 1; min-width: 200px;">
                        <label style="display: block; font-size: 0.8rem; margin-bottom: 5px;">Kategori Adı (TR)</label>
                        <input type="text" name="title_tr" value="<?php echo e($val_tr); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 8px;">
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <label style="display: block; font-size: 0.8rem; margin-bottom: 5px;">Kategori Adı (EN)</label>
                        <input type="text" name="title_en" value="<?php echo e($val_en); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 8px;">
                    </div>
                    
                    <div style="flex: 1; min-width: 250px;">
                        <label style="display: block; font-size: 0.8rem; margin-bottom: 5px;">Otel/Pansiyon Görseli</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <img src="<?php echo $current_h_img; ?>" style="width: 40px; height: 40px; border-radius: 4px; object-fit: cover; border: 1px solid #ddd;">
                            <input type="file" name="hotel_img" accept="image/*" style="font-size: 0.8rem;">
                        </div>
                    </div>

                    <div style="flex: 1; min-width: 250px;">
                        <label style="display: block; font-size: 0.8rem; margin-bottom: 5px;">Lokanta/Restoran Görseli</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <img src="<?php echo $current_r_img; ?>" style="width: 40px; height: 40px; border-radius: 4px; object-fit: cover; border: 1px solid #ddd;">
                            <input type="file" name="rest_img" accept="image/*" style="font-size: 0.8rem;">
                        </div>
                    </div>

                    <div>
                        <button type="submit" name="save_titles" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
            
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>İşletme Adı</th>
                            <th>Kategori</th>
                            <th>Aylık Tık / Yol</th>
                            <th>Kullanıcı Adı</th>
                            <th>Kayıt Tarihi</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($businesses as $b): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($b['business_name']); ?></strong></td>
                            <td><?php echo $b['category'] == 'Restaurant' ? 'Restoran' : 'Otel'; ?></td>
                            <td>
                                <span title="Görüntülenme" style="color:#00c9ff;"><i class="fa-solid fa-eye"></i> <?php echo $b['m_views']; ?></span> 
                                <span style="opacity:0.3; margin:0 5px;">|</span>
                                <span title="Yol Tarifi" style="color:#2ecc71;"><i class="fa-solid fa-route"></i> <?php echo $b['m_dirs']; ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($b['username']); ?></td>
                            <td><?php echo date('d.m.Y', strtotime($b['created_at'])); ?></td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="business_edit.php?id=<?php echo $b['id']; ?>" class="btn" style="padding: 0.4rem 0.8rem; border: 1px solid #ddd; color: var(--accent-color);"><i class="fa-solid fa-pen"></i></a>
                                    <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Bu işletmeyi silmek istediğinize emin misiniz?')">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="delete_id" value="<?php echo (int)$b['id']; ?>">
                                        <button type="submit" class="btn" style="padding: 0.4rem 0.8rem; border: 1px solid #ddd; color: #e74c3c;"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>
</html>
