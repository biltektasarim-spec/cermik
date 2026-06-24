<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Delete Operation
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM municipal_guide WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: municipal_guide.php?msg=deleted");
    exit;
}

// Add/Update Operation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? null;
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
    $title = $_POST['title'];
    $title_en = $_POST['title_en'];
    $description = $_POST['description'];
    $description_en = $_POST['description_en'];
    $sort_order = $_POST['sort_order'] ?? 0;
    
    // District ID Logic: Use POSTed value if Super Admin, otherwise session value
    $target_district_id = ($is_super_admin && isset($_POST['district_id'])) ? (int)$_POST['district_id'] : $admin_district_id;
    if ($target_district_id == 0) $target_district_id = null;
    
    $image = $_POST['old_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = '../uploads/guide/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename);
        $image = 'uploads/guide/' . $filename;
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE municipal_guide SET parent_id = ?, district_id = ?, title = ?, title_en = ?, description = ?, description_en = ?, image = ?, sort_order = ? WHERE id = ?");
        $stmt->execute([$parent_id, $target_district_id, $title, $title_en, $description, $description_en, $image, $sort_order, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO municipal_guide (parent_id, district_id, title, title_en, description, description_en, image, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$parent_id, $target_district_id, $title, $title_en, $description, $description_en, $image, $sort_order]);
    }
    header("Location: municipal_guide.php?msg=success");
    exit;
}

// Filter by district_id
// Super Admin in GENEL mode (district_id=0) sees ALL entries.
// District Admin sees their own district + items with NULL or 0 (Global).
if ($is_super_admin && $admin_district_id == 0) {
    if (isset($_GET['view_district']) && is_numeric($_GET['view_district'])) {
        $view_id = (int)$_GET['view_district'];
        $all_items_stmt = $pdo->prepare("SELECT * FROM municipal_guide WHERE district_id = ? OR district_id IS NULL OR district_id = 0 ORDER BY parent_id ASC, sort_order ASC");
        $all_items_stmt->execute([$view_id]);
    } else {
        $all_items_stmt = $pdo->query("SELECT * FROM municipal_guide ORDER BY parent_id ASC, sort_order ASC");
    }
} else {
    $all_items_stmt = $pdo->prepare("SELECT * FROM municipal_guide WHERE district_id = ? OR district_id IS NULL OR district_id = 0 ORDER BY parent_id ASC, sort_order ASC");
    $all_items_stmt->execute([$admin_district_id]);
}
$all_items = $all_items_stmt->fetchAll();

// Group items for selection and display
$parents = [];
$children = [];
foreach ($all_items as $item) {
    if (empty($item['parent_id']) || $item['parent_id'] == 0) {
        $parents[$item['id']] = $item;
    } else {
        $children[$item['parent_id']][] = $item;
    }
}

// Check for orphans (items whose parents are missing) and move them to parents to ensure they are visible
foreach ($children as $parentId => $orphanedChildren) {
    if (!isset($parents[$parentId])) {
        foreach ($orphanedChildren as $orphan) {
            $parents[$orphan['id']] = $orphan;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Belediye Rehberi Yönetimi</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .guide-grid { display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem; }
        .guide-item { background: white; border-radius: 12px; padding: 15px; border: 1px solid #ddd; }
        .sub-menu-list { margin-left: 30px; margin-top: 10px; border-left: 2px dashed #ddd; padding-left: 20px; display: flex; flex-direction: column; gap: 10px; }
        .form-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; }
        .form-card { background: white; padding: 30px; border-radius: 15px; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .item-info { display: flex; align-items: center; gap: 15px; }
        .item-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>Belediye Rehberi</h1>
                <p>İlçenize özel kurumsal rehber sayfalarını yönetin.</p>
            </div>
            <div class="header-right">
                <button class="btn btn-primary" onclick="openForm()"><i class="fa-solid fa-plus"></i> Yeni Menü Ekle</button>
            </div>
        </header>

        <main class="page-content">
            <?php if(isset($_GET['msg'])): ?>
                <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">İşlem başarıyla tamamlandı.</div>
            <?php endif; ?>

            <div class="guide-grid">
                <?php foreach($parents as $p): ?>
                <div class="guide-item">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="item-info">
                            <img src="../<?php echo $p['image'] ?: 'assets/img/project_default.jpg'; ?>" class="item-img">
                            <div>
                                <h3 style="margin: 0; font-size: 1.1rem;"><?php echo htmlspecialchars($p['title']); ?></h3>
                                <small style="color: #666;">Ana Menü</small>
                            </div>
                        </div>
                        <div style="display: flex; gap: 5px;">
                            <button class="btn" style="border: 1px solid #ddd;" onclick='editItem(<?php echo json_encode($p); ?>)'><i class="fa-solid fa-pen"></i></button>
                            <a href="municipal_guide.php?delete=<?php echo $p['id']; ?>" class="btn" style="color: #e74c3c; border: 1px solid #ddd;" onclick="return confirm('Silmek istediğinize emin misiniz?')"><i class="fa-solid fa-trash"></i></a>
                        </div>
                    </div>
                    
                    <?php if(isset($children[$p['id']])): ?>
                    <div class="sub-menu-list">
                        <?php foreach($children[$p['id']] as $c): ?>
                        <div class="guide-item" style="border-style: dotted;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div class="item-info">
                                    <img src="../<?php echo $c['image'] ?: 'assets/img/project_default.jpg'; ?>" class="item-img" style="width: 40px; height: 40px;">
                                    <div>
                                        <h4 style="margin: 0; font-size: 1rem; font-weight: 500;"><?php echo htmlspecialchars($c['title']); ?></h4>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 5px;">
                                    <button class="btn" style="padding: 5px 10px; border: 1px solid #ddd;" onclick='editItem(<?php echo json_encode($c); ?>)'><i class="fa-solid fa-pen"></i></button>
                                    <a href="municipal_guide.php?delete=<?php echo $c['id']; ?>" class="btn" style="padding: 5px 10px; color: #e74c3c; border: 1px solid #ddd;" onclick="return confirm('Silmek istediğinize emin misiniz?')"><i class="fa-solid fa-trash"></i></a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php if(empty($parents)): ?>
                    <p style="text-align: center; padding: 40px; color: #666;">Henüz ilçe rehberi için bir sayfa eklenmemiş.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Form Modal -->
    <div id="guideForm" class="form-overlay">
        <div class="form-card">
            <h2 id="formTitle">Yeni Menü Ekle</h2>
            <form method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" id="item_id">
                <input type="hidden" name="old_image" id="item_old_image">
                <div class="form-group">
                    <label>Başlık (TR)</label>
                    <input type="text" name="title" id="item_title" required>
                </div>
                <div class="form-group">
                    <label>Başlık (EN)</label>
                    <input type="text" name="title_en" id="item_title_en">
                </div>
                <div class="form-group">
                    <label>Üst Menü (Opsiyonel)</label>
                    <select name="parent_id" id="item_parent_id">
                        <option value="">-- Ana Menü Olarak Ekle --</option>
                        <?php foreach($parents as $p): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($is_super_admin && $admin_district_id == 0): ?>
                    <div class="form-group">
                        <label>İlçe</label>
                        <select name="district_id" id="item_district_id">
                            <option value="0">Tüm İlçeler (Global)</option>
                            <?php 
                            $districts_list = $pdo->query("SELECT id, name FROM districts WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
                            foreach($districts_list as $dist): ?>
                                <option value="<?php echo $dist['id']; ?>"><?php echo htmlspecialchars($dist['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label>Açıklama (TR)</label>
                    <textarea name="description" id="item_description" style="height: 100px;"></textarea>
                </div>
                <div class="form-group">
                    <label>Açıklama (EN)</label>
                    <textarea name="description_en" id="item_description_en" style="height: 100px;"></textarea>
                </div>
                <div class="form-group">
                    <label>Görsel</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Sıralama</label>
                    <input type="number" name="sort_order" id="item_sort_order" value="0">
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="flex: 2;">Kaydet</button>
                    <button type="button" class="btn" style="flex: 1; border: 1px solid #ddd;" onclick="closeForm()">İptal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openForm() {
            document.getElementById('formTitle').innerText = 'Yeni Menü Ekle';
            document.getElementById('item_id').value = '';
            document.getElementById('item_title').value = '';
            document.getElementById('item_title_en').value = '';
            document.getElementById('item_description').value = '';
            document.getElementById('item_description_en').value = '';
            document.getElementById('item_parent_id').value = '';
            document.getElementById('item_sort_order').value = '0';
            document.getElementById('item_old_image').value = '';
            document.getElementById('guideForm').style.display = 'flex';
        }
        function closeForm() {
            document.getElementById('guideForm').style.display = 'none';
        }
        function editItem(i) {
            document.getElementById('formTitle').innerText = 'Menüyü Düzenle';
            document.getElementById('item_id').value = i.id;
            document.getElementById('item_title').value = i.title;
            if (document.getElementById('item_district_id')) {
                document.getElementById('item_district_id').value = i.district_id || 0;
            }
            document.getElementById('item_title_en').value = i.title_en || '';
            document.getElementById('item_description').value = i.description;
            document.getElementById('item_description_en').value = i.description_en || '';
            document.getElementById('item_parent_id').value = i.parent_id || '';
            document.getElementById('item_sort_order').value = i.sort_order;
            document.getElementById('item_old_image').value = i.image || '';
            document.getElementById('guideForm').style.display = 'flex';
        }
    </script>
</body>
</html>
