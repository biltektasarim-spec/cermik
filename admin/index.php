<?php
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';


// İstatistikler
$is_super = ($_SESSION['admin_role'] === 'SUPER_ADMIN');
$d_filter = $is_super ? "" : " WHERE district_id = " . intval($_SESSION['admin_district_id']);
$d_filter_where = $is_super ? "" : " AND district_id = " . intval($_SESSION['admin_district_id']);

$count_places = $pdo->query("SELECT COUNT(*) FROM places" . $d_filter)->fetchColumn();
$count_events_pending = $pdo->query("SELECT COUNT(*) FROM events WHERE global_status = 'PENDING'" . ($is_super ? "" : " AND district_id = " . intval($_SESSION['admin_district_id'])))->fetchColumn();
$count_businesses = $pdo->query("SELECT COUNT(*) FROM businesses" . $d_filter)->fetchColumn();
$count_users = $pdo->query("SELECT COUNT(*) FROM users" . ($is_super ? "" : " WHERE district_id = " . intval($_SESSION['admin_district_id']))) ->fetchColumn();
$count_cek_gonder = $pdo->query("SELECT COUNT(*) FROM cek_gonder_forms" . $d_filter)->fetchColumn();

// Ziyaret İstatistikleri (Üye + Anonim)
$st_f = ($is_super ? "" : " WHERE district_id = " . (int)$_SESSION['admin_district_id']);
try {
    $count_visits = $pdo->query("SELECT (SELECT COUNT(*) FROM check_ins $st_f) + (SELECT COUNT(*) FROM passive_stats $st_f) AS total")->fetchColumn();
} catch (Exception $e) {
    $count_visits = 0; 
}

// Son Paylaşımlar
$recent_submissions_query = "SELECT s.*, u.email FROM submissions s JOIN users u ON s.user_id = u.id";
if (!$is_super_admin) {
    $recent_submissions_query .= " WHERE u.district_id = " . intval($_SESSION['admin_district_id']);
}
$recent_submissions_query .= " ORDER BY s.created_at DESC LIMIT 5";
$recent_submissions = $pdo->query($recent_submissions_query)->fetchAll();

// Fix filtering for dashboard using shared $admin_filter from auth_guard.php
$dash_cek_filter = str_replace('district_id', 'c.district_id', $admin_filter);
$recent_cek_gonder = $pdo->query("SELECT c.*, d.name AS district_name FROM cek_gonder_forms c LEFT JOIN districts d ON c.district_id = d.id WHERE " . $dash_cek_filter . " ORDER BY c.created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Çermik Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>Dashboard</h1>
                <p style="color: var(--text-muted);">Hoş geldiniz <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>, yönetim panelini buradan yönetebilirsiniz.</p>
            </div>
            <div class="header-right">
                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                    <span style="color: #e74c3c; margin-right: 15px; font-weight: 600;"><i class="fa-solid fa-check"></i> Silindi</span>
                <?php endif; ?>
                <a href="../index.php" target="_blank" class="btn" style="border: 1px solid #ddd;">Siteye Git</a>
            </div>
        </header>

        <main class="page-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon icon-blue">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Çek Gönder</h3>
                        <div class="value"><?php echo $count_cek_gonder; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-blue">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Mekanlar</h3>
                        <div class="value"><?php echo $count_places; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-orange">
                        <i class="fa-solid fa-shop"></i>
                    </div>
                    <div class="stat-info">
                        <h3>İşletmeler</h3>
                        <div class="value"><?php echo $count_businesses; ?></div>
                    </div>
                </div>
                <div class="stat-card" onclick="window.location.href='<?php echo $is_super ? 'events_global.php' : 'events.php'; ?>'" style="cursor:pointer;">
                    <div class="stat-icon icon-purple">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Bekleyen Etkinlik</h3>
                        <div class="value"><?php echo $count_events_pending; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-green">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Kullanıcılar</h3>
                        <div class="value"><?php echo $count_users; ?></div>
                    </div>
                </div>
                <div class="stat-card" onclick="window.location.href='checkins.php'" style="cursor:pointer; border-bottom: 3px solid #3498db;">
                    <div class="stat-icon" style="color:#3498db; background:rgba(52, 152, 219, 0.1);">
                        <i class="fa-solid fa-eye"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Ziyaretler (Top.)</h3>
                        <div class="value"><?php echo number_format($count_visits); ?></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2>Son Paylaşımlar</h2>
                    <a href="submissions.php" class="btn btn-primary">Tümünü Gör</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Başlık</th>
                            <th>Kullanıcı</th>
                            <th>Tarih</th>
                            <th>Durum</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_submissions as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['title']); ?></td>
                            <td><?php echo htmlspecialchars($s['email']); ?></td>
                            <td><?php echo date('d.m.Y H:i', strtotime($s['created_at'])); ?></td>
                            <td>
                                <span class="badge <?php echo $s['is_approved'] ? 'badge-success' : 'badge-pending'; ?>">
                                    <?php echo $s['is_approved'] ? 'Onaylı' : 'Bekliyor'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="submissions.php?id=<?php echo $s['id']; ?>" class="btn" style="padding: 0.4rem 0.8rem; border: 1px solid #ddd;">Detay</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card" style="margin-top: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2>Son Çek Gönder İletileri</h2>
                    <a href="cek_gonder_listesi.php" class="btn btn-primary">Tümünü Gör</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <?php if ($is_super): ?>
                            <th>İlçe</th>
                            <?php endif; ?>
                            <th>Tür</th>
                            <th>Ad Soyad</th>
                            <th>Tarih</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_cek_gonder as $cg): ?>
                        <tr>
                            <?php if ($is_super): ?>
                            <td><span style="font-weight:600; color:#555;"><?php echo htmlspecialchars($cg['district_name'] ?? 'Bilinmiyor'); ?></span></td>
                            <?php endif; ?>
                            <td>
                                <?php
                                $turColors = [
                                    'Bilgilendirme' => ['#3498db','#ebf5fb'],
                                    'İstek'         => ['#9b59b6','#f5eef8'],
                                    'Öneri'         => ['#f39c12','#fef9e7'],
                                    'Şikayet'       => ['#e74c3c','#fdedec'],
                                    'Teşekkür'      => ['#e91e63','#fce4ec'],
                                ];
                                [$color, $bg] = $turColors[$cg['basvuru_turu']] ?? ['#aaa','#f0f0f0'];
                                ?>
                                <span class="badge" style="background:<?php echo $bg; ?>; color:<?php echo $color; ?>; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem;">
                                    <?php echo htmlspecialchars($cg['basvuru_turu']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($cg['ad_soyad']); ?></td>
                            <td><?php echo date('d.m.Y H:i', strtotime($cg['created_at'])); ?></td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="cek_gonder_listesi.php" class="btn" style="padding: 0.4rem 0.8rem; border: 1px solid #ddd; font-size: 0.8rem;">İncele</a>
                                    <?php if ($is_super_admin): ?>
                                    <a href="cek_gonder_listesi.php?delete=<?php echo $cg['id']; ?>&dashboard=1" class="btn" onclick="return confirm('Silmek istediğinize emin misiniz?')" style="padding: 0.4rem 0.8rem; border: 1px solid #fecaca; color: #dc2626; background: #fff1f2; font-size: 0.8rem;"><i class="fa-solid fa-trash"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recent_cek_gonder)): ?>
                        <tr>
                            <td colspan="<?php echo $is_super ? '5' : '4'; ?>" style="text-align:center; padding: 2rem; color:#aaa;">
                                Henüz Çek Gönder iletisi yok.
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
