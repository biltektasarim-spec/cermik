<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Silme işlemi (Placeholder - Detaylı kod istenmedi)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // $stmt = $pdo->prepare("DELETE FROM genel_bilgiler WHERE id = ?");
    // $stmt->execute([$id]);
    header("Location: genel_bilgiler.php?msg=deleted_placeholder");
    exit;
}

// Bilgileri çek
$infos = $pdo->query("SELECT id, baslik, tarih FROM genel_bilgiler ORDER BY tarih DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genel Bilgiler - Çermik Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>Genel Bilgiler</h1>
                <p style="color: var(--text-muted);"><?php echo count($infos); ?> toplam bilgi kaydı mevcut.</p>
            </div>
            <div class="header-right">
                <a href="genel_bilgi_ekle.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Yeni Bilgi Ekle</a>
            </div>
        </header>

        <main class="page-content">
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted_placeholder'): ?>
                <div style="background: #e3f2fd; color: #1565c0; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #bbdefb;">
                    Silme fonksiyonu yer tutucu olarak çalıştı (JS/PHP işlemi burada olacak).
                </div>
            <?php endif; ?>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Başlık</th>
                            <th>Eklenme Tarihi</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($infos) > 0): ?>
                            <?php foreach ($infos as $i): ?>
                            <tr>
                                <td>#<?php echo $i['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($i['baslik']); ?></strong></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($i['tarih'])); ?></td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <a href="#" class="btn" style="padding: 0.4rem 0.8rem; border: 1px solid #ddd; color: var(--accent-color);" title="Düzenle">
                                            <i class="fa-solid fa-pen-to-square"></i> Düzenle
                                        </a>
                                        <a href="genel_bilgiler.php?delete=<?php echo $i['id']; ?>" class="btn" onclick="return confirm('Silmek istediğinize emin misiniz?')" style="padding: 0.4rem 0.8rem; border: 1px solid #ddd; color: #e74c3c;" title="Sil">
                                            <i class="fa-solid fa-trash"></i> Sil
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                    Henüz kayıtlı bilgi bulunamadı.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>
</html>
