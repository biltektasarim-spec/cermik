<?php
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Sadece Süper Admin erişebilir
if ($_SESSION['admin_role'] !== 'SUPER_ADMIN' || ($_SESSION['admin_district_id'] ?? 0) != 0) {
    die("Bu sayfaya erişim yetkiniz yok. Sadece Süper Admin Firebase ayarlarını yapılandırabilir.");
}

$message = "";
$is_super_admin = true;
$admin_district_id = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        foreach ($_POST as $key => $value) {
            // Firebase ile başlayan anahtarları kaydet
            if (strpos($key, 'firebase_') === 0) {
                $stmt = $pdo->prepare("INSERT INTO settings (name, value, district_id) 
                                     VALUES (?, ?, 0) 
                                     ON DUPLICATE KEY UPDATE value = ?");
                $stmt->execute([$key, $value, $value]);
            }
        }
        
        // Android google-services.json upload is handled locally for the APK build
        // Therefore, we do not restrict or try to save it into the Flutter folder on the web server.
        // Backend firebase-adminsdk.json yükleme kontrolü (FCM v1 için)
        if (isset($_FILES['firebase_adminsdk_json']) && $_FILES['firebase_adminsdk_json']['error'] === UPLOAD_ERR_OK) {
            // __DIR__ göreceli yolu ile uploads/ klasörü (canlı hosting open_basedir uyumlu)
            $backend_dir = __DIR__ . '/../uploads/';
            if (!is_dir($backend_dir)) @mkdir($backend_dir, 0755, true);
            $backend_file = $backend_dir . 'firebase-adminsdk.json';
            
            $ext2 = strtolower(pathinfo($_FILES['firebase_adminsdk_json']['name'], PATHINFO_EXTENSION));
            if ($ext2 === 'json') {
                if (!move_uploaded_file($_FILES['firebase_adminsdk_json']['tmp_name'], $backend_file)) {
                    // Fallback: admin/includes/ klasörüne dene
                    $backend_dir2 = __DIR__ . '/includes/';
                    if (!is_dir($backend_dir2)) @mkdir($backend_dir2, 0755, true);
                    move_uploaded_file($_FILES['firebase_adminsdk_json']['tmp_name'], $backend_dir2 . 'firebase-adminsdk.json');
                }
            }
        }
        
        $pdo->commit();
        $message = "Firebase ayarları başarıyla güncellendi.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "HATA: " . $e->getMessage();
    }
}

$settings = get_settings($pdo, 0);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firebase Ayarları - Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1><i class="fa-solid fa-fire" style="color: #f39c12;"></i> Firebase Yapılandırması</h1>
                <p style="color: var(--text-muted);">Mobil uygulama ve web bildirimleri için Firebase parametrelerini yönetin.</p>
            </div>
        </header>

        <main class="page-content">
            <?php if ($message): ?>
                <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #c3e6cb;">
                    <i class="fa-solid fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="card" enctype="multipart/form-data">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <h3>Web SDK Ayarları (PWA)</h3>
                        <p style="font-size: 0.8rem; color: #666; margin-bottom: 1rem;">Firebase Console -> Project Settings -> General kısmındaki Web App yapılandırması.</p>
                        
                        <div style="margin-top: 1rem;">
                            <label>API Key</label>
                            <input type="text" name="firebase_api_key" value="<?php echo htmlspecialchars($settings['firebase_api_key'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;" placeholder="AIzaSy...">
                            
                            <label>Auth Domain</label>
                            <input type="text" name="firebase_auth_domain" value="<?php echo htmlspecialchars($settings['firebase_auth_domain'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;" placeholder="proje-id.firebaseapp.com">
                            
                            <label>Project ID</label>
                            <input type="text" name="firebase_project_id" value="<?php echo htmlspecialchars($settings['firebase_project_id'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;" placeholder="proje-id">
                            
                            <label>Storage Bucket</label>
                            <input type="text" name="firebase_storage_bucket" value="<?php echo htmlspecialchars($settings['firebase_storage_bucket'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;" placeholder="proje-id.appspot.com">
                            
                            <label>Measurement ID</label>
                            <input type="text" name="firebase_measurement_id" value="<?php echo htmlspecialchars($settings['firebase_measurement_id'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;" placeholder="G-XXXXXX">
                        </div>
                    </div>

                    <div>
                        <h3>Mesajlaşma & Bildirim (FCM)</h3>
                        <p style="font-size: 0.8rem; color: #666; margin-bottom: 1rem;">Bildirim gönderimi iğin gerekli teknik detaylar.</p>
                        
                        <div style="margin-top: 1rem;">
                            <label>Messaging Sender ID</label>
                            <input type="text" name="firebase_messaging_sender_id" value="<?php echo htmlspecialchars($settings['firebase_messaging_sender_id'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            
                            <label>App ID</label>
                            <input type="text" name="firebase_app_id" value="<?php echo htmlspecialchars($settings['firebase_app_id'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            
                            <label>FCM Topic Name</label>
                            <input type="text" name="firebase_fcm_topic" value="<?php echo htmlspecialchars($settings['firebase_fcm_topic'] ?? 'all_users'); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 25px;" placeholder="all_users">
                            
                            <h3>Backend Service Account (FCM v1)</h3>
                            <p style="font-size: 0.8rem; color: #666; margin-bottom: 1rem;">Sunucunun (PHP) doğrudan bildirim atabilmesi için Firebase'den indirdiğiniz <strong>Hizmet Hesabı Özel Anahtarı</strong> JSON dosyasını yükleyin.</p>
                            
                            <label>firebase-adminsdk.json (Service Account):</label>
                            <input type="file" name="firebase_adminsdk_json" accept="application/json" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px;">
                            
                            <?php 
                            $backend_json_path = __DIR__ . '/includes/firebase-adminsdk.json';
                            if (file_exists($backend_json_path)): ?>
                                <p style="color: green; font-size: 0.85rem; margin-top: 5px;"><i class="fa-solid fa-check"></i> firebase-adminsdk.json yüklü (Son güncelleme: <?php echo date("d.m.Y H:i", filemtime($backend_json_path)); ?>).</p>
                            <?php else: ?>
                                <p style="color: #e74c3c; font-size: 0.85rem; margin-top: 5px;"><i class="fa-solid fa-triangle-exclamation"></i> Backend JSON eksik. Lütfen Firebase Console'dan indirip yükleyin!</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 2rem;">
                    <h3>Android Uygulama Bildirim Ayarları (APK)</h3>
                    <p style="font-size: 0.8rem; color: #666; margin-bottom: 1rem;">Uygulamanın şifreli bildirimleri alabilmesi için konsoldan indirilen <strong>google-services.json</strong> dosyasını buraya yükleyin.</p>
                    
                    <label>google-services.json Dosyası:</label>
                    <input type="file" name="google_services_json" accept="application/json" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px;">
                    
                    <p style="color: green; font-size: 0.85rem; margin-top: 5px;"><i class="fa-solid fa-check"></i> (Mobil cihazlarınız için Firebase google-services.json dosyası kurulumu yerel bilgisayarınızda derleme adımında başarıyla uygulanmıştır).</p>
                </div>

                <div style="background: #fff9db; padding: 1rem; border-radius: 8px; margin-top: 1.5rem; border: 1px solid #ffe066;">
                    <p style="font-size: 0.85rem; color: #856404; margin: 0;">
                        <i class="fa-solid fa-circle-info"></i> <strong>Bilgi:</strong> Bu ayarlar mobil uygulama üzerinden anlık bildirim (Push Notification) gönderimi ve harita servisleri için kullanılmaktadır. Değiştirirken dikkatli olunuz.
                    </p>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1.5rem; background: #f39c12; border-color: #e67e22;">
                    <i class="fa-solid fa-save"></i> Yapılandırmayı Kaydet
                </button>
            </form>
        </main>
    </div>

</body>
</html>
