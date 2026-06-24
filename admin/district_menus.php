<?php
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

if (!$is_super_admin) {
    header("Location: index.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Verify ownership or super admin
    $sql_check = $is_super_admin ? "1=1" : "district_id = $admin_district_id";
    $stmt = $pdo->prepare("DELETE FROM custom_menus WHERE id = ? AND ($sql_check)");
    $stmt->execute([$id]);
    header("Location: district_menus.php?msg=deleted");
    exit;
}

// Fetch Menus
$query = "SELECT m.*, d.name as district_name 
          FROM custom_menus m 
          LEFT JOIN districts d ON m.district_id = d.id 
          WHERE $admin_filter 
          ORDER BY m.district_id, m.sort_order ASC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$menus = $stmt->fetchAll();

// Fetch Districts for Super Admin filter/view
$districts = [];
if ($is_super_admin) {
    $districts = $pdo->query("SELECT id, name FROM districts ORDER BY name ASC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlçe Özel Menü Yönetimi - Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .menu-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .type-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .type-single { background: #e3f2fd; color: #1976d2; }
        .type-multi { background: #f3e5f5; color: #7b1fa2; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>İlçe Özel Menüleri</h1>
                <p style="color: var(--text-muted);">İlçelerin ana sayfasındaki özel kategori ve menüleri yönetin.</p>
            </div>
            <div class="header-right">
                <a href="district_menu_edit.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Yeni Menü Ekle</a>
            </div>
        </header>

        <main class="page-content">
            <?php if (isset($_GET['msg'])): ?>
                <div class="card" style="margin-bottom: 1rem; background: #e8f5e9; color: #2e7d32; padding: 10px;">
                    <?php 
                    if ($_GET['msg'] == 'deleted') echo "Menü başarıyla silindi.";
                    if ($_GET['msg'] == 'added') echo "Yeni menü eklendi.";
                    if ($_GET['msg'] == 'updated') echo "Menü güncellendi.";
                    ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <?php if ($is_super_admin && $admin_district_id == 0): ?>
                            <th>İlçe</th>
                            <?php endif; ?>
                            <th>Görsel</th>
                            <th>Menü Adı</th>
                            <th>Tür</th>
                            <th>Sıra</th>
                            <th>Durum</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menus as $m): ?>
                        <tr>
                            <?php if ($is_super_admin && $admin_district_id == 0): ?>
                            <td><strong><?php echo htmlspecialchars($m['district_name'] ?? 'Genel'); ?></strong></td>
                            <?php endif; ?>
                            <td>
                                <?php if ($m['image']): ?>
                                    <img src="../<?php echo $m['image']; ?>" class="menu-img">
                                <?php else: ?>
                                    <div class="menu-img" style="display:flex; align-items:center; justify-content:center; background:#f0f0f0;">
                                        <i class="fa-solid <?php echo $m['icon'] ?: 'fa-star'; ?>"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($m['name_tr']); ?></strong>
                                <div style="font-size: 0.8rem; color: #888;"><?php echo htmlspecialchars($m['name_en']); ?></div>
                            </td>
                            <td>
                                <span class="type-badge <?php echo $m['menu_type'] == 'single' ? 'type-single' : 'type-multi'; ?>">
                                    <?php echo $m['menu_type'] == 'single' ? 'Tekli' : 'Çoklu'; ?>
                                </span>
                            </td>
                            <td><?php echo $m['sort_order']; ?></td>
                            <td>
                                <span class="badge <?php echo $m['is_active'] ? 'badge-success' : 'badge-pending'; ?>">
                                    <?php echo $m['is_active'] ? 'Aktif' : 'Pasif'; ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    <a href="district_menu_edit.php?id=<?php echo $m['id']; ?>" class="btn btn-sm" title="Düzenle"><i class="fa-solid fa-pen"></i></a>
                                    
                                    <?php if ($m['menu_type'] == 'single' && $m['place_id']): ?>
                                        <a href="place_edit.php?id=<?php echo $m['place_id']; ?>" class="btn btn-sm" style="background: var(--secondary); color: white;" title="İçerik Yönetimi">
                                            <i class="fa-solid fa-file-lines"></i>
                                        </a>
                                    <?php endif; ?>

                                    <a href="district_menus.php?delete=<?php echo $m['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu menüyü silmek istediğinize emin misiniz?')" title="Sil"><i class="fa-solid fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($menus)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding: 2rem; color:#aaa;">Henüz özel menü eklenmemiş.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
