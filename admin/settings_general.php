<?php
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        foreach ($_POST as $key => $value) {
            if ($key == 'new_admin_password') {
                if (!empty($value) && $is_super_admin && $admin_district_id === 0) {
                    $hashed = password_hash($value, PASSWORD_BCRYPT, ['cost' => 12]);
                    $stmt = $pdo->prepare("INSERT INTO settings (name, value, district_id) VALUES ('admin_password_hash', ?, 0) ON DUPLICATE KEY UPDATE value = ?");
                    $stmt->execute([$hashed, $hashed]);
                }
                continue;
            }
            // Add district_id to the unique key handling
            $stmt = $pdo->prepare("INSERT INTO settings (name, value, district_id) 
                                 VALUES (?, ?, ?) 
                                 ON DUPLICATE KEY UPDATE value = ?");
            $stmt->execute([$key, $value, $admin_district_id, $value]);
        }
            // Logo Yükleme İşlemi (Döngü dışında)
            if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
                $target_dir = "../uploads/logo/";
                if (!is_dir($target_dir)) @mkdir($target_dir, 0755, true);
                
                $file_ext = pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
                $new_filename = 'logo_' . $admin_district_id . '_' . time() . '.' . $file_ext;
                
                if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $target_dir . $new_filename)) {
                    $img_path = 'uploads/logo/' . $new_filename;
                    $stmt_logo = $pdo->prepare("INSERT INTO settings (name, value, district_id) VALUES ('site_logo', ?, ?) ON DUPLICATE KEY UPDATE value = ?");
                    $stmt_logo->execute([$img_path, $admin_district_id, $img_path]);
                }
            }

        $pdo->commit();
        $message = "Ayarlar başarıyla güncellendi.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "HATA: " . $e->getMessage();
    }
}

// Load global settings, then overlay district-specific ones
$settings = get_settings($pdo, $admin_district_id);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genel Ayarlar - Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>Genel Ayarlar</h1>
                <p style="color: var(--text-muted);">Site genel bilgilerini ve politikaları yönetin.</p>
            </div>
        </header>

        <main class="page-content">
            <?php if ($message): ?>
                <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #c3e6cb;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="card">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <h3>Site Bilgileri</h3>
                        <div style="margin-top: 1rem;">
                            <label>Site Adı</label>
                            <input type="text" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            
                            <label>İlçe Logosu (Üst menü logo.png)</label>
                            <?php if(!empty($settings['site_logo'])): ?>
                                <div style="margin-bottom:5px;"><img src="../<?php echo htmlspecialchars($settings['site_logo']); ?>" style="height:40px; border-radius:5px;"></div>
                            <?php endif; ?>
                            <input type="file" name="site_logo" accept="image/*" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">

                            <label>E-posta</label>
                            <input type="email" name="site_email" value="<?php echo htmlspecialchars($settings['site_email'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            
                            <label>Telefon</label>
                            <input type="text" name="site_phone" value="<?php echo htmlspecialchars($settings['site_phone'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            
                            <label>Adres</label>
                            <textarea name="site_address" class="btn" style="width: 100%; border: 1px solid #ddd; min-height: 80px; padding: 10px; margin-bottom: 10px;"><?php echo htmlspecialchars($settings['site_address'] ?? ''); ?></textarea>
                            
                            <label>Telif Yazısı (Footer)</label>
                            <input type="text" name="copyright_text" value="<?php echo htmlspecialchars($settings['copyright_text'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">

                            <label>Ana Sayfa Slogan (TR)</label>
                            <input type="text" name="explore_desc" value="<?php echo htmlspecialchars($settings['explore_desc'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;" placeholder="Örn: Tarihin Sıcaklığı, Doğanın Saklı Yüzü">

                            <label>Ana Sayfa Slogan (EN)</label>
                            <input type="text" name="explore_desc_en" value="<?php echo htmlspecialchars($settings['explore_desc_en'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;" placeholder="Example: Warmth of History, Hidden Face of Nature">
                        </div>
                    </div>

                    <div>
                        <h3>Sosyal Medya Linkleri</h3>
                        <div style="margin-top: 1rem;">
                            <label><i class="fa-brands fa-facebook"></i> Facebook</label>
                            <input type="text" name="facebook_link" value="<?php echo htmlspecialchars($settings['facebook_link'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            
                            <label><i class="fa-brands fa-instagram"></i> Instagram</label>
                            <input type="text" name="instagram_link" value="<?php echo htmlspecialchars($settings['instagram_link'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            
                            <label><i class="fa-brands fa-youtube"></i> YouTube</label>
                            <input type="text" name="youtube_link" value="<?php echo htmlspecialchars($settings['youtube_link'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            
                            <label><i class="fa-brands fa-twitter"></i> Twitter / X</label>
                            <input type="text" name="twitter_link" value="<?php echo htmlspecialchars($settings['twitter_link'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                        </div>
                    </div>
                </div>

                <?php if ($is_super_admin || $admin_district_id > 0): ?>
                <div style="margin-top: 2rem;">
                    <h3>Ana Sayfa Banner Ayarları</h3>
                    <p style="font-size: 0.85rem; color: #666; margin-bottom: 1rem;">Ana sayfadaki büyük tanıtım kartını buradan güncelleyebilirsiniz.</p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <div>
                            <label>Banner Başlık (TR)</label>
                            <input type="text" name="hero_title_tr" value="<?php echo htmlspecialchars($settings['hero_title_tr'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            <label>Banner Açıklama (TR)</label>
                            <textarea name="hero_desc_tr" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; height: 80px; margin-bottom: 10px;"><?php echo htmlspecialchars($settings['hero_desc_tr'] ?? ''); ?></textarea>
                        </div>
                        <div>
                            <label>Banner Başlık (EN)</label>
                            <input type="text" name="hero_title_en" value="<?php echo htmlspecialchars($settings['hero_title_en'] ?? ''); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            <label>Banner Açıklama (EN)</label>
                            <textarea name="hero_desc_en" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; height: 80px; margin-bottom: 10px;"><?php echo htmlspecialchars($settings['hero_desc_en'] ?? ''); ?></textarea>
                            <label>Banner Sıralama (0 en üst)</label>
                            <input type="number" name="hero_sort_order" value="<?php echo intval($settings['hero_sort_order'] ?? 999); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div style="margin-top: 2rem;">
                    <h3>Menü Adları (İlçe Bazlı)</h3>
                    <p style="font-size: 0.85rem; color: #666; margin-bottom: 1rem;">Bu alanlardan ilçenizin ana sayfasındaki kategori isimlerini değiştirebilirsiniz.</p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <div>
                            <label>Tarihi Mekanlar (TR)</label>
                            <input type="text" name="menu_historical_tr" value="<?php echo htmlspecialchars($settings['menu_historical_tr'] ?? 'Tarihi Mekanlar'); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            <label>Doğa ve Parklar (TR)</label>
                            <input type="text" name="menu_nature_tr" value="<?php echo htmlspecialchars($settings['menu_nature_tr'] ?? 'Doğa ve Parklar'); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            <label>Park ve Bahçeler (TR)</label>
                            <input type="text" name="menu_parks_tr" value="<?php echo htmlspecialchars($settings['menu_parks_tr'] ?? 'Park ve Bahçeler'); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            <label>Kuruyemiş Pazarı (TR)</label>
                            <input type="text" name="menu_kuruyemis_tr" value="<?php echo htmlspecialchars($settings['menu_kuruyemis_tr'] ?? 'Kuruyemiş Pazarı'); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            <label>Lokantalar (TR)</label>
                            <input type="text" name="menu_restaurants_tr" value="<?php echo htmlspecialchars($settings['menu_restaurants_tr'] ?? 'Lokantalar'); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            <label>Oteller (TR)</label>
                            <input type="text" name="menu_hotels_tr" value="<?php echo htmlspecialchars($settings['menu_hotels_tr'] ?? 'Otel ve Pansiyon'); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                        </div>
                        <div>
                            <label>Tarihi Mekanlar (EN)</label>
                            <input type="text" name="menu_historical_en" value="<?php echo htmlspecialchars($settings['menu_historical_en'] ?? 'Historical Places'); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            <label>Doğa ve Parklar (EN)</label>
                            <input type="text" name="menu_nature_en" value="<?php echo htmlspecialchars($settings['menu_nature_en'] ?? 'Nature and Parks'); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            <label>Park ve Bahçeler (EN)</label>
                            <input type="text" name="menu_parks_en" value="<?php echo htmlspecialchars($settings['menu_parks_en'] ?? 'Parks and Gardens'); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            <label>Kuruyemiş Pazarı (EN)</label>
                            <input type="text" name="menu_kuruyemis_en" value="<?php echo htmlspecialchars($settings['menu_kuruyemis_en'] ?? 'Nuts Market'); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            <label>Lokantalar (EN)</label>
                            <input type="text" name="menu_restaurants_en" value="<?php echo htmlspecialchars($settings['menu_restaurants_en'] ?? 'Restaurants'); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                            <label>Oteller (EN)</label>
                            <input type="text" name="menu_hotels_en" value="<?php echo htmlspecialchars($settings['menu_hotels_en'] ?? 'Hotels'); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                        </div>
                    </div>
                </div>

                <?php if ($is_super_admin && $admin_district_id === 0): ?>
                <div style="margin-top: 2rem;">
                    <h3>SMS API Ayarları</h3>
                    <div style="margin-top: 1rem; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 2rem;">
                        <div>
                            <label>SMS API ID (VatanSMS)</label>
                            <input type="text" name="sms_api_id" value="<?php echo htmlspecialchars($settings['sms_api_id'] ?? '7073c30918869aee144ddca9'); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                        </div>
                        <div>
                            <label>SMS API KEY</label>
                            <input type="password" name="sms_api_key" value="<?php echo htmlspecialchars($settings['sms_api_key'] ?? 'bb37df2be980e603326bce12'); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                        </div>
                        <div>
                            <label>SMS Gönderen Başlık (Sender ID)</label>
                            <input type="text" name="sms_title" value="<?php echo htmlspecialchars($settings['sms_title'] ?? 'REHBER'); ?>" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
                        </div>
                    </div>
                    <div style="margin-top: 1rem;">
                        <label>OTP SMS Özelliği</label>
                        <select name="otp_enabled" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px;">
                            <option value="0" <?php echo ($settings['otp_enabled'] ?? '0') == '0' ? 'selected' : ''; ?>>Pasif</option>
                            <option value="1" <?php echo ($settings['otp_enabled'] ?? '0') == '1' ? 'selected' : ''; ?>>Aktif (Telefon ile Doğrulama)</option>
                        </select>
                        <p style="font-size: 0.8rem; color: #666; margin-top: 5px;">Aktif edilirse, admin girişinde telefona gelen kod istenecektir.</p>
                    </div>
                </div>
                <?php endif; ?>

                <div style="margin-top: 2rem;">
                    <h3>Hukuki Metinler</h3>
                    <p style="font-size: 0.85rem; color: #666; margin-bottom: 1rem;">Profil sayfasında görünen KVKK ve Çerez Politikası metinlerini buradan düzenleyebilirsiniz.</p>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                        <div>
                            <label>KVKK Aydınlatma Metni</label>
                            <textarea name="kvkk_text" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; height: 150px; margin-bottom: 10px;"><?php echo htmlspecialchars($settings['kvkk_text'] ?? ''); ?></textarea>
                        </div>
                        <div>
                            <label>Çerez Politikası</label>
                            <textarea name="cookie_policy" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; height: 150px; margin-bottom: 10px;"><?php echo htmlspecialchars($settings['cookie_policy'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Ayarları Kaydet</button>
            </form>
        </main>
    </div>

</body>
</html>
