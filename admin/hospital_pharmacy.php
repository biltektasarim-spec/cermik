<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Settings Save
if (isset($_POST['save_titles'])) {
    $tr = $_POST['title_tr'];
    $en = $_POST['title_en'];
    $dist_id = intval($_SESSION['admin_district_id'] ?? 0);

    $pdo->prepare("INSERT INTO settings (name, value, district_id) VALUES ('menu_hospital_tr', ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)")->execute([$tr, $dist_id]);
    $pdo->prepare("INSERT INTO settings (name, value, district_id) VALUES ('menu_hospital_en', ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)")->execute([$en, $dist_id]);

    // Icon Upload
    if (isset($_FILES['category_img']) && $_FILES['category_img']['error'] == 0) {
        $target_dir = "../uploads/categories/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES['category_img']['name'], PATHINFO_EXTENSION);
        $new_filename = 'cat_pharmacy_' . $dist_id . '_' . time() . '.' . $file_ext;
        
        if (move_uploaded_file($_FILES['category_img']['tmp_name'], $target_dir . $new_filename)) {
            $img_path = 'uploads/categories/' . $new_filename;
            $pdo->prepare("INSERT INTO settings (name, value, district_id) VALUES ('menu_pharmacy_img', ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)")->execute([$img_path, $dist_id]);
        }
    }

    header("Location: hospital_pharmacy.php?msg=saved");
    exit;
}

// Nöbet Durumu Güncelleme
if (isset($_GET['toggle_duty'])) {
    $id = $_GET['toggle_duty'];
    $current = $_GET['status'];
    $new = $current == 1 ? 0 : 1;
    $pdo->prepare("UPDATE pharmacies SET is_on_duty = ? WHERE id = ?")->execute([$new, $id]);
    header("Location: hospital_pharmacy.php?msg=updated");
    exit;
}

$dist_id = intval($_SESSION['admin_district_id'] ?? 0);
$stmt_s = $pdo->prepare("SELECT value FROM settings WHERE name = 'menu_hospital_tr' AND (district_id = ? OR district_id = 0) ORDER BY district_id DESC LIMIT 1");
$stmt_s->execute([$dist_id]);
$val_tr = $stmt_s->fetchColumn() ?: 'Hastane & Eczane';

$stmt_en = $pdo->prepare("SELECT value FROM settings WHERE name = 'menu_hospital_en' AND (district_id = ? OR district_id = 0) ORDER BY district_id DESC LIMIT 1");
$stmt_en->execute([$dist_id]);
$val_en = $stmt_en->fetchColumn() ?: 'Hospital & Pharmacy';

$stmt_img = $pdo->prepare("SELECT value FROM settings WHERE name = 'menu_pharmacy_img' AND (district_id = ? OR district_id = 0) ORDER BY district_id DESC LIMIT 1");
$stmt_img->execute([$dist_id]);
$current_img = $stmt_img->fetchColumn() ?: '../assets/img/categories/medical.jpg';
if (strpos($current_img, 'uploads/') === 0) $current_img = '../' . $current_img;

// Silme İşlemleri
if (isset($_GET['delete_hospital'])) {
    $id = $_GET['delete_hospital'];
    $admin_restricted_district = intval($_SESSION['admin_district_id'] ?? 0);
    if ($admin_restricted_district > 0) {
        $check = $pdo->prepare("SELECT district_id FROM hospitals WHERE id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() != $admin_restricted_district) die("Yetkisiz silme.");
    }
    $pdo->prepare("DELETE FROM hospitals WHERE id = ?")->execute([$id]);
    header("Location: hospital_pharmacy.php");
    exit;
}
if (isset($_GET['delete_pharmacy'])) {
    $id = $_GET['delete_pharmacy'];
    $admin_restricted_district = intval($_SESSION['admin_district_id'] ?? 0);
    if ($admin_restricted_district > 0) {
        $check = $pdo->prepare("SELECT district_id FROM pharmacies WHERE id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() != $admin_restricted_district) die("Yetkisiz silme.");
    }
    $pdo->prepare("DELETE FROM pharmacies WHERE id = ?")->execute([$id]);
    header("Location: hospital_pharmacy.php");
    exit;
}

$admin_restricted_district = intval($_SESSION['admin_district_id'] ?? 0);
if ($admin_restricted_district > 0) {
    $hospitals = $pdo->prepare("SELECT h.*, d.name as district_name FROM hospitals h LEFT JOIN districts d ON h.district_id = d.id WHERE h.district_id = ? ORDER BY h.name ASC");
    $hospitals->execute([$admin_restricted_district]);
    $hospitals = $hospitals->fetchAll();
    
    $pharmacies = $pdo->prepare("SELECT p.*, d.name as district_name FROM pharmacies p LEFT JOIN districts d ON p.district_id = d.id WHERE p.district_id = ? ORDER BY p.name ASC");
    $pharmacies->execute([$admin_restricted_district]);
    $pharmacies = $pharmacies->fetchAll();
} else {
    $hospitals = $pdo->query("SELECT h.*, d.name as district_name FROM hospitals h LEFT JOIN districts d ON h.district_id = d.id ORDER BY h.name ASC")->fetchAll();
    $pharmacies = $pdo->query("SELECT p.*, d.name as district_name FROM pharmacies p LEFT JOIN districts d ON p.district_id = d.id ORDER BY p.name ASC")->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hastane & Eczane Yönetimi - Çermik</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <header>
            <h1>Hastane & Eczane Yönetimi</h1>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="hospital_add.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Yeni Hastane</a>
            </div>
        </header>

        <main class="page-content">
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'saved'): ?>
                <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    Başlıklar başarıyla kaydedildi.
                </div>
            <?php endif; ?>

            <div class="card" style="margin-bottom: 1rem;">
                <form action="" method="POST" enctype="multipart/form-data" style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <label style="display: block; font-size: 0.8rem; margin-bottom: 5px;">Kategori Adı (TR)</label>
                        <input type="text" name="title_tr" value="<?php echo htmlspecialchars($val_tr); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 8px;">
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <label style="display: block; font-size: 0.8rem; margin-bottom: 5px;">Kategori Adı (EN)</label>
                        <input type="text" name="title_en" value="<?php echo htmlspecialchars($val_en); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 8px;">
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <label style="display: block; font-size: 0.8rem; margin-bottom: 5px;">Kategori Görseli (Eczane/Hastane)</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <img src="<?php echo $current_img; ?>" style="width: 40px; height: 40px; border-radius: 4px; object-fit: cover; border: 1px solid #ddd;">
                            <input type="file" name="category_img" accept="image/*" style="font-size: 0.8rem;">
                        </div>
                    </div>
                    <div>
                        <button type="submit" name="save_titles" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2>Hastaneler</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Adı</th>
                            <th>Konum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hospitals as $h): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($h['name']); ?></td>
                            <td><?php echo $h['lat'] . ", " . $h['lng']; ?></td>
                            <td>
                                <a href="hospital_edit.php?id=<?php echo $h['id']; ?>" class="btn">Düzenle</a>
                                <a href="?delete_hospital=<?php echo $h['id']; ?>" class="btn" style="color:red;" onclick="return confirm('Emin misiniz?')">Sil</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card" style="margin-top: 30px;">
                <h2>Eczaneler</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Adı</th>
                            <th>Telefon</th>
                            <th>Nöbet Durumu</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pharmacies as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                            <td><?php echo htmlspecialchars($p['phone']); ?></td>
                            <td>
                                <a href="?toggle_duty=<?php echo $p['id']; ?>&status=<?php echo $p['is_on_duty']; ?>" 
                                   class="badge <?php echo $p['is_on_duty'] ? 'badge-success' : 'badge-pending'; ?>" style="text-decoration:none;">
                                    <?php echo $p['is_on_duty'] ? 'Nöbetçi' : 'Normal'; ?>
                                </a>
                            </td>
                            <td>
                                <a href="pharmacy_edit.php?id=<?php echo $p['id']; ?>" class="btn">Düzenle</a>
                                <a href="?delete_pharmacy=<?php echo $p['id']; ?>" class="btn" style="color:red;" onclick="return confirm('Emin misiniz?')">Sil</a>
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
