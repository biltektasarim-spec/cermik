<?php
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

if (!$is_super_admin) {
    header("Location: index.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$menu = null;

if ($id > 0) {
    $sql_check = $is_super_admin ? "1=1" : "district_id = $admin_district_id";
    $stmt = $pdo->prepare("SELECT * FROM custom_menus WHERE id = ? AND ($sql_check)");
    $stmt->execute([$id]);
    $menu = $stmt->fetch();
    if (!$menu) die("Menü bulunamadı.");
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $district_id = $is_super_admin ? intval($_POST['district_id']) : $admin_district_id;
    $name_tr = $_POST['name_tr'] ?? ($menu['name_tr'] ?? '');
    $name_en = $_POST['name_en'] ?? ($menu['name_en'] ?? '');
    $slug = $_POST['slug'] ?? ($menu['slug'] ?? '');
    if (!$slug && $name_tr) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name_tr)));
    }
    
    $menu_type = 'single'; // Forced to single as per final requirements
    $target_url = $_POST['target_url'] ?? ($menu['target_url'] ?? '');
    
    // Auto-Target for Single Menus will be handled by the place_id logic on frontend
    // but we can set a descriptive placeholder or the slug
    if (!$target_url && $slug) {
        $target_url = $slug;
    }

    $icon = $_POST['icon'] ?? 'fa-link';
    $sort_order = intval($_POST['sort_order']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $image = $menu['image'] ?? null;
    $place_id = $menu['place_id'] ?? null;

    // Auto-create Place for Single Menus (Super Admin Only)
    if ($menu_type == 'single' && !$place_id && $is_super_admin) {
        $stmt_p = $pdo->prepare("INSERT INTO places (district_id, name, name_en, category) VALUES (?, ?, ?, 'Historical')");
        $stmt_p->execute([$district_id, $name_tr, $name_en]);
        $place_id = $pdo->lastInsertId();
    }

    // Image Upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $target_dir = "../uploads/menus/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $new_filename = 'menu_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_dir . $new_filename)) {
            $image = 'uploads/menus/' . $new_filename;
        }
    }

    if ($id > 0) {
        $sql = "UPDATE custom_menus SET district_id = ?, name_tr = ?, name_en = ?, slug = ?, image = ?, icon = ?, menu_type = ?, target_url = ?, sort_order = ?, is_active = ?, place_id = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$district_id, $name_tr, $name_en, $slug, $image, $icon, $menu_type, $target_url, $sort_order, $is_active, $place_id, $id]);
        
        // Sync to places table if place_id exists
        if ($place_id && $image) {
            $stmt = $pdo->prepare("UPDATE places SET image_main = ? WHERE id = ?");
            $stmt->execute([$image, $place_id]);
        }
        
        $msg = 'updated';
    } else {
        $sql = "INSERT INTO custom_menus (district_id, name_tr, name_en, slug, image, icon, menu_type, target_url, sort_order, is_active, place_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$district_id, $name_tr, $name_en, $slug, $image, $icon, $menu_type, $target_url, $sort_order, $is_active, $place_id]);
        
        // Sync to places table if place_id exists
        if ($place_id && $image) {
            $stmt = $pdo->prepare("UPDATE places SET image_main = ? WHERE id = ?");
            $stmt->execute([$image, $place_id]);
        }
        
        $msg = 'added';
    }

    header("Location: district_menus.php?msg=" . $msg);
    exit;
}

$districts = $pdo->query("SELECT id, name FROM districts ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $id ? 'Menü Düzenle' : 'Yeni Menü Ekle'; ?> - Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <h1><?php echo $id ? 'Menü Düzenle' : 'Yeni Menü Ekle'; ?></h1>
            <a href="district_menus.php" class="btn">Vazgeç</a>
        </header>

        <main class="page-content" style="max-width: 800px;">
            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($is_super_admin): ?>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">İlçe Seçimi</label>
                        <select name="district_id" required style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;">
                            <?php foreach ($districts as $d): ?>
                                <option value="<?php echo $d['id']; ?>" <?php echo (($menu['district_id'] ?? $admin_district_id) == $d['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Menü Adı (TR)</label>
                            <input type="text" name="name_tr" value="<?php echo htmlspecialchars($menu['name_tr'] ?? ''); ?>" required <?php echo !$is_super_admin ? 'disabled' : ''; ?> style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd; background: <?php echo !$is_super_admin ? '#f5f5f5' : '#fff'; ?>;">
                        </div>
                        <div class="form-group">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Menü Adı (EN)</label>
                            <input type="text" name="name_en" value="<?php echo htmlspecialchars($menu['name_en'] ?? ''); ?>" <?php echo !$is_super_admin ? 'disabled' : ''; ?> style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd; background: <?php echo !$is_super_admin ? '#f5f5f5' : '#fff'; ?>;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Menü Görseli</label>
                        <?php if ($menu['image'] ?? false): ?>
                            <img src="../<?php echo $menu['image']; ?>" style="width: 100px; height: 100px; object-fit: cover; margin-bottom: 10px; border-radius: 8px;">
                        <?php endif; ?>
                        <input type="file" name="image_file" accept="image/*" style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;">
                        <p style="font-size: 0.8rem; color: #888; margin-top: 5px;">Kare (1:1) veya yatay (16:9) PNG/JPG önerilir.</p>
                    </div>

                    <input type="hidden" name="menu_type" value="single">

                    <?php if ($is_super_admin): ?>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Hedef Link / Sayfa (Slug veya URL)</label>
                        <input type="text" name="target_url" value="<?php echo htmlspecialchars($menu['target_url'] ?? ''); ?>" placeholder="Sistem tarafından otomatik oluşturulur" style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Sıralama</label>
                            <input type="number" name="sort_order" value="<?php echo intval($menu['sort_order'] ?? 0); ?>" style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd;">
                        </div>
                        <div class="form-group">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Simge (FontAwesome)</label>
                            <input type="text" name="icon" value="<?php echo htmlspecialchars($menu['icon'] ?? 'fa-link'); ?>" <?php echo !$is_super_admin ? 'disabled' : ''; ?> placeholder="fa-landmark" style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd; background: <?php echo !$is_super_admin ? '#f5f5f5' : '#fff'; ?>;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="is_active" <?php echo ($menu['is_active'] ?? 1) ? 'checked' : ''; ?>>
                            <span style="font-weight: 600;">Menü Aktif</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">Menüyü Kaydet</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
