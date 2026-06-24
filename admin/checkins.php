<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/../business/GamificationService.php';

use Rehber\Business\GamificationService;

$gamification = new GamificationService($pdo);

// Sil İşlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (isset($_POST['delete_id'])) {
        $id = safe_id($_POST['delete_id']);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM check_ins WHERE id = ?")->execute([$id]);
        }
        header('Location: checkins.php?msg=deleted');
        exit;
    }
}

// Filtreler
$district_id = $_SESSION['admin_district_id'] ?? 0;
$is_super = ($_SESSION['admin_role'] === 'SUPER_ADMIN');

$target_filter = $_GET['target'] ?? ''; // Format: "type_id" örn: "place_15"
$selected_target_type = '';
$selected_target_id = 0;

if (!empty($target_filter)) {
    [$selected_target_type, $selected_target_id] = explode('_', $target_filter);
}

$where_clause = "";
$params = [];

if (!$is_super || $district_id > 0) {
    $where_clause = "WHERE c.district_id = ?";
    $params = [$district_id];
} else {
    $where_clause = "WHERE 1=1";
}

if ($selected_target_id > 0) {
    $where_clause .= " AND c.target_id = ? AND c.target_type = ?";
    $params[] = $selected_target_id;
    $params[] = $selected_target_type;
}

// İstatistikler (Sadece yetkili olunan bölge ve seçili mekan için)
$stats_where = ($district_id > 0) ? "WHERE district_id = ?" : "WHERE 1=1";
$stats_params = ($district_id > 0) ? [$district_id] : [];

if ($selected_target_id > 0) {
    $stats_where .= " AND target_id = ? AND target_type = ?";
    $stats_params[] = $selected_target_id;
    $stats_params[] = $selected_target_type;
}

// Birleşik İstatistik Fonksiyonu (Check-ins + Passive Stats)
function getCombinedCount($pdo, $stats_where, $stats_params, $date_condition = "") {
    try {
        $q = "SELECT 
                (SELECT COUNT(*) FROM check_ins $stats_where $date_condition) +
                (SELECT COUNT(*) FROM passive_stats $stats_where $date_condition) 
              AS total";
        $stmt = $pdo->prepare($q);
        $stmt->execute(array_merge($stats_params, $stats_params));
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        // Eğer tablo henüz yoksa sadece check_ins sayısını döndür
        $q = "SELECT COUNT(*) FROM check_ins $stats_where $date_condition";
        $stmt = $pdo->prepare($q);
        $stmt->execute($stats_params);
        return $stmt->fetchColumn();
    }
}

$monthly_count = getCombinedCount($pdo, $stats_where, $stats_params, "AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
$yearly_count = getCombinedCount($pdo, $stats_where, $stats_params, "AND YEAR(created_at) = YEAR(NOW())");
$total_count = getCombinedCount($pdo, $stats_where, $stats_params);

// Mekan Listesi (Filtre için)
if ($is_super) {
    $places_q = "SELECT p.id, CONCAT(p.name, ' (', d.name, ')') as name, 'place' as type 
                 FROM places p 
                 JOIN districts d ON p.district_id = d.id 
                 ORDER BY p.name ASC";
    $businesses_q = "SELECT b.id, CONCAT(b.business_name, ' (', d.name, ')') as name, 'business' as type 
                     FROM businesses b 
                     JOIN districts d ON b.district_id = d.id 
                     ORDER BY b.business_name ASC";
} else {
    $places_q = "SELECT id, name, 'place' as type FROM places WHERE district_id = " . (int)$district_id . " ORDER BY name ASC";
    $businesses_q = "SELECT id, business_name as name, 'business' as type FROM businesses WHERE district_id = " . (int)$district_id . " ORDER BY name ASC";
}

$filter_options_places = $pdo->query($places_q)->fetchAll();
$filter_options_businesses = $pdo->query($businesses_q)->fetchAll();

// Liste Sorgusu
$query = "
    SELECT c.*, u.first_name, u.last_name, u.email, d.name as district_name,
           COALESCE(p.name, b.business_name) as target_name 
    FROM check_ins c 
    JOIN users u ON c.user_id = u.id 
    LEFT JOIN places p ON (c.target_id = p.id AND c.target_type = 'place')
    LEFT JOIN businesses b ON (c.target_id = b.id AND c.target_type = 'business')
    JOIN districts d ON c.district_id = d.id 
    $where_clause
    ORDER BY c.created_at DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$checkins = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mekan Ziyaret Yönetimi - ROTAREHBER</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border-left: 5px solid var(--primary-color, #3498db);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .stat-card i {
            font-size: 2rem;
            color: #3498db;
            opacity: 0.8;
        }
        .stat-info h3 {
            margin: 0;
            font-size: 1.5rem;
            color: #2c3e50;
        }
        .stat-info p {
            margin: 0;
            color: #7f8c8d;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .badge-auto { background: #e8f4fd; color: #3498db; }
        .badge-manual { background: #fef9e7; color: #f1c40f; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left" style="flex: 1;">
                <h1>Mekan Ziyaret Geçmişi</h1>
                <p style="color: var(--text-muted);">Üye ziyaretleri ve anonim (100m) geçiş istatistiklerini filtreleyin.</p>
            </div>
            <div class="header-right">
                <form method="GET" class="filter-form" style="display: flex; gap: 10px; align-items: center;">
                    <select name="target" style="padding: 8px 12px; border-radius: 8px; border: 1px solid #ddd; min-width: 200px;">
                        <option value="">Tüm Mekanlar</option>
                        <optgroup label="Mekanlar (Park, Tarihi Yer vb.)">
                            <?php foreach($filter_options_places as $opt): ?>
                                <option value="place_<?php echo $opt['id']; ?>" <?php echo $target_filter === "place_".$opt['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($opt['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="İşletmeler (Restoran, Otel vb.)">
                            <?php foreach($filter_options_businesses as $opt): ?>
                                <option value="business_<?php echo $opt['id']; ?>" <?php echo $target_filter === "business_".$opt['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($opt['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                    <button type="submit" class="btn" style="background: #3498db; color: white; padding: 8px 15px;">Filtrele</button>
                    <?php if(!empty($target_filter)): ?>
                        <a href="checkins.php" class="btn" style="background: #95a5a6; color: white; padding: 8px 15px;">Temizle</a>
                    <?php endif; ?>
                </form>
            </div>
        </header>

        <main class="page-content">
            
            <div class="stats-grid">
                <div class="stat-card" style="border-left-color: #3498db;">
                    <i class="fa-solid fa-calendar-check"></i>
                    <div class="stat-info">
                        <h3><?php echo number_format($monthly_count); ?></h3>
                        <p>BU AYKİ TOPLAM (ÜYE+ANONİM)</p>
                    </div>
                </div>
                <div class="stat-card" style="border-left-color: #2ecc71;">
                    <i class="fa-solid fa-chart-line"></i>
                    <div class="stat-info">
                        <h3><?php echo number_format($yearly_count); ?></h3>
                        <p>BU YILKI TOPLAM (ÜYE+ANONİM)</p>
                    </div>
                </div>
                <div class="stat-card" style="border-left-color: #9b59b6;">
                    <i class="fa-solid fa-database"></i>
                    <div class="stat-info">
                        <h3><?php echo number_format($total_count); ?></h3>
                        <p>TOPLAM ETKİLEŞİM</p>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success" style="margin-bottom: 20px; padding: 15px; background: #d4edda; color: #155724; border-radius: 8px;">
                    <?php 
                        if($_GET['msg'] == 'deleted') echo "Ziyaret kaydı başarıyla silindi.";
                    ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Kullanıcı</th>
                            <th>Ziyaret Edilen Yer</th>
                            <th>Tarih</th>
                            <th>Tür</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($checkins as $c): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($c['first_name'] . ' ' . $c['last_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($c['email']); ?></small>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($c['target_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($c['district_name']); ?> (<?php echo $c['target_type'] === 'place' ? 'Mekan' : 'İşletme'; ?>)</small>
                            </td>
                            <td>
                                <?php echo date('d.m.Y H:i', strtotime($c['created_at'])); ?>
                            </td>
                            <td>
                                <?php if (($c['visit_type'] ?? 'MANUAL') === 'AUTO'): ?>
                                    <span class="badge badge-auto"><i class="fa-solid fa-robot"></i> Otomatik</span>
                                <?php else: ?>
                                    <span class="badge badge-manual"><i class="fa-solid fa-hand-pointer"></i> Manuel</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Bu kaydı silmek istediğinize emin misiniz?')">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="delete_id" value="<?php echo (int)$c['id']; ?>">
                                        <button type="submit" class="btn btn-sm" style="color: #e74c3c; border: 1px solid #e74c3c; padding: 5px 10px;">
                                            <i class="fa-solid fa-trash"></i> Sil
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($checkins)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 30px; color: #999;">Henüz ziyaret kaydı bulunmuyor.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>
</html>
