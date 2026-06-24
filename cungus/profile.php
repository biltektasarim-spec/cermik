<?php
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT u.*, d.name as district_name FROM users u LEFT JOIN districts d ON u.district_id = d.id WHERE u.id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch Check-ins (Pending and Approved)
$stmt = $pdo->prepare("
    SELECT c.id, c.status, c.created_at, c.target_type as type, d.name as district_name,
           COALESCE(p.name, b.business_name) as name
    FROM check_ins c
    LEFT JOIN places p ON (c.target_id = p.id AND c.target_type = 'place')
    LEFT JOIN businesses b ON (c.target_id = b.id AND c.target_type = 'business')
    LEFT JOIN districts d ON c.district_id = d.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$user_id]);
$checkins = $stmt->fetchAll();

$approved_checkins_count = 0;
foreach ($checkins as $c) {
    if ($c['status'] === 'APPROVED') {
        $approved_checkins_count++;
    }
}

// Fetch Cek Gonder Submissions
// We look up by user_id OR email OR phone to catch older ones before user_id was stored
$stmt = $pdo->prepare("
    SELECT f.*, d.name as district_name
    FROM cek_gonder_forms f
    LEFT JOIN districts d ON f.district_id = d.id
    WHERE f.user_id = ? 
       OR (f.email IS NOT NULL AND f.email != '' AND f.email = ?) 
       OR (f.tel_no IS NOT NULL AND f.tel_no != '' AND f.tel_no = ?)
    ORDER BY f.created_at DESC
");
$stmt->execute([$user_id, $user['email'], $user['phone']]);
$submissions = $stmt->fetchAll();

$district_id = $_SESSION['district_id'] ?? ($_COOKIE['district_id'] ?? 0);
$settings = get_settings($pdo, $district_id);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Profilim - ROTAREHBER</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        html, body { 
            height: 100dvh; 
            margin: 0; 
            padding: 0; 
            overflow: hidden !important; 
            position: fixed; 
            width: 100%;
        }
        #app {
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding-bottom: 70px;
        }
        .header { flex-shrink: 0; }
        .profile-header {
            text-align: center;
            padding: 20px 20px 40px;
            background: linear-gradient(180deg, rgba(0,201,255,0.2) 0%, transparent 100%);
            border-radius: 0 0 30px 30px;
            flex-shrink: 0;
        }
        .avatar-container {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 0 auto 10px;
        }
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid var(--primary);
            object-fit: cover;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
        }
        .edit-avatar {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--primary);
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            border: 2px solid var(--bg-dark);
            cursor: pointer;
            color: #000;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin: -30px 15px 15px;
            flex-shrink: 0;
        }
        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            padding: 10px 5px;
            border-radius: 15px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .stat-card h3 { font-size: 1.1rem; margin-bottom: 2px; color: var(--primary); }
        .stat-card span { font-size: 0.6rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase; }

        .profile-main {
            flex: 1;
            overflow-y: auto;
            padding: 0 20px 10px;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .profile-main::-webkit-scrollbar { display: none; }

        .tabs-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            position: sticky;
            top: 0;
            background: var(--bg-dark);
            padding: 10px 0;
            z-index: 5;
        }
        .tab-btn {
            flex: 1;
            padding: 8px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            border-radius: 12px;
            font-size: 0.8rem;
            cursor: pointer;
        }
        .tab-btn.active {
            background: rgba(0,201,255,0.1);
            border-color: var(--primary);
            color: var(--primary);
        }
        .tab-panel {
            display: none;
        }
        .tab-panel.active {
            display: block;
        }

        .checkin-item {
            padding: 12px;
            background: rgba(255,255,255,0.03);
            border-radius: 12px;
            margin-bottom: 8px;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .badge-status {
            font-size: 0.7rem; padding: 2px 6px; border-radius: 8px; color: #000; font-weight: 600;
        }
        .status-PENDING { background: #facc15; }
        .status-APPROVED { background: #4ade80; }
        .status-REJECTED { background: #f87171; }

        .cek-gonder-status {
            font-size: 0.7rem; padding: 3px 8px; border-radius: 8px; color: #fff; font-weight: 600; background: var(--primary); color: #000;
        }
        .cek-gonder-type {
            font-size: 0.75rem; color: var(--secondary); font-weight: 600; margin-bottom: 5px;
        }
    </style>
</head>
<body data-page-context="Profil" data-district-slug="cungus">
<?php include '../includes/theme_bg.php'; ?>
<div id="app">
    <header class="header">
        <a href="index.php" class="home-link"><i class="fa-solid fa-house"></i></a>
        <h1>Profilim</h1>
        <a href="../api/user_auth.php?action=logout" style="color: #f56565; font-size: 1.2rem;"><i class="fa-solid fa-right-from-bracket"></i></a>
    </header>

    <div class="profile-header animate-in">
        <div class="avatar-container">
            <?php 
            $profile_img = $user['profile_image'] ?: 'assets/img/default-avatar.png';
            $is_sub_dir = (strpos($_SERVER['PHP_SELF'], '/cermik/') !== false || strpos($_SERVER['PHP_SELF'], '/cungus/') !== false);
            $img_prefix = $is_sub_dir ? '../' : '';
            
            // If it's an external Google image, don't add prefix
            if ($user['profile_image'] && (strpos($user['profile_image'], 'http') === 0)) {
                $img_src = $user['profile_image'];
            } else {
                $img_src = $img_prefix . $profile_img;
            }
            ?>
            <img src="<?php echo $img_src; ?>" id="avatar-preview" class="profile-avatar" alt="Profil">
            <label for="avatar-input" class="edit-avatar">
                <i class="fa-solid fa-camera"></i>
            </label>
            <input type="file" id="avatar-input" style="display: none;" accept="image/*" onchange="uploadAvatar(this)">
        </div>
        <h2 style="margin-bottom: 5px; font-weight: 700; color: white;"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
        <p style="color: var(--text-secondary); font-size: 0.8rem; margin-top: 5px;"><?php echo $user['email']; ?></p>
        <?php if($user['district_name']): ?>
            <p style="color: var(--primary); font-size: 0.8rem; margin-top: 5px;"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($user['district_name']); ?></p>
        <?php endif; ?>
    </div>

    <div class="stats-grid animate-in" style="grid-template-columns: 1fr 1fr;">
        <div class="stat-card">
            <h3><?php echo $approved_checkins_count; ?></h3>
            <span>KEŞİFLERİM (CHECK-IN)</span>
        </div>
        <div class="stat-card">
            <h3><?php echo count($submissions); ?></h3>
            <span>ÇEK GÖNDER KAYDI</span>
        </div>
    </div>

    <div class="profile-main animate-in">
        <div class="tabs-nav">
            <button class="tab-btn active" onclick="switchProfileTab('checkins', this)">Check-inlerim</button>
            <button class="tab-btn" onclick="switchProfileTab('submissions', this)">Çek Gönder Geçmişi</button>
        </div>

        <div id="tab-checkins" class="tab-panel active">
            <?php if ($checkins): ?>
                <?php foreach ($checkins as $c): ?>
                    <div class="checkin-item">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div>
                                <h4 style="margin: 0; font-size: 1.05rem;"><?php echo htmlspecialchars($c['name']); ?></h4>
                                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 5px;">
                                    <i class="fa-solid fa-map-pin"></i> <?php echo htmlspecialchars($c['district_name']); ?>
                                </p>
                            </div>
                            </div>

                        <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 10px; text-align: right;">
                            <?php echo date('d.m.Y H:i', strtotime($c['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; padding: 20px; opacity: 0.5;">Henüz hiç check-in yapmadınız.</p>
            <?php endif; ?>
        </div>

        <div id="tab-submissions" class="tab-panel">
            <?php if ($submissions): ?>
                <?php foreach ($submissions as $sub): ?>
                    <div class="checkin-item">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div>
                                <div class="cek-gonder-type"><i class="fa-solid fa-paper-plane"></i> <?php echo htmlspecialchars($sub['basvuru_turu']); ?></div>
                                <h4 style="margin: 0; font-size: 0.95rem; line-height: 1.4;"><?php echo mb_strimwidth(htmlspecialchars($sub['aciklama']), 0, 80, "..."); ?></h4>
                                <?php if($sub['district_name']): ?>
                                    <p style="font-size: 0.75rem; color: var(--secondary); margin-top: 5px; font-weight: 600;">
                                        <i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($sub['district_name']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <?php 
                                $p_status = $sub['process_status'] ?? 'Beklemede';
                                $s_bg = '#facc15'; $s_col = '#854d0e'; // Beklemede
                                if ($p_status === 'İşleme Alındı') { $s_bg = '#60a5fa'; $s_col = '#1e3a8a'; }
                                else if ($p_status === 'Tamamlandı' || $p_status === 'Çözüldü') { $s_bg = '#4ade80'; $s_col = '#14532d'; }
                                
                                // Şikayet veya İstek mi kontrol et, değilse düz stil
                                $b_turu = mb_strtolower(trim($sub['basvuru_turu']), 'UTF-8');
                                $valid_types = ['şikayet', 'i̇stek', 'istek'];
                                if (!in_array($b_turu, $valid_types)) {
                                    $p_status = 'İletildi';
                                    $s_bg = 'var(--primary)';
                                    $s_col = '#000';
                                }
                            ?>
                            <div class="cek-gonder-status" style="background: <?php echo $s_bg; ?>; color: <?php echo $s_col; ?>;">
                                <?php echo htmlspecialchars($p_status); ?>
                            </div>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 10px; text-align: right;">
                            <?php echo date('d.m.Y H:i', strtotime($sub['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; padding: 20px; opacity: 0.5;">Henüz bir gönderiminiz bulunmuyor.</p>
            <?php endif; ?>
        </div>

        <div class="card" style="margin-top: 15px;">
            <h3><i class="fa-solid fa-building"></i> Kurumsal</h3>
            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;">
                <a href="javascript:void(0)" onclick="showPolicy('kvkk')" style="color: var(--text-secondary); text-decoration: none; font-size: 0.8rem; padding: 8px; background: rgba(255,255,255,0.03); border-radius: 8px;"><i class="fa-solid fa-file-contract"></i> KVKK Aydınlatma Metni</a>
                <a href="javascript:void(0)" onclick="showPolicy('cookie')" style="color: var(--text-secondary); text-decoration: none; font-size: 0.8rem; padding: 8px; background: rgba(255,255,255,0.03); border-radius: 8px;"><i class="fa-solid fa-cookie-bite"></i> Çerez Politikası</a>
            </div>
        </div>
    </div>
</div>

<script>
function switchProfileTab(tab, btnElement) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    
    document.getElementById('tab-' + tab).classList.add('active');
    btnElement.classList.add('active');
}

function showPolicy(type) {
    const titleEl = document.getElementById('policy-title');
    const contentEl = document.getElementById('policy-content');

    if (type === 'kvkk') {
        titleEl.textContent = 'KVKK';
        contentEl.innerHTML = document.getElementById('kvkk-content-data').innerHTML || 'Henüz metin girilmemiş.';
    } else if (type === 'cookie') {
        titleEl.textContent = 'Çerez Politikası';
        contentEl.innerHTML = document.getElementById('cookie-content-data').innerHTML || 'Henüz metin girilmemiş.';
    }

    document.getElementById('policy-modal').classList.add('active');
}

function uploadAvatar(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Dosya boyutu kontrolü (Maksimum 5MB)
        if (file.size > 5 * 1024 * 1024) {
            app.showToast('Dosya boyutu çok büyük (Maksimum 5MB)', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('profile_image', file);

        document.getElementById('avatar-preview').style.opacity = '0.5';
        
        const apiPath = app.getApiUrl('update_profile.php');

        fetch(apiPath, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if(res.status === 'success') {
                const imgPrefix = isSubDir ? '../' : '';
                document.getElementById('avatar-preview').src = imgPrefix + res.image_path + '?v=' + Date.now();
                app.showToast('Profil fotoğrafı güncellendi', 'success');
            } else {
                app.showToast(res.message, 'error');
            }
            document.getElementById('avatar-preview').style.opacity = '1';
        })
        .catch(err => {
            app.showToast('İşlem sırasında bir hata oluştu', 'error');
            document.getElementById('avatar-preview').style.opacity = '1';
        });
    }
}
</script>
<script src="../assets/js/app.js"></script>
<script>
app.isLoggedIn = true;
</script>
<!-- Bottom Navigation -->
<?php include '../includes/bottom_nav.php'; ?>
</body>
</html>
