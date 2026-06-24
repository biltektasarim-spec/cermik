<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Settings Save
if (isset($_POST['save_titles'])) {
    $tr = $_POST['title_tr'];
    $en = $_POST['title_en'];
    $pdo->prepare("INSERT INTO settings (name, value, district_id) VALUES ('menu_historical_tr', ?, ?) ON DUPLICATE KEY UPDATE value = ?")->execute([$tr, $admin_district_id, $tr]);
    $pdo->prepare("INSERT INTO settings (name, value, district_id) VALUES ('menu_historical_en', ?, ?) ON DUPLICATE KEY UPDATE value = ?")->execute([$en, $admin_district_id, $en]);
    
    // Image Upload
    if (isset($_FILES['cat_image']) && $_FILES['cat_image']['error'] == 0) {
        $target_dir = "../uploads/categories/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_ext = pathinfo($_FILES['cat_image']['name'], PATHINFO_EXTENSION);
        $new_filename = 'historical_' . $admin_district_id . '_' . time() . '.' . $file_ext;
        if (move_uploaded_file($_FILES['cat_image']['tmp_name'], $target_dir . $new_filename)) {
            $img_path = 'uploads/categories/' . $new_filename;
            $pdo->prepare("INSERT INTO settings (name, value, district_id) VALUES ('menu_historical_img', ?, ?) ON DUPLICATE KEY UPDATE value = ?")->execute([$img_path, $admin_district_id, $img_path]);
        }
    }
    header("Location: places_historical.php?msg=saved");
    exit;
}

// Silme işlemi
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM places WHERE id = ? AND $admin_filter");
    $stmt->execute([$id]);
    header("Location: places_historical.php?msg=deleted");
    exit;
}

$stmt_s = $pdo->prepare("SELECT value FROM settings WHERE name = 'menu_historical_tr' AND (district_id = ? OR district_id = 0) ORDER BY district_id DESC LIMIT 1");
$stmt_s->execute([$admin_district_id]);
$val_tr = $stmt_s->fetchColumn() ?: 'Tarihi Mekanlar';

$stmt_en = $pdo->prepare("SELECT value FROM settings WHERE name = 'menu_historical_en' AND (district_id = ? OR district_id = 0) ORDER BY district_id DESC LIMIT 1");
$stmt_en->execute([$admin_district_id]);
$val_en = $stmt_en->fetchColumn() ?: 'Historical Places';

$stmt_img = $pdo->prepare("SELECT value FROM settings WHERE name = 'menu_historical_img' AND (district_id = ? OR district_id = 0) ORDER BY district_id DESC LIMIT 1");
$stmt_img->execute([$admin_district_id]);
$cat_img = $stmt_img->fetchColumn() ?: 'assets/img/categories/historical.jpg';

// Sadece Tarihi Mekanları çek (Multi-tenancy)
$places = $pdo->query("SELECT * FROM places WHERE category = 'Historical' AND $admin_filter ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarihi Mekanlar - Çermik Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header style="display: flex; align-items: center; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 15px; flex: 1;">
                <img src="../<?php echo $cat_img; ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 12px; border: 2px solid var(--accent-color);">
                <div class="header-left">
                    <h1>Tarihi Mekan Yönetimi</h1>
                    <p style="color: var(--text-muted);"><?php echo count($places); ?> tarihi mekan kayıtlı.</p>
                </div>
            </div>
            <div class="header-right">
                <a href="place_add.php?cat=Historical" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Yeni Tarihi Mekan Ekle</a>
            </div>
        </header>

        <main class="page-content">
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div style="background: #ffebee; color: #c62828; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    Mekan başarıyla silindi.
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'saved'): ?>
                <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    Başlıklar başarıyla kaydedildi.
                </div>
            <?php endif; ?>

            <div class="card" style="margin-bottom: 1rem;">
                <form action="" method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 15px; align-items: flex-end;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; margin-bottom: 5px;">Kategori Adı (TR)</label>
                        <input type="text" name="title_tr" value="<?php echo htmlspecialchars($val_tr); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 8px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; margin-bottom: 5px;">Kategori Adı (EN)</label>
                        <input type="text" name="title_en" value="<?php echo htmlspecialchars($val_en); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 8px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; margin-bottom: 5px;">Kategori Görseli</label>
                        <input type="file" name="cat_image" accept="image/*" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 5px;">
                    </div>
                    <div>
                        <button type="submit" name="save_titles" class="btn btn-primary" style="height: 38px;">Kaydet</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Görsel</th>
                            <th>Mekan Adı</th>
                            <th>Popülerlik</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($places as $p): ?>
                        <tr>
                            <td>
                                <img src="../<?php echo $p['image_main']; ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                            </td>
                            <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                            <td><i class="fa-solid fa-star" style="color: #f1c40f;"></i> <?php echo $p['popular_score']; ?></td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="place_edit.php?id=<?php echo $p['id']; ?>" class="btn" style="padding: 0.4rem 0.8rem; border: 1px solid #ddd; color: var(--accent-color);">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="places_historical.php?delete=<?php echo $p['id']; ?>" class="btn" onclick="return confirm('Silmek istediğinize emin misiniz?')" style="padding: 0.4rem 0.8rem; border: 1px solid #ddd; color: #e74c3c;">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
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
