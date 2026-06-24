<?php
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/../includes/image_utils.php';

$is_super = ($_SESSION['admin_role'] === 'SUPER_ADMIN');
$district_id = $_SESSION['admin_district_id'];

// Silme İşlemi
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // District admin ise sadece kendi ilçesinin etkinliğini silebilir
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ? " . ($is_super ? "" : "AND district_id = ?"));
    $is_super ? $stmt->execute([$id]) : $stmt->execute([$id, $district_id]);
    header('Location: events.php?msg=deleted');
    exit;
}

// Yeni Etkinlik Ekleme
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

    $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, district_id, status, is_global, global_status, image) VALUES (?, ?, ?, ?, 'APPROVED', 1, 'PENDING', ?)");
    $stmt->execute([$title, $desc, $date, $district_id, $image_path]);
        
    header('Location: events.php?msg=added');
    exit;
}

// Etkinlikleri Listele
$query = "SELECT * FROM events WHERE district_id = ? ORDER BY event_date DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$district_id]);
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etkinlik Yönetimi - İlçe Paneli</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>Etkinlik Yönetimi</h1>
                <p style="color: var(--text-muted);">İlçenizde paylaşılan etkinlikleri yönetin. Eklediğiniz etkinlikler ilçenizin sayfasında yayınlanırken, **Global Onay** sonrası ana sayfada da görüntülenecektir.</p>
            </div>
        </header>

        <main class="page-content">
            <?php if (isset($_GET['msg'])): ?>
                <div class="card" style="background: var(--primary); color: #000; padding: 10px; margin-bottom: 20px; border-radius: 8px;">
                    <?php 
                        if($_GET['msg'] == 'added') echo 'Etkinlik ilçe sayfasında yayınlanmaya başladı. Global onay için üst panele gönderildi.';
                        if($_GET['msg'] == 'deleted') echo 'Etkinlik sistemden silindi.';
                    ?>
                </div>
            <?php endif; ?>

            <div class="card" style="margin-bottom: 2rem;">
                <h3>Yeni İlçe Etkinliği Ekle</h3>
                <form method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                        <input type="text" name="title" placeholder="Etkinlik Başlığı" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px;" required>
                        <input type="date" name="event_date" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px;" required>
                        <input type="file" name="image" accept="image/*" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px;">
                    </div>
                    <textarea name="description" placeholder="Etkinlik Açıklaması" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-top: 15px; height: 80px;" required></textarea>
                    <button type="submit" name="add_event" class="btn btn-primary" style="margin-top: 15px;">Etkinlik Yayınla</button>
                </form>
            </div>

            <div class="card">
                <h2>İlçe Etkinlik Listesi</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Başlık</th>
                            <th>Durum (İlçe)</th>
                            <th>Global Onay</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $e): ?>
                        <tr>
                            <td><?php echo date('d.m.Y', strtotime($e['event_date'])); ?></td>
                            <td><strong><?php echo htmlspecialchars($e['title']); ?></strong></td>
                            <td><span class="badge badge-success">Yayında</span></td>
                            <td>
                                <?php if ($e['global_status'] == 'APPROVED'): ?>
                                    <span class="badge badge-success"><i class="fa-solid fa-check"></i> Onaylandı (Ana Sayfada)</span>
                                <?php elseif ($e['global_status'] == 'PENDING'): ?>
                                    <span class="badge badge-pending"><i class="fa-solid fa-clock"></i> Onay Bekliyor</span>
                                <?php else: ?>
                                    <span class="badge badge-danger"><i class="fa-solid fa-xmark"></i> Reddedildi</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?delete=<?php echo $e['id']; ?>" class="btn" style="color: #e74c3c;" onclick="return confirm('Emin misiniz?')">Sil</a>
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
