<?php
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Sadece Super Admin girebilir
if ($_SESSION['admin_role'] !== 'SUPER_ADMIN') {
    header('Location: index.php');
    exit;
}

// İlçe Değiştirme (Simülasyon/View)
if (isset($_GET['switch_to'])) {
    $target_id = intval($_GET['switch_to']);
    // Eğer 0 ise Genel S.Admin moduna dön
    if ($target_id === 0) {
        $_SESSION['admin_district_id'] = 0;
        $_SESSION['admin_view_mode'] = 'GLOBAL';
    } else {
        $_SESSION['admin_district_id'] = $target_id;
        $_SESSION['admin_view_mode'] = 'DISTRICT';
    }
    header('Location: index.php');
    exit;
}

// İlçeleri Listele
$districts = $pdo->query("SELECT * FROM districts ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlçe Yönetimi - S.Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>Tüm İlçeler</h1>
                <p style="color: var(--text-muted);">Sistemde kayıtlı ilçeleri yönetin veya verilerine göz atın.</p>
            </div>
        </header>

        <main class="page-content">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2>İlçe Listesi</h2>
                    <a href="district_add.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Yeni İlçe Ekle</a>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>İlçe Adı</th>
                            <th>Slug</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>GENEL / TÜMÜ</strong></td>
                            <td><code>global</code></td>
                            <td><span class="badge badge-success">Aktif</span></td>
                            <td>
                                <a href="?switch_to=0" class="btn" style="background: #3498db; color: white;">Paneli Gör</a>
                            </td>
                        </tr>
                        <?php foreach ($districts as $d): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($d['name']); ?></td>
                            <td><code><?php echo htmlspecialchars($d['slug']); ?></code></td>
                            <td>
                                <span class="badge <?php echo $d['is_active'] ? 'badge-success' : 'badge-pending'; ?>">
                                    <?php echo $d['is_active'] ? 'Aktif' : 'Pasif'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="district_admins.php?district_id=<?php echo $d['id']; ?>" class="btn" style="background: #e67e22; color: white;"><i class="fa-solid fa-users-gear"></i> Yöneticiler</a>
                                <a href="?switch_to=<?php echo $d['id']; ?>" class="btn" style="background: #27ae60; color: white;">Paneli Gör</a>
                                <a href="district_edit.php?id=<?php echo $d['id']; ?>" class="btn"><i class="fa-solid fa-edit"></i></a>
                                <?php if ($d['id'] != 3 && $d['id'] != 5): // Çermik ve Çüngüş silinmesin ?>
                                <a href="district_delete.php?id=<?php echo $d['id']; ?>" class="btn" style="background: #e74c3c; color: white;" onclick="return confirm('Bu ilçeyi ve tüm verilerini tamamen SİLMEK istediğinizden emin misiniz? Bu işlem geri alınamaz!');"><i class="fa-solid fa-trash"></i></a>
                                <?php endif; ?>
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
