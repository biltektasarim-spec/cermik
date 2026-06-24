<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Onay/Sil İşlemleri (POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (isset($_POST['approve_id'])) {
        $id = safe_id($_POST['approve_id']);
        if ($id > 0) {
            $pdo->prepare('UPDATE submissions SET is_approved = 1 WHERE id = ?')->execute([$id]);
        }
        header('Location: submissions.php?msg=approved');
        exit;
    }

    if (isset($_POST['delete_id'])) {
        $id = safe_id($_POST['delete_id']);
        if ($id > 0) {
            $pdo->prepare('DELETE FROM submissions WHERE id = ?')->execute([$id]);
        }
        header('Location: submissions.php?msg=deleted');
        exit;
    }
}

$submissions = $pdo->query("SELECT s.*, u.email FROM submissions s JOIN users u ON s.user_id = u.id WHERE (" . str_replace('district_id', 's.district_id', $admin_filter) . ") ORDER BY s.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paylaşım Yönetimi - Çermik Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>Paylaşım Yönetimi</h1>
                <p style="color: var(--text-muted);">Kullanıcılardan gelen fotoğrafları ve yazıları yönetin.</p>
            </div>
        </header>

        <main class="page-content">
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Görsel</th>
                            <th>Bilgi</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $s): ?>
                        <tr>
                            <td>
                                <?php if($s['image_path']): ?>
                                    <div style="width: 80px; height: 60px; border: 2px solid #ddd; border-radius: 8px; overflow: hidden; cursor: pointer;" onclick="openImageModal('../<?php echo htmlspecialchars($s['image_path']); ?>')">
                                        <img src="../<?php echo htmlspecialchars($s['image_path']); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                <?php else: ?>
                                    <div style="width: 80px; height: 60px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #999;">Foto Yok</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($s['title']); ?></strong><br>
                                <small><?php echo htmlspecialchars($s['email']); ?> - <?php echo date('d.m.Y', strtotime($s['created_at'])); ?></small>
                                <p style="font-size: 0.85rem; margin-top: 5px; color: #555;"><?php echo htmlspecialchars(substr($s['content'], 0, 100)); ?>...</p>
                            </td>
                            <td>
                                <span class="badge <?php echo $s['is_approved'] ? 'badge-success' : 'badge-pending'; ?>">
                                    <?php echo $s['is_approved'] ? 'Onaylı' : 'Onay Bekliyor'; ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <?php if (!$s['is_approved']): ?>
                                        <form method="POST" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="approve_id" value="<?php echo (int)$s['id']; ?>">
                                            <button type="submit" class="btn" style="padding: 0.4rem 0.8rem; border: 1px solid #ddd; color: #2ecc71;" title="Onayla">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Silmek istediğinize emin misiniz?')">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="delete_id" value="<?php echo (int)$s['id']; ?>">
                                        <button type="submit" class="btn" style="padding: 0.4rem 0.8rem; border: 1px solid #ddd; color: #e74c3c;">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Resim Büyütme Modali -->
    <div id="imageModal" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); flex-direction:column; align-items:center; justify-content:center;">
        <span onclick="document.getElementById('imageModal').style.display='none'" style="position:absolute; top:20px; right:30px; font-size:40px; color:white; cursor:pointer;">&times;</span>
        <img id="largeImg" src="" style="max-width:90%; max-height:80vh; border: 5px solid white; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.5); object-fit:contain;">
        <a id="downloadBtn" href="" download class="btn" style="margin-top:20px; background:#2ecc71; color:white; padding:10px 20px; border-radius:8px; text-decoration:none; font-weight:bold;"><i class="fa-solid fa-download"></i> Resmi İndir</a>
    </div>

    <script>
    function openImageModal(src) {
        document.getElementById('largeImg').src = src;
        document.getElementById('downloadBtn').href = src;
        document.getElementById('imageModal').style.display = 'flex';
    }
    </script>
</body>
</html>
