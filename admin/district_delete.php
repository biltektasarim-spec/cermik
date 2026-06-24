<?php
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

if ($_SESSION['admin_role'] !== 'SUPER_ADMIN') {
    die("Yetkisiz erişim");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Güvenlik: 0 (Genel), 3 (Çermik), 5 (Çüngüş) ana şablon ilçeleridir, sistemden kazara silinmesi engellenmiştir.
    if ($id <= 0 || $id == 3 || $id == 5) {
        die("Güvenlik Kilidi: Çermik, Çüngüş veya Genel sistem ayarları silinemez.");
    }
    
    // İlçe bilgisini al
    $stmt = $pdo->prepare("SELECT slug FROM districts WHERE id = ?");
    $stmt->execute([$id]);
    $slug = $stmt->fetchColumn();
    
    if (!$slug) {
        die("İlçe bulunamadı.");
    }

    try {
        $pdo->beginTransaction();

        // 1. İlişkili verileri tablolarından sil (Places, Businesses, Ayarlar vb.)
        // Eğer tablo yoksa hata vermemesi için teker teker try-catch içine alıyoruz.
        $tables_to_clean = [
            'places', 'businesses', 'events', 'settings', 'district_admins', 'custom_menus', 'cek_gonder_forms'
        ];
        
        foreach ($tables_to_clean as $table) {
            try {
                $pdo->prepare("DELETE FROM {$table} WHERE district_id = ?")->execute([$id]);
            } catch (Exception $e) {
                // Tablo yoksa umursama, sonrakine geç
            }
        }
        
        // 2. İlçeyi sil
        $pdo->prepare("DELETE FROM districts WHERE id = ?")->execute([$id]);
        
        $pdo->commit();

        // 3. Dosya klasörünü sil (Eğer varsa)
        $target_dir = dirname(__DIR__) . '/' . $slug;
        if (is_dir($target_dir) && $slug !== 'cermik' && $slug !== 'cungus') {
            // Basit bir recursive delete fonksiyonu
            function deleteDir($dirPath) {
                if (!is_dir($dirPath)) {
                    return;
                }
                $files = glob($dirPath . '/*', GLOB_MARK);
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        deleteDir($file);
                    } else {
                        unlink($file);
                    }
                }
                rmdir($dirPath);
            }
            deleteDir($target_dir);
        }

        header("Location: districts_manage.php?msg=deleted");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Silme hatası: " . $e->getMessage());
    }
}
?>
