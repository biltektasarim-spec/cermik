<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

$district_id = intval($_SESSION['admin_district_id'] ?? 0);
$is_super = ($_SESSION['admin_role'] === 'SUPER_ADMIN');

// Handle Deletion
if (isset($_POST['delete_id'])) {
    $del = intval($_POST['delete_id']);
    try {
        if ($district_id > 0) {
            $pdo->prepare("DELETE FROM live_broadcasts WHERE id = ? AND district_id = ?")->execute([$del, $district_id]);
        } else {
            $pdo->prepare("DELETE FROM live_broadcasts WHERE id = ?")->execute([$del]);
        }
        $msg = "Yayın başarıyla silindi.";
    } catch (Exception $e) {
        $err = "Silinirken hata oluştu: " . $e->getMessage();
    }
}

// Fetch Broadcasts
if ($district_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM live_broadcasts WHERE district_id = ? ORDER BY sort_order ASC, id DESC");
    $stmt->execute([$district_id]);
} else {
    // SuperAdmin Views All Generic (0/NULL) and Specific Districts if Mode=GENERAL
    $stmt = $pdo->query("SELECT lb.*, d.name as district_name FROM live_broadcasts lb LEFT JOIN districts d ON lb.district_id = d.id ORDER BY lb.district_id ASC, lb.sort_order ASC, lb.id DESC");
}
$broadcasts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Canlı Yayın Yönetimi - Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="header-actions" style="display:flex; justify-content:space-between; align-items:center;">
                <h1>Canlı Yayın Yönetimi</h1>
                <a href="live_broadcast_edit.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Yeni Video / Yayın Ekle</a>
            </div>

            <?php if (!empty($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>
            <?php if (!empty($err)) echo "<div class='alert alert-danger'>$err</div>"; ?>

            <div class="card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Resim</th>
                            <th>Başlık</th>
                            <?php if($is_super): ?><th>İlçe</th><?php endif; ?>
                            <th>Platform</th>
                            <th>Sıra</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($broadcasts)): ?>
                            <tr><td colspan="<?php echo $is_super ? '7' : '6'; ?>" style="text-align:center;">Henüz hiç kayıt eklenmemiş.</td></tr>
                        <?php else: ?>
                            <?php foreach($broadcasts as $item): ?>
                            <tr>
                                <td>
                                    <?php if($item['image']): ?>
                                        <img src="../<?php echo htmlspecialchars($item['image']); ?>" style="width:50px; height:50px; object-fit:cover; border-radius:5px;">
                                    <?php else: ?>
                                        <div style="width:50px; height:50px; background:#ddd; display:flex; align-items:center; justify-content:center; border-radius:5px;">Belgesiz</div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['title']); ?></td>
                                <?php if($is_super): ?>
                                    <td><?php echo htmlspecialchars($item['district_name'] ?? 'Genel Sistem / Tümü'); ?></td>
                                <?php endif; ?>
                                <td>
                                    <?php if(!empty($item['youtube_url'])): ?>
                                        <i class="fa-brands fa-youtube" style="color:red; font-size:1.2rem;" title="YouTube"></i>
                                    <?php endif; ?>
                                    <?php if(!empty($item['facebook_url'])): ?>
                                        <i class="fa-brands fa-facebook" style="color:#1877F2; font-size:1.2rem; margin-left:5px;" title="Facebook"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo intval($item['sort_order']); ?></td>
                                <td>
                                    <?php if($item['is_active']): ?>
                                        <span class="badge" style="background:#2ecc71;">Açık (Aktif)</span>
                                    <?php else: ?>
                                        <span class="badge" style="background:#e74c3c;">Kapalı</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display:flex; gap:5px;">
                                        <a href="live_broadcast_edit.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary" title="Düzenle"><i class="fa-solid fa-pen"></i></a>
                                        <form method="POST" onsubmit="return confirm('Bu canlı yayın bağlantısını silmek istediğinize emin misiniz?');" style="margin:0;">
                                            <input type="hidden" name="delete_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="btn btn-sm" style="background:#e74c3c; color:white;" title="Sil"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
