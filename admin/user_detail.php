<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

$id = $_GET['id'] ?? 0;

// Ana kullanıcı bilgilerini çek
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die("Kullanıcı bulunamadı.");
}

// Son Gidilen Mekan
$stmt = $pdo->prepare("SELECT p.name, v.visit_date FROM user_visits v JOIN places p ON v.place_id = p.id WHERE v.user_id = ? ORDER BY v.visit_date DESC LIMIT 1");
$stmt->execute([$id]);
$last_visit = $stmt->fetch();

// Tüm Gezi Geçmişi
$stmt = $pdo->prepare("SELECT p.name, v.visit_date FROM user_visits v JOIN places p ON v.place_id = p.id WHERE v.user_id = ? ORDER BY v.visit_date DESC");
$stmt->execute([$id]);
$all_visits = $stmt->fetchAll();

// AI Konuşma Geçmişi
$stmt = $pdo->prepare("SELECT * FROM ai_chat_logs WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$id]);
$ai_logs = $stmt->fetchAll();

// Paylaşılan Resimler
$stmt = $pdo->prepare("SELECT * FROM submissions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$id]);
$submissions = $stmt->fetchAll();

// Çek Gönder Geçmişi
$stmt = $pdo->prepare("SELECT c.*, d.name AS district_name FROM cek_gonder_forms c LEFT JOIN districts d ON c.district_id = d.id WHERE c.user_id = ? OR c.email = ? OR c.tel_no = ? ORDER BY c.created_at DESC");
$stmt->execute([$id, $user['email'], $user['phone']]);
$cek_gonder_history = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Detayı - <?php echo htmlspecialchars($user['email']); ?></title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .detail-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; }
        .user-sidebar { background: #fff; padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); height: fit-content; }
        .user-avatar { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #f0f0f0; margin: 0 auto 15px; display: block; }
        .info-label { font-size: 0.8rem; color: #a0aec0; text-transform: uppercase; letter-spacing: 1px; margin-top: 15px; }
        .info-value { font-weight: 600; color: #2d3748; }
        
        .activity-tabs { margin-top: 25px; }
        .tab-nav { display: flex; gap: 10px; border-bottom: 2px solid #edf2f7; margin-bottom: 20px; }
        .tab-link { padding: 10px 20px; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; font-weight: 600; color: #718096; }
        .tab-link.active { color: #3182ce; border-bottom-color: #3182ce; }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }

        .log-item { padding: 15px; border-bottom: 1px solid #edf2f7; }
        .log-date { font-size: 0.75rem; color: #a0aec0; }
        .log-content { margin-top: 5px; }
        
        .submission-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; }
        .submission-card img { width: 100%; height: 150px; object-fit: cover; border-radius: 10px; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>Vatandaş Detay Bilgisi</h1>
                <p style="color: var(--text-muted);">Kullanıcı hareketleri ve iletişim bilgileri.</p>
            </div>
            <div class="header-right">
                <a href="users.php" class="btn" style="border: 1px solid #ddd;">Geri Dön</a>
            </div>
        </header>

        <main class="page-content">
            <div class="detail-grid">
                <!-- Sol: Kullanıcı Kartı -->
                <div class="user-sidebar">
                    <?php 
                    $profileImg = $user['profile_image'] ? $user['profile_image'] : 'assets/img/default-avatar.png';
                    $livePath = "../" . $profileImg;
                    if ($user['profile_image'] && !file_exists($livePath) && file_exists("../laravel_api/public/" . $profileImg)) {
                        $livePath = "../laravel_api/public/" . $profileImg;
                    } elseif (!$user['profile_image']) {
                        $livePath = "assets/img/default-avatar.png"; // Fallback if no image at all
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($livePath); ?>" class="user-avatar" alt="Avatar">
                    <h2 style="text-align:center; margin-bottom: 5px;"><?php echo explode('@', $user['email'])[0]; ?></h2>
                    <p style="text-align:center; color: #38a169; font-weight: 600; font-size: 0.9rem;"><i class="fa-solid fa-circle-check"></i> <?php echo $user['is_active'] ? 'Aktif Üye' : 'Pasif Üye'; ?></p>

                    <div class="info-label">E-Posta Adresi</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>

                    <div class="info-label">Telefon Numarası</div>
                    <div class="info-value"><?php echo $user['phone'] ?: 'Belirtilmemiş'; ?></div>

                    <div class="info-label">Kayıt Tarihi</div>
                    <div class="info-value"><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></div>

                    <div class="info-label">Son Giriş Zamanı</div>
                    <div class="info-value"><?php echo $user['last_login_at'] ? date('d.m.Y H:i', strtotime($user['last_login_at'])) : 'Hiç giriş yapmadı'; ?></div>

                    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

                    <div class="info-label">Son Gezdiği Mekan</div>
                    <div class="info-value" style="color: #3182ce;">
                        <?php echo $last_visit ? $last_visit['name'] : 'Kayıt bulunamadı'; ?>
                    </div>
                    <?php if ($last_visit): ?>
                        <div style="font-size: 0.75rem; color: #a0aec0;"><?php echo date('d.m.Y H:i', strtotime($last_visit['visit_date'])); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Sağ: Hareket Dökümü -->
                <div class="activity-tabs">
                    <div class="tab-nav">
                        <div class="tab-link active" onclick="openTab(event, 'visits')">Gezi Geçmişi (<?php echo count($all_visits); ?>)</div>
                        <div class="tab-link" onclick="openTab(event, 'ai')">AI Sohbeti (<?php echo count($ai_logs); ?>)</div>
                        <div class="tab-link" onclick="openTab(event, 'uploads')">Paylaşımlar (<?php echo count($submissions); ?>)</div>
                        <div class="tab-link" onclick="openTab(event, 'cek_gonder')">Çek Gönder (<?php echo count($cek_gonder_history); ?>)</div>
                    </div>

                    <!-- Gezi Geçmişi -->
                    <div id="visits" class="tab-pane active card">
                        <?php if ($all_visits): ?>
                            <?php foreach ($all_visits as $v): ?>
                                <div class="log-item">
                                    <div class="log-date"><?php echo date('d.m.Y H:i', strtotime($v['visit_date'])); ?></div>
                                    <div class="log-content"><strong><?php echo $v['name']; ?></strong> mekanını ziyaret etti.</div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="padding: 20px; color: #718096; text-align: center;">Gezi kaydı bulunmuyor.</p>
                        <?php endif; ?>
                    </div>

                    <!-- AI Sohbeti -->
                    <div id="ai" class="tab-pane card">
                        <?php if ($ai_logs): ?>
                            <?php foreach ($ai_logs as $log): ?>
                                <div class="log-item" style="background: rgba(49, 130, 206, 0.02);">
                                    <div class="log-date"><?php echo date('d.m.Y H:i', strtotime($log['created_at'])); ?></div>
                                    <div class="log-content">
                                        <div style="color: #2d3748; font-weight: 600; margin-bottom: 5px;"><i class="fa-solid fa-comment-dots"></i> <?php echo htmlspecialchars($log['question']); ?></div>
                                        <div style="font-size: 0.9rem; color: #4a5568; border-left: 3px solid #3182ce; padding-left: 10px;"><?php echo htmlspecialchars($log['answer']); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="padding: 20px; color: #718096; text-align: center;">AI sohbet kaydı bulunmuyor.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Paylaşımlar -->
                    <div id="uploads" class="tab-pane card">
                        <?php if ($submissions): ?>
                            <div class="submission-grid" style="padding: 15px;">
                                <?php foreach ($submissions as $s): ?>
                                    <div class="submission-card">
                                        <img src="../<?php echo $s['image_path']; ?>" alt="Paylaşım">
                                        <div style="font-size: 0.75rem; margin-top: 5px; font-weight: 600; text-align: center;">
                                            <?php echo htmlspecialchars($s['title']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="padding: 20px; color: #718096; text-align: center;">Henüz paylaşım yapılmamış.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Çek Gönder Geçmişi -->
                    <div id="cek_gonder" class="tab-pane card">
                        <?php if ($cek_gonder_history): ?>
                            <?php foreach ($cek_gonder_history as $cg): ?>
                                <div class="log-item">
                                    <div class="log-date"><?php echo date('d.m.Y H:i', strtotime($cg['created_at'])); ?></div>
                                    <div class="log-content">
                                        <div style="font-weight: 600; color: #2d3748; margin-bottom: 5px;">
                                            <?php echo htmlspecialchars($cg['basvuru_turu']); ?>
                                            - <?php echo htmlspecialchars($cg['district_name'] ?? 'Bilinmiyor'); ?>
                                        </div>
                                        <div style="font-size: 0.9rem; color: #4a5568; margin-bottom: 5px;">
                                            <?php echo htmlspecialchars($cg['aciklama']); ?>
                                        </div>
                                        <div>
                                            <?php 
                                            $p_status = $cg['process_status'] ?? 'Beklemede';
                                            $s_bg = '#fefcbf'; $s_col = '#b7791f'; 
                                            if ($p_status === 'İşleme Alındı') { $s_bg = '#bee3f8'; $s_col = '#2b6cb0'; }
                                            else if ($p_status === 'Tamamlandı') { $s_bg = '#c6f6d5'; $s_col = '#2f855a'; }
                                            ?>
                                            <span style="font-size: 0.75rem; font-weight: bold; background: <?php echo $s_bg; ?>; color: <?php echo $s_col; ?>; padding: 3px 8px; border-radius: 6px;">
                                                <?php echo htmlspecialchars($p_status); ?>
                                            </span>
                                        </div>
                                        <div style="margin-top: 10px; display: flex; gap: 8px;">
                                            <?php foreach (['foto1','foto2','foto3'] as $foto): ?>
                                                <?php if ($cg[$foto]): ?>
                                                    <?php 
                                                    $fotoPath = $cg[$foto];
                                                    $livePath = "../" . $fotoPath;
                                                    if (!file_exists($livePath) && file_exists("../laravel_api/public/" . $fotoPath)) {
                                                        $livePath = "../laravel_api/public/" . $fotoPath;
                                                    }
                                                    ?>
                                                    <a href="<?php echo htmlspecialchars($livePath); ?>" target="_blank">
                                                        <img src="<?php echo htmlspecialchars($livePath); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd;" alt="Fotoğraf">
                                                    </a>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="padding: 20px; color: #718096; text-align: center;">Çek Gönder kaydı bulunmuyor.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-pane");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].classList.remove("active");
        }
        tablinks = document.getElementsByClassName("tab-link");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active");
        }
        document.getElementById(tabName).classList.add("active");
        evt.currentTarget.classList.add("active");
    }
    </script>
</body>
</html>
