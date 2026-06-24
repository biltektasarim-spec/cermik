<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/auth_guard.php';

$page = basename($_SERVER['PHP_SELF'], ".php");
$is_super = ($_SESSION['admin_role'] === 'SUPER_ADMIN');

// Aktif ilçe ismini bul (District Admin ise kendi ilçesi, Super Admin ise 'Genel')
$sidebar_title = 'YÖNETİM';
$current_district_name = 'GENEL SİSTEM';
$district_id = intval($_SESSION['admin_district_id'] ?? 0);

if ($district_id > 0) {
    $stmt = $pdo->prepare("SELECT name FROM districts WHERE id = ?");
    $stmt->execute([$district_id]);
    $current_district_name = $stmt->fetchColumn() ?: 'BİLİNMEYEN İLÇE';
    $sidebar_title = mb_strtoupper($current_district_name);
} else if ($is_super) {
    if (isset($_SESSION['admin_view_mode']) && $_SESSION['admin_view_mode'] === 'DISTRICT') {
        // Super admin bir ilçe paneline geçmişse
        $stmt = $pdo->prepare("SELECT name FROM districts WHERE id = ?");
        $stmt->execute([$district_id]);
        $current_district_name = $stmt->fetchColumn() ?: 'İLÇE';
    }
    $sidebar_title = 'S.ADMIN';
}
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h2><?php echo $sidebar_title; ?></h2>
        <div style="background: rgba(255,255,255,0.1); padding: 5px 10px; border-radius: 5px; margin-top: 5px; font-size: 0.8rem; border-left: 3px solid #3498db;">
            <i class="fa-solid fa-location-dot"></i> <strong>MOD:</strong> <?php echo $current_district_name; ?>
        </div>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="index.php" class="<?php echo $page == 'index' ? 'active' : ''; ?>">
                <i class="fa-solid fa-gauge"></i> Dashboard
            </a>
        </li>
        <?php if ($is_super && $district_id == 0): ?>
        <li>
            <a href="districts_manage.php" class="<?php echo $page == 'districts_manage' ? 'active' : ''; ?>">
                <i class="fa-solid fa-earth-africa"></i> Tüm İlçeler
            </a>
        </li>
        <?php endif; ?>
        <?php if ($district_id > 0): ?>
        <li>
            <a href="places_historical.php" class="<?php echo $page == 'places_historical' ? 'active' : ''; ?>">
                <i class="fa-solid fa-monument"></i> Tarihi Mekanlar
            </a>
        </li>
        <li>
            <a href="places_nature.php" class="<?php echo $page == 'places_nature' ? 'active' : ''; ?>">
                <i class="fa-solid fa-leaf"></i> Doğa
            </a>
        </li>
        <li>
            <a href="places_parks.php" class="<?php echo $page == 'places_parks' ? 'active' : ''; ?>">
                <i class="fa-brands fa-pagelines"></i> Park ve Bahçeler
            </a>
        </li>
        <li>
            <?php 
                $stmt_hm = $pdo->prepare("SELECT name FROM places WHERE category = 'HotSpring' AND district_id = ? LIMIT 1");
                $stmt_hm->execute([$district_id]);
                $hm_name = $stmt_hm->fetchColumn() ?: 'Özel Sayfa Yönetimi';
                
                // İlçeye göre özel link belirle
                $hm_link = 'special_menu_edit.php'; // Varsayılan (Yeni İlçeler)
                if ($district_id == 3) $hm_link = 'kaplica_edit.php';
                if ($district_id == 5) $hm_link = 'special_menu_edit.php'; // Çüngüş için de Karakaya yerine bu kullanılıyor
            ?>
            <a href="<?php echo $hm_link; ?>" class="<?php echo ($page == 'special_menu_edit' || $page == 'kaplica_edit') ? 'active' : ''; ?>">
                <i class="fa-solid fa-layer-group"></i> <?php echo htmlspecialchars($hm_name); ?>
            </a>
        </li>
        <li>
            <a href="hospital_pharmacy.php" class="<?php echo ($page == 'hospital_pharmacy' || $page == 'hospital_add' || $page == 'pharmacy_add') ? 'active' : ''; ?>">
                <i class="fa-solid fa-hospital"></i> Hastane & Eczane
            </a>
        </li>
        <li>
            <a href="projects.php" class="<?php echo $page == 'projects' ? 'active' : ''; ?>">
                <i class="fa-solid fa-diagram-project"></i> Projeler / Hizmetler
            </a>
        </li>
        <li>
            <a href="live_broadcasts.php" class="<?php echo ($page == 'live_broadcasts' || $page == 'live_broadcast_edit') ? 'active' : ''; ?>">
                <i class="fa-solid fa-video"></i> Canlı Yayın Yönetimi
            </a>
        </li>
        <li>
            <a href="settings_mayor.php" class="<?php echo $page == 'settings_mayor' ? 'active' : ''; ?>">
                <i class="fa-solid fa-user-tie"></i> Başkan Ayarları
            </a>
        </li>
        <?php endif; ?>
        <li>
            <a href="businesses.php" class="<?php echo $page == 'businesses' ? 'active' : ''; ?>">
                <i class="fa-solid fa-shop"></i> İşletmeler
            </a>
        </li>
        <li>
            <a href="announcements.php" class="<?php echo $page == 'announcements' ? 'active' : ''; ?>">
                <i class="fa-solid fa-bullhorn"></i> Duyurular
            </a>
        </li>
        <?php if ($is_super && $district_id == 0): ?>
        <li>
            <a href="users.php" class="<?php echo $page == 'users' ? 'active' : ''; ?>">
                <i class="fa-solid fa-users"></i> Kullanıcılar
            </a>
        </li>
        <?php endif; ?>
        <li>
            <a href="settings_general.php" class="<?php echo $page == 'settings_general' ? 'active' : ''; ?>">
                <i class="fa-solid fa-gears"></i> Genel Ayarlar
            </a>
        </li>
        <li>
            <a href="municipal_guide.php" class="<?php echo $page == 'municipal_guide' ? 'active' : ''; ?>">
                <i class="fa-solid fa-book-atlas"></i> Belediye Rehberi
            </a>
        </li>

        <?php 
        // Özel Menüleri (Custom Pages) Getir
        $stmt_custom = $pdo->prepare("SELECT name_tr, place_id FROM custom_menus WHERE district_id = ? AND is_active = 1 AND place_id IS NOT NULL ORDER BY sort_order ASC");
        $stmt_custom->execute([$district_id]);
        $custom_sidebar_menus = $stmt_custom->fetchAll();
        if ($custom_sidebar_menus):
            foreach ($custom_sidebar_menus as $cm):
                $is_active_cm = (isset($_GET['id']) && $_GET['id'] == $cm['place_id'] && $page == 'place_edit');
        ?>
        <li>
            <a href="place_edit.php?id=<?php echo $cm['place_id']; ?>" class="<?php echo $is_active_cm ? 'active' : ''; ?>">
                <i class="fa-solid fa-file-pen"></i> <?php echo htmlspecialchars($cm['name_tr']); ?>
            </a>
        </li>
        <?php endforeach; endif; ?>

        <?php if ($is_super && ($_SESSION['admin_view_mode'] ?? '') !== 'DISTRICT'): ?>
        <li>
            <a href="district_menus.php" class="<?php echo $page == 'district_menus' ? 'active' : ''; ?>">
                <i class="fa-solid fa-bars-staggered"></i> İlçe Menüleri
            </a>
        </li>
        <?php endif; ?>
        <?php if ($is_super && $district_id == 0): ?>
        <li>
            <a href="communications.php" class="<?php echo $page == 'communications' ? 'active' : ''; ?>">
                <i class="fa-solid fa-envelope-open-text"></i> İletişim Merkezi
            </a>
        </li>
        <li>
            <a href="mail_send.php" class="<?php echo $page == 'mail_send' ? 'active' : ''; ?>">
                <i class="fa-solid fa-paper-plane"></i> Toplu Mail Gönder
            </a>
        </li>
        <li>
            <a href="settings_firebase.php" class="<?php echo $page == 'settings_firebase' ? 'active' : ''; ?>">
                <i class="fa-solid fa-fire"></i> Firebase Ayarları
            </a>
        </li>
        <li>
            <a href="fcm_send.php" class="<?php echo $page == 'fcm_send' ? 'active' : ''; ?>">
                <i class="fa-solid fa-bell"></i> Bildirim Gönder (Push)
            </a>
        </li>
        <?php endif; ?>
        <?php if ($is_super && $district_id == 0): ?>
        <li>
            <a href="events_global.php" class="<?php echo $page == 'events_global' ? 'active' : ''; ?>">
                <i class="fa-solid fa-earth-americas"></i> Genel Etkinlikler
            </a>
        </li>
        <?php endif; ?>
        <li>
            <a href="cek_gonder_listesi.php" class="<?php echo $page == 'cek_gonder_listesi' ? 'active' : ''; ?>">
                <i class="fa-solid fa-paper-plane"></i> Çek Gönder Başvuruları
            </a>
        </li>
        <?php if ($district_id > 0): ?>
        <li>
            <a href="cek_gonder_ayarlari.php" class="<?php echo $page == 'cek_gonder_ayarlari' ? 'active' : ''; ?>">
                <i class="fa-solid fa-sliders"></i> Çek Gönder Ayarları
            </a>
        </li>
        <?php endif; ?>
        <li>
            <a href="checkins.php" class="<?php echo $page == 'checkins' ? 'active' : ''; ?>">
                <i class="fa-solid fa-street-view"></i> Mekan Ziyaretleri
            </a>
        </li>
        <?php if ($district_id > 0 && $district_id != 5): ?>
        <li>
            <a href="place_edit.php?id=<?php 
                $stmt = $pdo->prepare("SELECT id FROM places WHERE category = 'Kuruyemis' AND district_id = ?");
                $stmt->execute([$district_id]);
                echo $stmt->fetchColumn() ?: '0';
            ?>" class="<?php echo (isset($_GET['id']) && $pdo->query("SELECT category FROM places WHERE id = ".intval($_GET['id']))->fetchColumn() == 'Kuruyemis') ? 'active' : ''; ?>">
                <i class="fa-solid fa-store"></i> Kuruyemiş Pazarı Ayarı
            </a>
        </li>
        <?php endif; ?>
    </ul>
    <div style="padding: 2rem;">
        <a href="logout.php" class="btn btn-primary" style="display: block; text-align: center; background: #e74c3c;">
            <i class="fa-solid fa-right-from-bracket"></i> Çıkış Yap
        </a>
    </div>
</div>
