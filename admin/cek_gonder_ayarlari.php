<?php
require_once '../config.php';
require_once 'includes/auth_guard.php';
header('Content-Type: text/html; charset=utf-8');

$page = 'cek_gonder_ayarlari';
$msg = '';
$district_id = intval($_SESSION['admin_district_id'] ?? 0);

// Default structure format (Fallbacks)
$default_types = [
    ['id' => 'Bilgilendirme', 'icon' => 'fa-circle-info', 'color' => '#3498db', 'tr' => 'Bilgi', 'en' => 'Info', 'active' => true],
    ['id' => 'İstek', 'icon' => 'fa-hand', 'color' => '#9b59b6', 'tr' => 'İstek', 'en' => 'Request', 'active' => true],
    ['id' => 'Öneri', 'icon' => 'fa-lightbulb', 'color' => '#f39c12', 'tr' => 'Öneri', 'en' => 'Suggestion', 'active' => true],
    ['id' => 'Şikayet', 'icon' => 'fa-triangle-exclamation', 'color' => '#e74c3c', 'tr' => 'Şikayet', 'en' => 'Complaint', 'active' => true],
    ['id' => 'Teşekkür', 'icon' => 'fa-heart', 'color' => '#e91e63', 'tr' => 'Teşekkür', 'en' => 'Thanks', 'active' => true],
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $menu_cek_gonder_tr = trim($_POST['menu_cek_gonder_tr'] ?? 'Çek Gönder');
    $menu_cek_gonder_en = trim($_POST['menu_cek_gonder_en'] ?? 'Snap & Send');
    
    // Yüklenen type ayarlarını json formatında derleme
    $posted_types = [];
    foreach ($default_types as $index => $def) {
        $tid = $def['id'];
        $posted_types[] = [
            'id'     => $def['id'],
            'icon'   => $def['icon'],
            'color'  => $def['color'],
            'tr'     => trim($_POST["type_tr_{$index}"] ?? $def['tr']),
            'en'     => trim($_POST["type_en_{$index}"] ?? $def['en']),
            'active' => isset($_POST["type_active_{$index}"]) && $_POST["type_active_{$index}"] === '1'
        ];
    }
    
    $json_types = json_encode($posted_types, JSON_UNESCAPED_UNICODE);

    // Save to settings
    $settings_keys = [
        'menu_cek_gonder_tr' => $menu_cek_gonder_tr,
        'menu_cek_gonder_en' => $menu_cek_gonder_en,
        'cek_gonder_types'   => $json_types
    ];

    foreach ($settings_keys as $key => $val) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE district_id = ? AND name = ?");
        $check->execute([$district_id, $key]);
        if ($check->fetchColumn() > 0) {
            $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE district_id = ? AND name = ?");
            $stmt->execute([$val, $district_id, $key]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO settings (district_id, name, value) VALUES (?, ?, ?)");
            $stmt->execute([$district_id, $key, $val]);
        }
    }
    
    $msg = "Ayar başarıyla kaydedildi.";
}

// Fetch Current Settings
$current_settings = [];
$stmt = $pdo->prepare("SELECT name, value FROM settings WHERE district_id = ? AND name IN ('menu_cek_gonder_tr', 'menu_cek_gonder_en', 'cek_gonder_types')");
$stmt->execute([$district_id]);
while($row = $stmt->fetch()) {
    $current_settings[$row['name']] = $row['value'];
}

$title_tr = $current_settings['menu_cek_gonder_tr'] ?? 'Çek Gönder';
$title_en = $current_settings['menu_cek_gonder_en'] ?? 'Snap & Send';

$loaded_types = $default_types;
if (isset($current_settings['cek_gonder_types']) && !empty($current_settings['cek_gonder_types'])) {
    $parsed = json_decode($current_settings['cek_gonder_types'], true);
    if (is_array($parsed) && count($parsed) > 0) {
        $loaded_types = $parsed;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Çek Gönder Ayarları - Admin Paneli</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .settings-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); margin: 20px 0; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #444; }
        .form-group input[type="text"] { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; }
        .type-item { display: flex; align-items: center; gap: 15px; padding: 15px; border: 1px solid #f0f0f0; border-radius: 10px; margin-bottom: 10px; background: #fff; }
        .type-icon { font-size: 24px; width: 40px; text-align: center; }
        .type-controls { flex: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .type-toggle { display: flex; align-items: center; justify-content: flex-end; width: 100px; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        hr { border: 0; border-top: 1px solid #eee; margin: 30px 0; }
        
        /* Switch Toggle Status */
        .switch { position: relative; display: inline-block; width: 50px; height: 26px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #2196F3; }
        input:checked + .slider:before { transform: translateX(24px); }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <header class="main-header">
                <h2><i class="fa-solid fa-gears"></i> Çek Gönder Ayarları</h2>
            </header>

            <div class="content-body">
                <?php if ($msg): ?>
                    <div class="alert alert-success"><?php echo $msg; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="settings-card">
                        <h3>Genel Ayarlar</h3>
                        <p style="color: #666; font-size: 14px; margin-bottom: 20px;">Menü ve başlık ismini bu bölümden düzenleyebilirsiniz.</p>
                        
                        <div class="form-group">
                            <label>Çek Gönder Menü İsmi (Türkçe)</label>
                            <input type="text" name="menu_cek_gonder_tr" value="<?php echo htmlspecialchars($title_tr); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Çek Gönder Menü İsmi (İngilizce)</label>
                            <input type="text" name="menu_cek_gonder_en" value="<?php echo htmlspecialchars($title_en); ?>" required>
                        </div>
                    </div>

                    <div class="settings-card">
                        <h3>Başvuru Tipleri</h3>
                        <p style="color: #666; font-size: 14px; margin-bottom: 20px;">İlçe sakinlerinin size gönderebileceği istek/şikayet tiplerini ve isimlerini yönetin. Pasif bırakılan özellikler mobil uygulamada ve web sitesinde görünmeyecektir.</p>
                        
                        <?php foreach($loaded_types as $index => $type): ?>
                        <div class="type-item">
                            <div class="type-icon" style="color: <?php echo htmlspecialchars($type['color'] ?? '#333'); ?>">
                                <i class="fa-solid <?php echo htmlspecialchars($type['icon']); ?>"></i>
                            </div>
                            <div class="type-controls">
                                <div>
                                    <label style="font-size:12px;color:#888;">Türkçe Görünüm</label>
                                    <input type="text" style="width: 100%; border: 1px solid #ddd; padding: 8px; border-radius: 5px;" 
                                           name="type_tr_<?php echo $index; ?>" value="<?php echo htmlspecialchars($type['tr'] ?? ''); ?>" required>
                                </div>
                                <div>
                                    <label style="font-size:12px;color:#888;">İngilizce Görünüm</label>
                                    <input type="text" style="width: 100%; border: 1px solid #ddd; padding: 8px; border-radius: 5px;" 
                                           name="type_en_<?php echo $index; ?>" value="<?php echo htmlspecialchars($type['en'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="type-toggle">
                                <span style="margin-right:10px; font-weight:bold; font-size: 14px; color: <?php echo ($type['active'] ?? true) ? '#2196F3' : '#aaa'; ?>">Aktif</span>
                                <label class="switch">
                                    <input type="hidden" name="type_active_<?php echo $index; ?>" value="0">
                                    <input type="checkbox" name="type_active_<?php echo $index; ?>" value="1" <?php echo ($type['active'] ?? true) ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" style="width: 100%; padding: 15px; background: #0088cc; color: white; border: none; border-radius: 8px; font-size: 1.1rem; cursor: pointer; font-weight: 600;">
                        <i class="fa-solid fa-save"></i> Ayarları Kaydet
                    </button>
                    <br><br>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
