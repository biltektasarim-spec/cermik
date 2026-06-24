<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Silme işlemi
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM places WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: places_hotspring.php?msg=deleted");
    exit;
}

// Sadece Kaplıcaları çek
$places = $pdo->query("SELECT p.*, d.name as district_name FROM places p LEFT JOIN districts d ON p.district_id=d.id WHERE p.category='HotSpring' AND ($admin_filter) ORDER BY p.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kaplıcalar - Çermik Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>Kaplıca Yönetimi</h1>
                <p style="color: var(--text-muted);">Kaplıca bilgilerini, şifalı hastalıkları ve 360Â° görünümü güncelleyin.</p>
            </div>
        </header>

        <main class="page-content">
            <div class="card">
                <p style="margin-bottom: 20px;">Aşağıdaki butona tıklayarak kaplıca detaylarını, hastalık bilgilerini ve panorama ayarlarını düzenleyebilirsiniz.</p>
                <?php if ($places): ?>
                    <?php foreach ($places as $p): ?>
                        <div style="padding: 20px; border: 1px solid #eee; border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h2 style="margin: 0;"><?php echo htmlspecialchars($p['name']); ?></h2>
                                <p style="color: #666; font-size: 0.9rem;">Son Güncelleme: <?php echo $p['created_at']; ?></p>
                            </div>
                            <a href="place_edit.php?id=<?php echo $p['id']; ?>" class="btn btn-primary" style="padding: 10px 25px;">Bilgileri Düzenle</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px;">
                        <p>Henüz kayıtlı kaplıca bulunamadı.</p>
                        <a href="place_add.php?cat=HotSpring" class="btn btn-primary">İlk Kaplıca Kaydını Oluştur</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>
