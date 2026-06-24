<?php
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/../includes/image_utils.php';

// Sadece Super Admin girebilir
if ($_SESSION['admin_role'] !== 'SUPER_ADMIN') {
    header('Location: index.php');
    exit;
}

// Global Bazda Onay İşlemi
if (isset($_GET['approve_global'])) {
    $id = intval($_GET['approve_global']);
    $pdo->prepare("UPDATE events SET global_status = 'APPROVED', is_global = 1 WHERE id = ?")->execute([$id]);
    header('Location: events_global.php?msg=approved');
    exit;
}

// Reddetme İşlemi
if (isset($_GET['reject_global'])) {
    $id = intval($_GET['reject_global']);
    $pdo->prepare("UPDATE events SET global_status = 'REJECTED' WHERE id = ?")->execute([$id]);
    header('Location: events_global.php?msg=rejected');
    exit;
}

// Silme İşlemi
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM events WHERE id = ?")->execute([$id]);
    header('Location: events_global.php?msg=deleted');
    exit;
}

// S.Admin Direkt Global Etkinlik Ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $date = $_POST['event_date'];
    
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/events/';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0755, true);
        
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $new_name = md5(time() . rand()) . '.' . $ext;
            $temp_path = $_FILES['image']['tmp_name'];
            $target_path = $upload_dir . $new_name;
            
            // Resize image to max 1200x800 while maintaining ratio
            if (resizeImage($temp_path, $target_path, 1200, 800)) {
                $image_path = 'uploads/events/' . $new_name;
            } else if (move_uploaded_file($temp_path, $target_path)) {
                $image_path = 'uploads/events/' . $new_name;
            }
        }
    }
    
    $pdo->prepare("INSERT INTO events (title, description, event_date, is_global, status, global_status, image) VALUES (?, ?, ?, 1, 'APPROVED', 'APPROVED', ?)")
        ->execute([$title, $desc, $date, $image_path]);
        
    header('Location: events_global.php?msg=added');
    exit;
}

// Onay Bekleyen Global Adayları (İlçelerden Gelenler)
$pending_globals = $pdo->query("SELECT e.*, d.name as district_name FROM events e LEFT JOIN districts d ON e.district_id = d.id WHERE e.is_global = 1 AND e.global_status = 'PENDING' ORDER BY e.event_date DESC")->fetchAll();

// Onaylı Global Etkinlikler
$approved_globals = $pdo->query("SELECT e.*, d.name as district_name FROM events e LEFT JOIN districts d ON e.district_id = d.id WHERE e.is_global = 1 AND e.global_status = 'APPROVED' ORDER BY e.event_date DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Etkinlik Yönetimi - S.Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>Global Etkinlik Yönetimi</h1>
                <p style="color: var(--text-muted);">Ana sayfada (Sol Menü) görünecek etkinlikleri yönetin.</p>
            </div>
        </header>

        <main class="page-content">
            <?php if (isset($_GET['msg'])): ?>
                <div class="card" style="background: var(--primary); color: #000; padding: 10px; margin-bottom: 20px; border-radius: 8px;">
                    <?php 
                        if($_GET['msg'] == 'approved') echo 'Etkinlik ana sayfada yayınlanmaya başladı.';
                        if($_GET['msg'] == 'rejected') echo 'Etkinlik reddedildi.';
                        if($_GET['msg'] == 'deleted') echo 'Etkinlik sistemden silindi.';
                        if($_GET['msg'] == 'added') echo 'Yeni global etkinlik eklendi.';
                    ?>
                </div>
            <?php endif; ?>

            <!-- Onay Bekleyenler -->
            <?php if (!empty($pending_globals)): ?>
            <div class="card" style="margin-bottom: 2rem; border: 2px solid var(--primary);">
                <h3 style="color: var(--primary); margin-bottom: 15px;"><i class="fa-solid fa-clock"></i> Onay Bekleyen Global Talepler (İlçelerden)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>İlçe</th>
                            <th>Tarih</th>
                            <th>Başlık</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_globals as $e): ?>
                        <tr>
                            <td><span class="badge"><?php echo htmlspecialchars($e['district_name'] ?: 'Genel'); ?></span></td>
                            <td><?php echo date('d.m.Y', strtotime($e['event_date'])); ?></td>
                            <td><strong><?php echo htmlspecialchars($e['title']); ?></strong></td>
                            <td>
                                <a href="?approve_global=<?php echo $e['id']; ?>" class="btn" style="background: #27ae60; color: white;">Ana Sayfaya Al</a>
                                <a href="?reject_global=<?php echo $e['id']; ?>" class="btn" style="background: #e67e22; color: white;">Reddet</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <div class="card" style="margin-bottom: 2rem;">
                <h3>Hızlı Global Etkinlik Ekle (S.Admin)</h3>
                <form method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                        <input type="text" name="title" placeholder="Etkinlik Başlığı" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px;" required>
                        <input type="date" name="event_date" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px;" required>
                        <input type="file" name="image" accept="image/*" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px;">
                    </div>
                    <textarea name="description" placeholder="Etkinlik Açıklaması" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-top: 15px; height: 80px;" required></textarea>
                    <button type="submit" name="add_event" class="btn btn-primary" style="margin-top: 15px;">Global Etkinlik Olarak Paylaş</button>
                </form>
            </div>

            <div class="card">
                <h2>Yayındaki Global Etkinlikler</h2>
                <table>
                    <thead>
                        <tr>
                            <th>İlçe</th>
                            <th>Tarih</th>
                            <th>Başlık</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($approved_globals as $e): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($e['district_name'] ?: 'Genel'); ?></td>
                            <td><?php echo date('d.m.Y', strtotime($e['event_date'])); ?></td>
                            <td><strong><?php echo htmlspecialchars($e['title']); ?></strong></td>
                            <td><span class="badge badge-success">Yayında</span></td>
                            <td>
                                <a href="?delete=<?php echo $e['id']; ?>" class="btn" style="color: #e74c3c;" onclick="return confirm('Emin misiniz?')">Kaldır / Sil</a>
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
