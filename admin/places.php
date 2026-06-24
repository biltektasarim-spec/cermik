<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Silme işlemi
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM places WHERE id = ? AND ($admin_filter)");
    $stmt->execute([$id]);
    header("Location: places.php?msg=deleted");
    exit;
}

// Mekanları çek
$places = $pdo->query("SELECT p.*, d.name as district_name FROM places p LEFT JOIN districts d ON p.district_id = d.id WHERE $admin_filter ORDER BY p.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mekan Yönetimi - Çermik Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>Mekan Yönetimi</h1>
                <p style="color: var(--text-muted);"><?php echo count($places); ?> toplam mekan kayıtlı.</p>
            </div>
            <div class="header-right">
                <a href="place_add.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Yeni Mekan Ekle</a>
            </div>
        </header>

        <main class="page-content">
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div style="background: #ffebee; color: #c62828; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    Mekan başarıyla silindi.
                </div>
            <?php endif; ?>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Görsel</th>
                            <th>Mekan Adı</th>
                            <th>Kategori</th>
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
                            <td><?php echo $p['category']; ?></td>
                            <td><i class="fa-solid fa-star" style="color: #f1c40f;"></i> <?php echo $p['popular_score']; ?></td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="place_edit.php?id=<?php echo $p['id']; ?>" class="btn" style="padding: 0.4rem 0.8rem; border: 1px solid #ddd; color: var(--accent-color);">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="places.php?delete=<?php echo $p['id']; ?>" class="btn" onclick="return confirm('Silmek istediğinize emin misiniz?')" style="padding: 0.4rem 0.8rem; border: 1px solid #ddd; color: #e74c3c;">
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
