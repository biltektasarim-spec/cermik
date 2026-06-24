<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Sadece Süper Admin erişebilir
if ($_SESSION['admin_role'] !== 'SUPER_ADMIN' || ($_SESSION['admin_district_id'] ?? 0) != 0) {
    die("Bu sayfaya erişim yetkiniz yok. Sadece Süper Admin bildirim gönderebilir.");
}

$settings = get_settings($pdo, 0);
$fcm_key = $settings['firebase_fcm_server_key'] ?? '';
$fcm_topic = $settings['firebase_fcm_topic'] ?? 'all_users';

function get_fcm_v1_access_token($json_path) {
    if (!file_exists($json_path)) return ['token' => false, 'error' => 'Dosya yok'];
    $key_info = json_decode(file_get_contents($json_path), true);
    if (!isset($key_info['client_email']) || !isset($key_info['private_key'])) 
        return ['token' => false, 'error' => 'client_email veya private_key eksik'];

    $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);

    // Sunucu saati Google ile senkronize olmayabilir.
    // oauth2.googleapis.com'a GET isteği yapıp Date header'dan gerçek zamanı al
    $real_now = time();
    $tc = curl_init('https://oauth2.googleapis.com/.well-known/openid-configuration');
    curl_setopt_array($tc, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,   // Header + body
        CURLOPT_HTTPGET        => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 8,
    ]);
    $tc_resp = curl_exec($tc);
    curl_close($tc);
    if ($tc_resp && preg_match('/Date:\s*(.+)/i', $tc_resp, $m)) {
        $gt = strtotime(trim($m[1]));
        if ($gt > 0) $real_now = $gt;
    }

    $payload = json_encode([
        'iss' => $key_info['client_email'],
        'scope' => 'https://www.googleapis.com/auth/cloud-platform https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => $real_now + 3300,  // 55 dk (60 dk sınırından güvenli tarafta)
        'iat' => $real_now - 60     // 60 sn geriden başla (saat farkı tamponu)
    ]);

    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

    $signature = '';
    $sign_result = openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $key_info['private_key'], OPENSSL_ALGO_SHA256);
    if (!$sign_result) {
        return ['token' => false, 'error' => 'openssl_sign BAŞARISIZ: ' . openssl_error_string()];
    }
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curl_error) {
        return ['token' => false, 'error' => "cURL hatası: $curl_error"];
    }

    $token_info = json_decode($response, true);
    if (empty($token_info['access_token'])) {
        return ['token' => false, 'error' => "OAuth2 yanıtı (HTTP $http_code): $response"];
    }
    return ['token' => $token_info['access_token'], 'error' => ''];
}

function resizeImage($source_path, $target_path, $max_w = 1024, $max_h = 512) {
    list($orig_w, $orig_h, $type) = getimagesize($source_path);
    
    $aspect = $orig_w / $orig_h;
    $target_aspect = $max_w / $max_h;
    
    if ($orig_w <= $max_w && $orig_h <= $max_h) {
        // Small enough, just copy if different paths
        if ($source_path !== $target_path) copy($source_path, $target_path);
        return true;
    }
    
    // Scale to fit
    if ($aspect > $target_aspect) {
        $new_w = $max_w;
        $new_h = floor($max_w / $aspect);
    } else {
        $new_h = $max_h;
        $new_w = floor($max_h * $aspect);
    }
    
    switch ($type) {
        case IMAGETYPE_JPEG: $src_img = imagecreatefromjpeg($source_path); break;
        case IMAGETYPE_PNG:  $src_img = imagecreatefrompng($source_path);  break;
        case IMAGETYPE_GIF:  $src_img = imagecreatefromgif($source_path);  break;
        case IMAGETYPE_WEBP: $src_img = imagecreatefromwebp($source_path); break;
        default: return false;
    }
    
    if (!$src_img) return false;
    
    $dst_img = imagecreatetruecolor($new_w, $new_h);
    
    // Preserve transparency for PNG/WEBP
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_WEBP) {
        imagealphablending($dst_img, false);
        imagesavealpha($dst_img, true);
    }
    
    imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_w, $new_h, $orig_w, $orig_h);
    
    switch ($type) {
        case IMAGETYPE_JPEG: imagejpeg($dst_img, $target_path, 85); break;
        case IMAGETYPE_PNG:  imagepng($dst_img, $target_path); break;
        case IMAGETYPE_GIF:  imagegif($dst_img, $target_path); break;
        case IMAGETYPE_WEBP: imagewebp($dst_img, $target_path, 85); break;
    }
    
    imagedestroy($src_img);
    imagedestroy($dst_img);
    return true;
}

$message = "";
$status = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_push'])) {
    $title = trim($_POST['title']);
    $body = trim($_POST['body']);
    $image_url = trim($_POST['image_url']);
    $click_link = trim($_POST['click_link']);

    // --- Dosya Yükleme İşlemi ---
    if (!empty($_FILES['image_file']['name'])) {
        $upload_dir = '../uploads/notifications/';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0755, true);

        $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        if (in_array($ext, $allowed)) {
            $new_name = 'notif_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $new_name)) {
                // Görseli bildirime uygun boyuta (Max 1024x512) getir
                resizeImage($upload_dir . $new_name, $upload_dir . $new_name, 1024, 512);
                
                // Protokolü (http/https) ve hostu otomatik belirle
                $protocol = $is_https ? 'https://' : 'http://';
                $host = $_SERVER['HTTP_HOST'];
                // Sunucu kök klasörüne göre dinamik yol (REHBER veya CUMA veya / için otomatik)
                $doc_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
                $abs_upload = realpath($upload_dir . $new_name);
                $relative_path = str_replace($doc_root, '', $abs_upload);
                $relative_path = str_replace('\\', '/', $relative_path); // Windows uyumu
                $image_url = $protocol . $host . $relative_path;
            }
        }
    }

    // Önce uploads/ klasörüne bak (canlı hosting open_basedir uyumlu - göreceli yol)
    $backend_json_path = __DIR__ . '/../uploads/firebase-adminsdk.json';
    // Bulamazsa eski konuma bak (localhost / yerel test için)
    if (!file_exists($backend_json_path)) {
        $backend_json_path = __DIR__ . '/includes/firebase-adminsdk.json';
    }
    $access_token = false;
    $project_id = '';
    
    if (file_exists($backend_json_path)) {
        $key_info = @json_decode(file_get_contents($backend_json_path), true);
        $project_id = $key_info['project_id'] ?? '';
        $token_result = get_fcm_v1_access_token($backend_json_path);
        $access_token = $token_result['token'];
        $token_error  = $token_result['error'];
    }

    if (!$access_token || empty($project_id)) {
        // Detaylı hata tespiti
        $debug_info = $token_error ?? '';
        if (!file_exists($backend_json_path)) {
            $debug_info = "JSON dosyası bulunamadı: $backend_json_path";
        } elseif (empty($project_id)) {
            $debug_info = "project_id okunamadı";
        }
        $message = "HATA: Firebase Service Account JSON yüklenmemiş veya başarısız. [$debug_info]";
        $status = "error";
    } elseif (empty($title) || empty($body)) {
        $message = "HATA: Başlık ve mesaj boş olamaz.";
        $status = "error";
    } else {
        // FCM Gönderim Mantığı (HTTP v1 API)
        $url = 'https://fcm.googleapis.com/v1/projects/' . $project_id . '/messages:send';
        
        $message_payload = [
            'message' => [
                'topic' => $fcm_topic,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'android' => [
                    'notification' => [
                        'channel_id' => 'high_importance_channel',
                        'sound' => 'default'
                    ]
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default'
                        ]
                    ]
                ],
                'data' => [
                    'id' => (string)time(),
                    'status' => 'done',
                    'title' => $title,
                    'body' => $body,
                    'type' => 'general',
                    'link' => $click_link
                ]
            ]
        ];
        
        if (!empty($image_url)) {
            $message_payload['message']['notification']['image'] = $image_url;
            $message_payload['message']['data']['image'] = $image_url;
        }

        $headers = [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message_payload));
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $message = "Bildirim başarıyla gönderildi!";
            $status = "success";
            
            // Log kaydı
            try { $pdo->exec("ALTER TABLE communication_logs MODIFY COLUMN type VARCHAR(20) NOT NULL"); } catch (Exception $e) {}
            $stmt = $pdo->prepare("INSERT INTO communication_logs (type, recipient, subject, message, status) VALUES ('FCM', ?, ?, ?, 'Success')");
            $stmt->execute(['/topics/' . $fcm_topic, $title, $body]);
        } else {
            $message = "Gönderim hatası (HTTP $httpCode): " . $result;
            $status = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bildirim Gönder (Push) - Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .preview-card {
            background: #f8f9fb;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 20px;
            max-width: 350px;
            margin: 20px auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .preview-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 0.8rem;
            color: #666;
        }
        .preview-content {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .preview-title {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 5px;
        }
        .preview-body {
            font-size: 0.9rem;
            color: #333;
            line-height: 1.4;
        }
        .preview-img {
            width: 100%;
            height: 150px;
            object-fit: contain;
            object-position: center;
            border-radius: 8px;
            margin-top: 10px;
            background: #eee;
            display: none;
        }
        .preview-img.show {
            display: block;
        }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1><i class="fa-solid fa-bell"></i> Push Bildirimi Gönder</h1>
                <p style="color: var(--text-muted);">Tüm uygulama kullanıcılarına anlık mobil bildirim gönderin.</p>
            </div>
        </header>

        <main class="page-content">
            <?php if ($message): ?>
                <div style="background: <?php echo $status == 'success' ? '#d4edda' : '#fee2e2'; ?>; 
                            color: <?php echo $status == 'success' ? '#155724' : '#7f1d1d'; ?>; 
                            padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid <?php echo $status == 'success' ? '#c3e6cb' : '#fca5a5'; ?>;">
                    <i class="fa-solid <?php echo $status == 'success' ? 'fa-check-circle' : 'fa-circle-exclamation'; ?>"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 400px; gap: 2rem; align-items: start;">
                <form method="POST" class="card" enctype="multipart/form-data" id="fcmForm">
                    <h3>Bildirim Detayları</h3>
                    <div style="margin-top: 1rem;">
                        <label>Bildirim Başlığı</label>
                        <input type="text" name="title" id="notif_title" required class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 15px;" placeholder="Örn: Yeni Etkinlik Duyurusu!">
                        
                        <label>Bildirim Mesajı</label>
                        <textarea name="body" id="notif_body" required class="btn" style="width: 100%; border: 1px solid #ddd; min-height: 100px; padding: 10px; margin-bottom: 15px;" placeholder="Kullanıcıların ekranında görünecek kısa mesaj..."></textarea>
                        
                        <label>Bildirim Görseli (Dosya Seçin)</label>
                        <input type="file" name="image_file" id="notif_file" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 15px;" accept="image/*">
                        
                        <div style="text-align: center; color: #888; font-size: 0.8rem; margin: 10px 0;">— VEYA —</div>

                        <label>Görsel URL (İnternet Adresi)</label>
                        <input type="url" name="image_url" id="notif_image" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 15px;" placeholder="https://site.com/resim.jpg">
                        
                        <label>Tıklanınca Açılacak Link (Opsiyonel)</label>
                        <input type="url" name="click_link" id="notif_link" class="btn" style="width: 100%; border: 1px solid #ddd; padding: 10px; margin-bottom: 15px;" placeholder="https://rotarehber.com/etkinlik.php?id=5">

                        <p style="font-size: 0.8rem; color: #888;">Hedef Grup: <strong>/topics/<?php echo $fcm_topic; ?></strong></p>
                    </div>

                    <button type="submit" name="send_push" class="btn btn-primary" style="width: 100%; margin-top: 1rem; background: #3498db; border-color: #2980b9;">
                        <i class="fa-solid fa-paper-plane"></i> Bildirimi Hemen Gönder
                    </button>
                </form>

                <div class="card" style="text-align: center;">
                    <h3><i class="fa-solid fa-mobile-screen-button"></i> Mobil Önizleme</h3>
                    <div class="preview-card">
                        <div class="preview-header">
                            <i class="fa-solid fa-location-dot"></i> <span>ROTAREHBER • şimdi</span>
                        </div>
                        <div class="preview-content">
                            <div class="preview-title" id="p_title">Bildirim Başlığı</div>
                            <div class="preview-body" id="p_body">Mesaj içeriği burada görünecek...</div>
                            <img src="" class="preview-img" id="p_img">
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const titleInput = document.getElementById('notif_title');
        const bodyInput = document.getElementById('notif_body');
        const imgInput = document.getElementById('notif_image');
        const fileInput = document.getElementById('notif_file');

        const pTitle = document.getElementById('p_title');
        const pBody = document.getElementById('p_body');
        const pImg = document.getElementById('p_img');

        titleInput.addEventListener('input', () => pTitle.innerText = titleInput.value || 'Bildirim Başlığı');
        bodyInput.addEventListener('input', () => pBody.innerText = bodyInput.value || 'Mesaj içeriği burada görünecek...');
        
        imgInput.addEventListener('input', () => updatePreviewImage());
        fileInput.addEventListener('change', () => {
            if (fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    pImg.src = e.target.result;
                    pImg.classList.add('show');
                };
                reader.readAsDataURL(fileInput.files[0]);
            } else {
                updatePreviewImage();
            }
        });

        function updatePreviewImage() {
            if (imgInput.value) {
                pImg.src = imgInput.value;
                pImg.classList.add('show');
            } else if (!fileInput.files.length) {
                pImg.classList.remove('show');
            }
        }
    </script>
</body>
</html>
