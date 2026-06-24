<?php
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

$msg = '';
$admin_district_id = intval($_SESSION['admin_district_id'] ?? 0);
$is_super = ($_SESSION['admin_role'] === 'SUPER_ADMIN');

// ─── Duyuru Ekleme ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_announcement'])) {
    if (empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $msg = "Yüklenen dosya çok büyük! Sunucu limiti (post_max_size) aşıldı.";
        $msg_type = 'error';
    } else {
        csrf_verify();
    $image = null;
    if (isset($_FILES['ann_image']) && $_FILES['ann_image']['error'] === UPLOAD_ERR_OK) {
        $upload_err = '';
        if (validate_uploaded_image($_FILES['ann_image'], $upload_err)) {
            $dir = '../uploads/announcements/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $filename = safe_upload_filename($_FILES['ann_image']);
            if (move_uploaded_file($_FILES['ann_image']['tmp_name'], $dir . $filename)) {
                $image = 'uploads/announcements/' . $filename;
            }
        } else {
            $msg = "Görsel hatası: $upload_err";
        }
    }
        if (!$msg) {
            $target_district = ($is_super && $admin_district_id == 0) ? intval($_POST['target_district_id'] ?? 0) : $admin_district_id;
            $db_district = ($target_district > 0) ? $target_district : null;

            $pdo->prepare('INSERT INTO announcements (district_id, content, content_en, image) VALUES (?, ?, ?, ?)')
                ->execute([$db_district, trim($_POST['content']), trim($_POST['content_en'] ?? ''), $image]);
            header('Location: announcements.php?msg=ann_added');
            exit;
        }
    }
}

// ─── Etkinlik Ekleme ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    csrf_verify();
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_err = '';
        if (validate_uploaded_image($_FILES['image'], $upload_err)) {
            $dir = '../uploads/events/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $filename = safe_upload_filename($_FILES['image']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dir . $filename)) {
                $image = 'uploads/events/' . $filename;
            }
        }
    }
    $target_district = ($is_super && $admin_district_id == 0) ? intval($_POST['target_district_id'] ?? 0) : $admin_district_id;
    
    if ($target_district > 0) {
        $db_district = $target_district;
        $is_global = 1;
        $global_status = 'PENDING';
    } else {
        $db_district = null;
        $is_global = 1;
        $global_status = 'APPROVED';
    }

    $pdo->prepare('INSERT INTO events (district_id, title, title_en, description, description_en, event_date, image, is_global, global_status, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([
            $db_district,
            trim($_POST['title'] ?? ''),
            trim($_POST['title_en'] ?? ''),
            trim($_POST['description'] ?? ''),
            trim($_POST['description_en'] ?? ''),
            $_POST['event_date'] ?? '',
            $image,
            $is_global,
            $global_status,
            'APPROVED'
        ]);
    header('Location: announcements.php?msg=event_added');
    exit;
}

// ─── Duyuru Silme (POST + CSRF) ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ann_id'])) {
    csrf_verify();
    $id = safe_id($_POST['delete_ann_id']);
    if ($id > 0) $pdo->prepare('DELETE FROM announcements WHERE id = ?')->execute([$id]);
    header('Location: announcements.php?msg=deleted');
    exit;
}

// ─── Etkinlik Silme (POST + CSRF) ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event_id'])) {
    csrf_verify();
    $id = safe_id($_POST['delete_event_id']);
    if ($id > 0) $pdo->prepare('DELETE FROM events WHERE id = ?')->execute([$id]);
    header('Location: announcements.php?msg=deleted');
    exit;
}

// ─── Veri Çek ─────────────────────────────────────────────────────────────────
if (!$msg && isset($_GET['msg'])) {
    $msg_map = [
        'ann_added'   => ['success', 'Duyuru başarıyla yayınlandı.'],
        'event_added' => ['success', 'Etkinlik başarıyla eklendi.'],
        'deleted'     => ['info',    'Kayıt silindi.'],
    ];
    if (isset($msg_map[$_GET['msg']])) {
        [$msg_type, $msg] = $msg_map[$_GET['msg']];
    }
}

$announcements = [];
$events        = [];
$all_districts = [];

try {
    // Tüm ilçeleri çek (Süper Admin'in seçebilmesi için)
    $all_districts = $pdo->query('SELECT id, name FROM districts ORDER BY name ASC')->fetchAll();

    if ($is_super && $admin_district_id == 0) {
        $announcements = $pdo->query('SELECT a.*, d.name as district_name FROM announcements a LEFT JOIN districts d ON a.district_id = d.id ORDER BY a.created_at DESC')->fetchAll();
        $events        = $pdo->query('SELECT e.*, d.name as district_name FROM events e LEFT JOIN districts d ON e.district_id = d.id ORDER BY e.event_date DESC')->fetchAll();
    } else {
        $st = $pdo->prepare('SELECT a.*, d.name as district_name FROM announcements a LEFT JOIN districts d ON a.district_id = d.id WHERE a.district_id = ? ORDER BY a.created_at DESC');
        $st->execute([$admin_district_id]);
        $announcements = $st->fetchAll();
        
        $st2 = $pdo->prepare('SELECT e.*, d.name as district_name FROM events e LEFT JOIN districts d ON e.district_id = d.id WHERE e.district_id = ? ORDER BY e.event_date DESC');
        $st2->execute([$admin_district_id]);
        $events = $st2->fetchAll();
    }
} catch (\PDOException $ex) {
    $msg = 'Veritabanı hatası: ' . $ex->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duyurular & Etkinlikler - Çermik Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1><i class="fa-solid fa-bullhorn"></i> Duyuru & Etkinlik Yönetimi</h1>
                <p style="color: var(--text-muted);">Güncel haberler ve etkinlikleri yönetin.</p>
            </div>
            <div class="header-right">
                <a href="mail_send.php" class="btn btn-primary">
                    <i class="fa-solid fa-paper-plane"></i> Üyelere Mail Gönder
                </a>
            </div>
        </header>

        <main class="page-content">

            <?php if ($msg): ?>
            <div style="background: <?php echo (isset($msg_type) && $msg_type === 'success') ? '#d1fae5; border-color:#6ee7b7; color:#065f46' : '#dbeafe; border-color:#93c5fd; color:#1e40af'; ?>;
                        border:1px solid; padding:12px 18px; border-radius:8px; margin-bottom:16px;">
                <?php echo e((string)$msg); ?>
            </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">

                <!-- Duyurular Bölümü -->
                <div class="card">
                    <h3><i class="fa-solid fa-bullhorn" style="color:#f59e0b;"></i> Duyuru Yayınla</h3>
                    <form action="" method="POST" enctype="multipart/form-data" style="margin-top: 1rem;">
                        <?php echo csrf_field(); ?>
                        <textarea name="content" class="btn" required
                            style="width:100%; border:1px solid #ddd; min-height:80px; padding:10px; margin-bottom:5px; box-sizing:border-box;"
                            placeholder="Duyuru metni (TR)..."></textarea>
                        <textarea name="content_en" class="btn"
                            style="width:100%; border:1px solid #ddd; min-height:60px; padding:10px; margin-bottom:10px; box-sizing:border-box;"
                            placeholder="Announcement text (EN)..."></textarea>
                        
                        <?php if ($is_super && $admin_district_id == 0): ?>
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-size:0.8rem; margin-bottom:5px; color:#666;">Yayınlanacak Yer</label>
                            <select name="target_district_id" class="btn" style="width:100%; border:1px solid #ddd; padding:10px; box-sizing:border-box;">
                                <option value="0">Tümü (Tüm İlçelerde Görünsün)</option>
                                <?php foreach ($all_districts as $dist): ?>
                                    <option value="<?php echo $dist['id']; ?>"><?php echo htmlspecialchars($dist['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-size:0.8rem; margin-bottom:5px; color:#666;">Duyuru Görseli (İsteğe bağlı)</label>
                            <input type="file" name="ann_image" class="btn"
                                   style="width:100%; border:1px solid #ddd; padding:10px; box-sizing:border-box;"
                                   accept="image/jpeg,image/png,image/webp,image/gif">
                        </div>
                        <button type="submit" name="add_announcement" class="btn btn-primary" style="width:100%;">
                            <i class="fa-solid fa-bullhorn"></i> Duyuruyu Paylaş
                        </button>
                    </form>

                    <h3 style="margin-top:2rem;"><i class="fa-solid fa-list"></i> Aktif Duyurular (<?php echo count($announcements); ?>)</h3>
                    <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr><th>Görsel</th><th>Bölge</th><th>İçerik</th><th>Tarih</th><th>İşlem</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($announcements as $a): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($a['image'])): ?>
                                        <img src="../<?php echo e((string)$a['image']); ?>" style="width:50px; height:50px; object-fit:cover; border-radius:5px;">
                                    <?php else: ?>
                                        <div style="width:50px; height:50px; background:#eee; border-radius:5px; display:flex; align-items:center; justify-content:center; color:#ccc;"><i class="fa-solid fa-image"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td><span style="font-size:0.75rem; color:#666;"><?php echo $a['district_name'] ?: 'Tümü'; ?></span></td>
                                <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo e((string)substr($a['content'], 0, 80)); ?>...</td>
                                <td style="white-space:nowrap;"><?php echo date('d.m.Y', strtotime($a['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Duyuru silinsin mi?')">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="delete_ann_id" value="<?php echo (int)$a['id']; ?>">
                                        <button type="submit" style="color:#e74c3c; background:none; border:none; cursor:pointer; font-size:1rem;">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($announcements)): ?>
                            <tr><td colspan="4" style="text-align:center; color:#888; padding:20px;">Henüz duyuru yok.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>

                <!-- Etkinlikler Bölümü -->
                <div class="card">
                    <h3><i class="fa-solid fa-calendar-days" style="color:#8b5cf6;"></i> Etkinlik Ekle</h3>
                    <form action="" method="POST" enctype="multipart/form-data" style="margin-top: 1rem;">
                        <?php echo csrf_field(); ?>
                        <input type="text" name="title" required maxlength="300"
                               placeholder="Etkinlik Başlığı (TR)" class="btn"
                               style="width:100%; border:1px solid #ddd; padding:10px; margin-bottom:5px; box-sizing:border-box;">
                        <input type="text" name="title_en" maxlength="300"
                               placeholder="Event Title (EN)" class="btn"
                               style="width:100%; border:1px solid #ddd; padding:10px; margin-bottom:10px; box-sizing:border-box;">
                        <textarea name="description" placeholder="Açıklama (TR)" class="btn"
                            style="width:100%; border:1px solid #ddd; min-height:60px; padding:10px; margin-bottom:5px; box-sizing:border-box;"></textarea>
                        <textarea name="description_en" placeholder="Description (EN)" class="btn"
                            style="width:100%; border:1px solid #ddd; min-height:60px; padding:10px; margin-bottom:10px; box-sizing:border-box;"></textarea>
                        
                        <?php if ($is_super && $admin_district_id == 0): ?>
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-size:0.8rem; margin-bottom:5px; color:#666;">Yayınlanacak Yer</label>
                            <select name="target_district_id" class="btn" style="width:100%; border:1px solid #ddd; padding:10px; box-sizing:border-box;">
                                <option value="0">Tümü (Tüm İlçelerde Görünsün)</option>
                                <?php foreach ($all_districts as $dist): ?>
                                    <option value="<?php echo $dist['id']; ?>"><?php echo htmlspecialchars($dist['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        <input type="datetime-local" name="event_date" required class="btn"
                               style="width:100%; border:1px solid #ddd; padding:10px; margin-bottom:10px; box-sizing:border-box;">
                        <div style="margin-bottom:15px;">
                            <label style="display:block; font-size:0.8rem; margin-bottom:5px; color:#666;">Etkinlik Görseli</label>
                            <input type="file" name="image" class="btn"
                                   style="width:100%; border:1px solid #ddd; padding:10px; box-sizing:border-box;"
                                   accept="image/jpeg,image/png,image/webp,image/gif">
                        </div>
                        <button type="submit" name="add_event" class="btn btn-primary" style="width:100%;">
                            <i class="fa-solid fa-calendar-plus"></i> Etkinliği Kaydet
                        </button>
                    </form>

                    <h3 style="margin-top:2rem;"><i class="fa-solid fa-list"></i> Etkinlik Listesi (<?php echo count($events); ?>)</h3>
                    <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr><th>Görsel</th><th>Bölge</th><th>Etkinlik</th><th>Tarih</th><th>İşlem</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $ev): ?>
                            <tr>
                                <td>
                                    <?php if ($ev['image']): ?>
                                        <img src="../<?php echo e((string)$ev['image']); ?>" style="width:50px; height:50px; object-fit:cover; border-radius:5px;">
                                    <?php else: ?>
                                        <div style="width:50px; height:50px; background:#eee; border-radius:5px; display:flex; align-items:center; justify-content:center; color:#ccc;"><i class="fa-solid fa-image"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td><span style="font-size:0.75rem; color:#666;"><?php echo $ev['district_name'] ?: 'Tümü'; ?></span></td>
                                <td><strong><?php echo e($ev['title']); ?></strong></td>
                                <td style="white-space:nowrap;"><?php echo date('d.m.Y H:i', strtotime($ev['event_date'])); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Etkinlik silinsin mi?')">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="delete_event_id" value="<?php echo (int)$ev['id']; ?>">
                                        <button type="submit" style="color:#e74c3c; background:none; border:none; cursor:pointer; font-size:1rem;">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($events)): ?>
                            <tr><td colspan="4" style="text-align:center; color:#888; padding:20px;">Henüz etkinlik yok.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

</body>
</html>
