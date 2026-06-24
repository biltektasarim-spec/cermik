<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

$users = $pdo->query("SELECT u.*, d.name as district_name FROM users u LEFT JOIN districts d ON u.district_id = d.id ORDER BY u.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Yönetimi - Çermik Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>Kullanıcı Yönetimi</h1>
                <p style="color: var(--text-muted);"><?php echo count($users); ?> kayıtlı vatandaş.</p>
            </div>
            <div class="header-right">
                <button onclick="exportSmsFormat()" class="btn btn-primary"><i class="fa-solid fa-file-export"></i> Toplu SMS Export</button>
            </div>
        </header>

        <main class="page-content">
            <!-- Toplu SMS Dışa Aktar Görünümü -->
            <div id="sms-export-view" class="card" style="display:none; margin-bottom: 20px; border: 2px solid #3182ce;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h3><i class="fa-solid fa-table-list"></i> Toplu SMS Formatı (Telefon | Mesaj)</h3>
                    <button onclick="document.getElementById('sms-export-view').style.display='none'" class="btn" style="background:#eee;">Kapat</button>
                </div>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="table" id="sms-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f7fafc;">
                                <th style="border: 1px solid #dee2e6; padding: 10px; text-align: left;">Telefon</th>
                                <th style="border: 1px solid #dee2e6; padding: 10px; text-align: left;">Mesaj</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): 
                                $phone = preg_replace('/[^0-9]/', '', $u['phone'] ?? '');
                                $first_name = $u['first_name'] ?? '';
                                $last_name = $u['last_name'] ?? '';
                                $msg = "Merhaba " . htmlspecialchars($first_name . ' ' . $last_name);
                                if (empty($phone)) continue;
                            ?>
                            <tr>
                                <td style="border: 1px solid #dee2e6; padding: 10px;"><?php echo $phone; ?></td>
                                <td style="border: 1px solid #dee2e6; padding: 10px;"><?php echo $msg; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p style="margin-top: 10px; font-size: 0.85rem; color: #666;">Bu tabloyu kopyalayıp Excel'e veya toplu SMS paneline yapıştırabilirsiniz.</p>
            </div>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Telefon</th>
                            <th>Kayıt Tarihi</th>
                            <th>Durum</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($u['email']); ?></strong></td>
                            <td><?php echo htmlspecialchars($u['phone']); ?></td>
                            <td><?php echo date('d.m.Y', strtotime($u['created_at'])); ?></td>
                            <td>
                                <span class="badge <?php echo $u['is_active'] ? 'badge-success' : 'badge-pending'; ?>">
                                    <?php echo $u['is_active'] ? 'Aktif' : 'Pasif'; ?>
                                </span>
                            </td>
                             <td>
                                <a href="user_detail.php?id=<?php echo $u['id']; ?>" class="btn" style="padding: 0.4rem 0.8rem; border: 1px solid #3182ce; color: #3182ce;" title="Üye Hareketleri ve Detay"><i class="fa-solid fa-eye"></i> Detay</a>
                                <form action="user_delete.php" method="POST" style="display:inline;" onsubmit="return confirm('Bu kullanıcıyı ve tüm hareketlerini silmek istediğinize emin misiniz?');">
                                    <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                    <button type="submit" class="btn" style="padding: 0.4rem 0.8rem; border: 1px solid #e53e3e; color: #e53e3e;" title="Kullanıcıyı Sil"><i class="fa-solid fa-trash"></i></button>
                                </form>
                             </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
    function exportSmsFormat() {
        var view = document.getElementById('sms-export-view');
        if (view.style.display === 'none') {
            view.style.display = 'block';
            view.scrollIntoView({ behavior: 'smooth' });
        } else {
            view.style.display = 'none';
        }
    }
    </script>
</body>
</html>
