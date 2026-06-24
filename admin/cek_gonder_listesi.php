<?php
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Tablo yoksa oluştur
$pdo->exec("
    CREATE TABLE IF NOT EXISTS cek_gonder_forms (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        basvuru_turu VARCHAR(50)  NOT NULL,
        ad_soyad    VARCHAR(100) NOT NULL,
        tc_no       VARCHAR(11)  NOT NULL,
        email       VARCHAR(150),
        tel_no      VARCHAR(20),
        aciklama    TEXT         NOT NULL,
        foto1       VARCHAR(255),
        foto2       VARCHAR(255),
        foto3       VARCHAR(255),
        process_status VARCHAR(50) DEFAULT 'Beklemede',
        created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Toggle SMS Ayarı
if (isset($_POST['toggle_sms_enabled']) && $is_super_admin) {
    $new_val = $_POST['sms_enabled'] == '1' ? '1' : '0';
    $stmt = $pdo->prepare("SELECT id FROM settings WHERE name = 'cek_gonder_sms_enabled' AND district_id = 0");
    $stmt->execute();
    if ($stmt->fetch()) {
        $pdo->prepare("UPDATE settings SET value = ? WHERE name = 'cek_gonder_sms_enabled' AND district_id = 0")->execute([$new_val]);
    } else {
        $pdo->prepare("INSERT INTO settings (name, value, district_id) VALUES ('cek_gonder_sms_enabled', ?, 0)")->execute([$new_val]);
    }
    header("Location: cek_gonder_listesi.php?msg=sms_updated");
    exit;
}

$sms_enabled = false;
$stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'cek_gonder_sms_enabled' AND district_id = 0");
$stmt->execute();
if ($row = $stmt->fetch()) {
    $sms_enabled = $row['value'] == '1';
}

// Silme işlemi (Sadece Super Admin)
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && $is_super_admin) {
    $stmt = $pdo->prepare("SELECT foto1, foto2, foto3 FROM cek_gonder_forms WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    $row = $stmt->fetch();
    if ($row) {
        foreach (['foto1','foto2','foto3'] as $f) {
            if ($row[$f] && file_exists('../' . $row[$f])) {
                @unlink('../' . $row[$f]);
            }
        }
        $pdo->prepare("DELETE FROM cek_gonder_forms WHERE id = ?")->execute([(int)$_GET['delete']]);
    }
    $redir = isset($_GET['dashboard']) ? 'index.php' : 'cek_gonder_listesi.php';
    header("Location: $redir?msg=deleted");
    exit;
} else if (isset($_GET['delete']) && !$is_super_admin) {
    $redir = isset($_GET['dashboard']) ? 'index.php' : 'cek_gonder_listesi.php';
    header("Location: $redir?msg=Yetkiniz yok.");
    exit;
}

// Filtre
$filterTur = $_GET['tur'] ?? '';
$search    = trim($_GET['q'] ?? '');
$where = [];
$params = [];

// Adding admin filter logic:
$admin_filter_current = str_replace('district_id', 'c.district_id', $admin_filter);
$where[] = $admin_filter_current;

if ($filterTur) { $where[] = "c.basvuru_turu = ?"; $params[] = $filterTur; }
if ($search)    { $where[] = "(c.ad_soyad LIKE ? OR c.tc_no LIKE ? OR c.email LIKE ?)"; $params = array_merge($params, ["%$search%","%$search%","%$search%"]); }
$sql = "SELECT c.*, d.name AS district_name FROM cek_gonder_forms c LEFT JOIN districts d ON c.district_id = d.id" . ($where ? " WHERE ".implode(" AND ",$where) : "") . " ORDER BY c.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$forms = $stmt->fetchAll();

// Türe göre renkler
$turColors = [
    'Bilgilendirme' => ['#3498db','#ebf5fb'],
    'İstek'         => ['#9b59b6','#f5eef8'],
    'Öneri'         => ['#f39c12','#fef9e7'],
    'Şikayet'       => ['#e74c3c','#fdedec'],
    'Teşekkür'      => ['#e91e63','#fce4ec'],
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Çek Gönder Başvuruları - Admin Paneli</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .filter-bar { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; align-items: center; }
        .filter-bar input, .filter-bar select { padding: 9px 14px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.95rem; font-family: inherit; }
        .filter-bar button { padding: 9px 18px; background: #0088cc; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .badge-tur { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 700; }
        .form-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .form-table th { background: #f7fafc; padding: 12px 14px; text-align: left; border-bottom: 2px solid #e2e8f0; font-weight: 700; color: #4a5568; }
        .form-table td { padding: 11px 14px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
        .form-table tr:hover td { background: #f8f9ff; }
        .foto-thumbs { display: flex; gap: 6px; }
        .foto-thumbs a img { width: 45px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; transition: transform 0.2s; }
        .foto-thumbs a img:hover { transform: scale(1.1); }
        .btn-del { background: #fff5f5; color: #e53e3e; border: 1px solid #fed7d7; padding: 5px 10px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .detail-btn { background: #ebf8ff; color: #2b6cb0; border: 1px solid #bee3f8; padding: 5px 10px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .aciklama-col { max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .stat-cards { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 22px; }
        .stat-card { background: white; border-radius: 12px; padding: 15px; text-align: center; border: 1px solid #e2e8f0; }
        .stat-card .num { font-size: 1.8rem; font-weight: 800; }
        .stat-card .lbl { font-size: 0.78rem; color: #718096; margin-top: 3px; }
        /* Modal */
        .detail-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; padding: 20px; align-items: center; justify-content: center; }
        .detail-modal.open { display: flex; }
        .modal-box { background: white; border-radius: 16px; width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto; padding: 30px; }
        .modal-box h3 { margin-bottom: 20px; font-size: 1.2rem; }
        .detail-row { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
        .detail-row .lbl { width: 130px; color: #718096; font-size: 0.85rem; flex-shrink: 0; }
        .detail-row .val { font-weight: 600; font-size: 0.9rem; }
        @media(max-width: 900px) { .stat-cards { grid-template-columns: repeat(3,1fr); } }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <header class="main-header">
            <h2><i class="fa-solid fa-paper-plane"></i> Çek Gönder Başvuruları</h2>
        </header>
        <main class="page-content">

            <?php if (isset($_GET['msg'])): ?>
                <div style="background:#c6f6d5; color:#2f855a; padding:12px 18px; border-radius:8px; margin-bottom:16px;">
                    <?php 
                        if ($_GET['msg'] === 'deleted') echo '✅ Başvuru silindi.';
                        else if ($_GET['msg'] === 'sms_updated') echo '✅ SMS ayarı güncellendi.';
                        else echo htmlspecialchars($_GET['msg']); 
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($is_super_admin): ?>
            <div style="background:white; border:1px solid #e2e8f0; padding:15px 20px; border-radius:12px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h4 style="margin:0 0 5px 0; color:#2d3748;"><i class="fa-solid fa-comment-sms" style="color:#0088cc;"></i> SMS Bildirim Servisi</h4>
                    <span style="font-size:0.85rem; color:#718096;">"İşleme Alındı" veya "Tamamlandı" işaretlendiğinde başvuru sahibine SMS gitmesini istiyorsanız bu ayarı aktif edin. Bu ayar tüm ilçeler için geçerli olacaktır.</span>
                </div>
                <form method="POST" style="margin:0; display:flex; gap:10px; align-items:center;">
                    <select name="sms_enabled" style="padding:6px 12px; border-radius:6px; border:1px solid #ddd;">
                        <option value="0" <?php echo !$sms_enabled ? 'selected' : ''; ?>>Pasif (Sadece Uygulamada Görünsün)</option>
                        <option value="1" <?php echo $sms_enabled ? 'selected' : ''; ?>>Aktif (SMS Gönder)</option>
                    </select>
                    <button type="submit" name="toggle_sms_enabled" style="background:#0088cc; color:white; border:none; padding:7px 15px; border-radius:6px; cursor:pointer; font-weight:600;">Kaydet</button>
                </form>
            </div>
            <?php endif; ?>

            <?php
            // İstatistikler
            $stats_sql = "SELECT c.basvuru_turu, COUNT(*) as cnt FROM cek_gonder_forms c WHERE " . $admin_filter_current . " GROUP BY c.basvuru_turu";
            $stats = $pdo->query($stats_sql)->fetchAll(PDO::FETCH_KEY_PAIR);
            $total = array_sum($stats);
            $turList = ['Bilgilendirme','İstek','Öneri','Şikayet','Teşekkür'];
            ?>
            <div class="stat-cards">
                <div class="stat-card">
                    <div class="num" style="color:#0088cc;"><?php echo $total ?: 0; ?></div>
                    <div class="lbl">Toplam</div>
                </div>
                <?php foreach ($turList as $tur):
                    [$color] = $turColors[$tur] ?? ['#aaa','#fff'];
                ?>
                <div class="stat-card">
                    <div class="num" style="color:<?php echo $color; ?>"><?php echo $stats[$tur] ?? 0; ?></div>
                    <div class="lbl"><?php echo $tur; ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Filtreler -->
            <form method="GET" class="filter-bar">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Ad, TC, E-posta ara...">
                <select name="tur">
                    <option value="">Tüm Türler</option>
                    <?php foreach ($turList as $tur): ?>
                        <option value="<?php echo $tur; ?>" <?php echo $filterTur === $tur ? 'selected' : ''; ?>><?php echo $tur; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit"><i class="fa-solid fa-search"></i> Filtrele</button>
                <?php if ($filterTur || $search): ?>
                    <a href="cek_gonder_listesi.php" style="padding:9px 14px; background:#eee; border-radius:8px; text-decoration:none; color:#555; font-weight:600;">
                        <i class="fa-solid fa-xmark"></i> Temizle
                    </a>
                <?php endif; ?>
            </form>

            <div class="card" style="overflow-x: auto;">
                <?php if ($forms): ?>
                <table class="form-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <?php if ($is_super_admin): ?>
                            <th>İlçe</th>
                            <?php endif; ?>
                            <th>Tür</th>
                            <th>Ad Soyad</th>
                            <th>TC No</th>
                            <th>İletişim</th>
                            <th>Açıklama</th>
                            <th>Fotoğraf</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($forms as $f):
                            [$color, $bg] = $turColors[$f['basvuru_turu']] ?? ['#aaa','#f0f0f0'];
                        ?>
                        <tr>
                            <td style="color:#aaa; font-size:0.8rem;">#<?php echo $f['id']; ?></td>
                            <?php if ($is_super_admin): ?>
                            <td><span style="font-weight:600; color:#555;"><?php echo htmlspecialchars($f['district_name'] ?? 'Bilinmiyor'); ?></span></td>
                            <?php endif; ?>
                            <td>
                                <span class="badge-tur" style="background:<?php echo $bg; ?>; color:<?php echo $color; ?>;">
                                    <?php echo htmlspecialchars($f['basvuru_turu']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($f['ad_soyad']); ?></td>
                            <td style="font-family:monospace;"><?php echo htmlspecialchars($f['tc_no']); ?></td>
                            <td>
                                <?php if ($f['email']): ?><div style="font-size:0.82rem;"><i class="fa-solid fa-envelope" style="color:#9b59b6;"></i> <?php echo htmlspecialchars($f['email']); ?></div><?php endif; ?>
                                <?php if ($f['tel_no']): ?><div style="font-size:0.82rem;"><i class="fa-solid fa-phone" style="color:#27ae60;"></i> <?php echo htmlspecialchars($f['tel_no']); ?></div><?php endif; ?>
                            </td>
                            <td class="aciklama-col" title="<?php echo htmlspecialchars($f['aciklama']); ?>">
                                <?php echo htmlspecialchars(mb_substr($f['aciklama'], 0, 60, 'UTF-8')); ?><?php echo mb_strlen($f['aciklama'],'UTF-8') > 60 ? '...' : ''; ?>
                            </td>
                            <td>
                                <div class="foto-thumbs">
                                    <?php foreach (['foto1','foto2','foto3'] as $foto): ?>
                                        <?php if ($f[$foto]): ?>
                                            <?php 
                                            $fotoPath = $f[$foto];
                                            $livePath = "../" . $fotoPath;
                                            if (!file_exists($livePath) && file_exists("../laravel_api/public/" . $fotoPath)) {
                                                $livePath = "../laravel_api/public/" . $fotoPath;
                                            }
                                            ?>
                                            <a href="<?php echo htmlspecialchars($livePath); ?>" target="_blank">
                                                <img src="<?php echo htmlspecialchars($livePath); ?>" alt="Fotoğraf">
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php if (!$f['foto1'] && !$f['foto2'] && !$f['foto3']): ?>
                                        <span style="color:#aaa; font-size:0.78rem;">Yok</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $valid_types = ['Şikayet', 'İstek'];
                                $is_valid_type = in_array($f['basvuru_turu'], $valid_types);
                                
                                if ($is_valid_type) {
                                    $p_status = $f['process_status'] ?? 'Beklemede';
                                    $s_bg = '#fefcbf'; $s_col = '#b7791f'; // Beklemede
                                    if ($p_status === 'İşleme Alındı') { $s_bg = '#bee3f8'; $s_col = '#2b6cb0'; }
                                    else if ($p_status === 'Tamamlandı') { $s_bg = '#c6f6d5'; $s_col = '#2f855a'; }
                                    echo '<span style="display:inline-block; padding:4px 8px; border-radius:6px; font-size:0.75rem; font-weight:700; background:'.$s_bg.'; color:'.$s_col.';">' . htmlspecialchars($p_status) . '</span>';
                                } else {
                                    echo '<span style="color:#aaa; font-size:0.8rem;">-</span>';
                                }
                                ?>
                            </td>
                            <td style="font-size:0.8rem; color:#718096; white-space:nowrap;">
                                <?php echo date('d.m.Y', strtotime($f['created_at'])); ?><br>
                                <?php echo date('H:i', strtotime($f['created_at'])); ?>
                            </td>
                            <td>
                                <div style="display:flex; flex-direction:column; gap:5px;">
                                    <?php if ($is_valid_type && ($p_status === 'Beklemede')): ?>
                                    <button class="detail-btn" style="background:#bee3f8; color:#2b6cb0; border-color:#bee3f8;" onclick="updateStatus(<?php echo $f['id']; ?>, 'İşleme Alındı')">
                                        <i class="fa-solid fa-play"></i> İşleme Al
                                    </button>
                                    <?php elseif ($is_valid_type && ($p_status === 'İşleme Alındı')): ?>
                                    <button class="detail-btn" style="background:#c6f6d5; color:#2f855a; border-color:#c6f6d5;" onclick="updateStatus(<?php echo $f['id']; ?>, 'Tamamlandı')">
                                        <i class="fa-solid fa-check"></i> Tamamla
                                    </button>

                                    <?php endif; ?>
                                    <button class="detail-btn" style="background:#edf2f7; color:#4a5568; border-color:#e2e8f0;" onclick='showDetail(<?php echo json_encode($f, JSON_UNESCAPED_UNICODE); ?>)'>
                                        <i class="fa-solid fa-eye"></i> Detay
                                    </button>
                                    <?php if ($is_super_admin): ?>
                                    <a class="btn-del" href="?delete=<?php echo $f['id']; ?>" onclick="return confirm('Bu başvuruyu silmek istediğinizden emin misiniz?')">
                                        <i class="fa-solid fa-trash"></i> Sil
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div style="text-align:center; padding:50px; color:#aaa;">
                        <i class="fa-solid fa-inbox" style="font-size:3rem; margin-bottom:15px;"></i>
                        <p>Henüz başvuru bulunmamaktadır.</p>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>

<!-- Detay Modal -->
<div class="detail-modal" id="detailModal">
    <div class="modal-box">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0;"><i class="fa-solid fa-file-lines"></i> Başvuru Detayı</h3>
            <button onclick="document.getElementById('detailModal').classList.remove('open')"
                style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#aaa;">✕</button>
        </div>
        <div id="detail-content"></div>
        <div id="detail-photos" style="margin-top:20px;"></div>
    </div>
</div>

<script>
function showDetail(f) {
    const turColors = {
        'Bilgilendirme': '#3498db', 'İstek': '#9b59b6', 'Öneri': '#f39c12',
        'Şikayet': '#e74c3c', 'Teşekkür': '#e91e63'
    };
    const color = turColors[f.basvuru_turu] || '#555';
    const rows = [
        ['Başvuru Türü', `<span style="color:${color}; font-weight:800;">${f.basvuru_turu}</span>`],
        ['İlçe', f.district_name || 'Bilinmiyor'],
        ['Ad Soyad', f.ad_soyad],
        ['TC Kimlik No', `<span style="font-family:monospace;">${f.tc_no}</span>`],
        ['E-posta', f.email || '<span style="color:#aaa">Girilmemiş</span>'],
        ['Telefon', f.tel_no || '<span style="color:#aaa">Girilmemiş</span>'],
        ['Başvuru Tarihi', f.created_at],
        ['Açıklama', `<div style="white-space:pre-wrap;">${f.aciklama}</div>`],
    ];
    document.getElementById('detail-content').innerHTML = rows.map(([l,v]) =>
        `<div class="detail-row"><div class="lbl">${l}</div><div class="val">${v}</div></div>`
    ).join('');

    const photos = [f.foto1, f.foto2, f.foto3].filter(Boolean);
    document.getElementById('detail-photos').innerHTML = photos.length
        ? `<p style="font-weight:700; margin-bottom:10px;"><i class="fa-solid fa-images"></i> Fotoğraflar</p>
           <div style="display:flex; gap:8px; flex-wrap:wrap;">
           ${photos.map(p => `<a href="../${p}" target="_blank"><img src="../${p}" style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #ddd;"></a>`).join('')}
           </div>`
        : '';

    document.getElementById('detailModal').classList.add('open');
}

function updateStatus(id, newStatus) {
    if(!confirm(`Bu başvuruyu "${newStatus}" durumuna geçirmek istediğinize emin misiniz?`)) return;
    
    fetch('api_update_cek_gonder_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&status=${encodeURIComponent(newStatus)}`
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            let msg = 'Durum güncellendi.';
            if (data.sms_sent) msg += '\n✅ SMS gönderildi.';
            
            if (data.push_sent) {
                msg += '\n✅ Mobil bildirim gönderildi.';
            } else {
                msg += '\n❌ Mobil bildirim GİTMEDİ.';
                if (data.push_error) msg += '\nSebep: ' + data.push_error;
            }
            
            alert(msg);
            location.reload();
        } else {
            alert('Hata: ' + data.message);
        }
    })
    .catch(err => {
        alert('İstek sırasında bir hata oluştu');
    });
}
</script>
</body>
</html>
