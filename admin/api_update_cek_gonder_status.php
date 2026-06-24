<?php
// VER:20260504-TIMESYNC-FIX
error_reporting(0);
ini_set('display_errors', 0);
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/../includes/SmsService.php';

ob_clean(); // Prevent any output from config.php or warnings that might corrupt JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';
$allowed_statuses = ['Beklemede', 'İşleme Alındı', 'Tamamlandı'];

if ($id <= 0 || !in_array($status, $allowed_statuses)) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz veri']);
    exit;
}

try {
    // Veritabanı tablolarında fcm_token sütunları var mı kontrol et (Canlı sunucudaki SQL çökmesini engellemek için)
    $hasFcmInForms = false;
    $hasFcmInUsers = false;
    
    try {
        $col1 = $pdo->query("SHOW COLUMNS FROM cek_gonder_forms LIKE 'fcm_token'");
        $hasFcmInForms = $col1->rowCount() > 0;
        
        $col2 = $pdo->query("SHOW COLUMNS FROM users LIKE 'fcm_token'");
        $hasFcmInUsers = $col2->rowCount() > 0;
    } catch (Exception $e) {}

    // Dinamik SQL oluştur
    $selectFields = "c.tel_no, c.district_id, c.ad_soyad, c.basvuru_turu, c.user_id, d.name as district_name";
    if ($hasFcmInForms) {
        $selectFields .= ", c.fcm_token as form_fcm";
    }
    if ($hasFcmInUsers) {
        $selectFields .= ", u.fcm_token as user_fcm";
    }

    $sql = "
        SELECT $selectFields 
        FROM cek_gonder_forms c 
        LEFT JOIN districts d ON c.district_id = d.id 
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.id = ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $form = $stmt->fetch();

    if (!$form) {
        echo json_encode(['status' => 'error', 'message' => 'Başvuru bulunamadı']);
        exit;
    }
    
    // Doğru fcm tokenini güvenle eşle (Eğer veritabanında sütun yoksa boş döner)
    $form['fcm_token'] = '';
    if (!empty($form['form_fcm'])) {
        $form['fcm_token'] = $form['form_fcm'];
    } elseif (!empty($form['user_fcm'])) {
        $form['fcm_token'] = $form['user_fcm'];
    }

    // $is_super_admin and $admin_district_id comes from auth_guard.php
    if (!$is_super_admin && $admin_district_id != $form['district_id']) {
         echo json_encode(['status' => 'error', 'message' => 'Yetkisiz işlem']);
         exit;
    }
    
    $valid_sms_types = ['Şikayet', 'İstek', 'Sikayet', 'Istek', 'Complaint', 'Request', 'Şikayet', 'İstek'];
    $form_type = trim($form['basvuru_turu'] ?? '');
    $form_type_lower = mb_strtolower($form_type, 'UTF-8');

    // Durum takibi yapılabilecek türleri kontrol et
    $allowed_process_types = ['şikayet', 'istek', 'sikayet', 'istek', 'complaint', 'request'];
    
    if (!in_array($form_type, $valid_sms_types) && !in_array($form_type_lower, $allowed_process_types)) {
        echo json_encode(['status' => 'error', 'message' => "Bu başvuru türü durum takibine dahil değildir: " . $form_type]);
        exit;
    }

    // Update status
    $pdo->prepare("UPDATE cek_gonder_forms SET process_status = ? WHERE id = ?")->execute([$status, $id]);

    // Check if SMS is globally enabled
    $sms_enabled = false;
    $stmt2 = $pdo->prepare("SELECT value FROM settings WHERE name = 'cek_gonder_sms_enabled' AND district_id = 0");
    $stmt2->execute();
    if ($row = $stmt2->fetch()) {
        $sms_enabled = $row['value'] == '1';
    }

    $sms_sent = false;
    if ($sms_enabled && !empty($form['tel_no'])) {
        function format_phone_local_cek($phone) {
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($phone) == 10 && $phone[0] == '5') return '0' . $phone;
            if (strlen($phone) == 11 && $phone[0] == '0') return $phone;
            if (strlen($phone) > 11 && substr($phone, 0, 2) == '90') return '0' . substr($phone, 2);
            return $phone;
        }

        function clean_tr_chars($text) {
            $tr = ['ş','Ş','ı','İ','ğ','Ğ','ü','Ü','ö','Ö','ç','Ç'];
            $eng = ['s','S','i','I','g','G','u','U','o','O','c','C'];
            return str_replace($tr, $eng, $text);
        }

        $phone = format_phone_local_cek($form['tel_no']);
        if ($phone) {
            // Get SMS Config
            $s_stmt = $pdo->prepare("SELECT name, value FROM settings WHERE name IN ('sms_api_id', 'sms_api_key', 'sms_title')");
            $s_stmt->execute();
            $s_conf = [];
            while($r = $s_stmt->fetch()) $s_conf[$r['name']] = $r['value'];

            $api_id = $s_conf['sms_api_id'] ?? '7073c30918869aee144ddca9';
            $api_key = $s_conf['sms_api_key'] ?? 'bb37df2be980e603326bce12';
            $sms_title = $s_conf['sms_title'] ?: 'ROTAREHBER';

            $dyn_name = clean_tr_chars($form['ad_soyad'] ?? '');
            $dyn_dist = clean_tr_chars($form['district_name'] ?? '');
            $dyn_type = clean_tr_chars($form['basvuru_turu'] === 'Şikayet' ? 'sikayet' : 'istek'); 

            if ($status === 'İşleme Alındı') {
                $msg = "Sn. {$dyn_name} {$dyn_dist} Belediyesine iletmis oldugunuz {$dyn_type} talebiniz isleme alinmistir.";
            } else {
                $msg = "Sn. {$dyn_name} {$dyn_dist} Belediyesine iletmis oldugunuz {$dyn_type} talebiniz icin gerekli islem yapilmistir.";
            }

            $smsService = new SmsService($api_id, $api_key, $sms_title);
            try {
                $smsService->sendSms($phone, $msg);
                $sms_sent = true;
            } catch (Exception $e) {}
        }
    }

    // --- PUSH NOTIFICATION (FCM) GÖNDERİMİ ---
    $push_sent = false;
    $push_error = '';
    
    // --- PUSH BİLDİRİMİ GÖNDER (FCM HTTP v1) TEMİZ BLOK ---
    $push_sent = false;
    $push_error = '';

    if (!empty($form['fcm_token'])) {
        $json_path = __DIR__ . '/../uploads/firebase-adminsdk.json';
        if (!file_exists($json_path)) {
            $json_path = __DIR__ . '/includes/firebase-adminsdk.json';
        }

        if (file_exists($json_path)) {
            $key_info = @json_decode(file_get_contents($json_path), true);
            if ($key_info && isset($key_info['client_email']) && isset($key_info['private_key'])) {
                
                // 1. Token Alımı - SUNUCU SAATİ KRİTİK HATA VAR (33,952 sn fark)
                // Bu yüzden MUTLAKA Google'ın gerçek saati kullanılıyor
                $real_now = null;
                
                // gstatic.com/generate_204 - kanıtlanmış çalışan endpoint
                $time_sources = [
                    'https://www.gstatic.com/generate_204',
                    'https://oauth2.googleapis.com/.well-known/openid-configuration',
                    'https://accounts.google.com/',
                ];
                foreach ($time_sources as $ts_url) {
                    $tc = curl_init($ts_url);
                    curl_setopt_array($tc, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HEADER         => true,
                        CURLOPT_NOBODY         => true,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_TIMEOUT        => 8,
                    ]);
                    $tc_resp = curl_exec($tc);
                    $tc_err = curl_error($tc);
                    curl_close($tc);
                    if ($tc_resp && !$tc_err && preg_match('/Date:\s*(.+?)[\r\n]/i', $tc_resp, $m)) {
                        $gt = strtotime(trim($m[1]));
                        if ($gt > 1700000000) { // 2023+ geçerli timestamp
                            $real_now = $gt;
                            break;
                        }
                    }
                }
                
                // Hiçbir kaynaktan saat alınamazsa son çare olarak server time kullan
                if (!$real_now) {
                    $real_now = time();
                }

                $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
                $payload = json_encode([
                    'iss'   => $key_info['client_email'],
                    'scope' => 'https://www.googleapis.com/auth/cloud-platform https://www.googleapis.com/auth/firebase.messaging',
                    'aud'   => 'https://oauth2.googleapis.com/token',
                    'exp'   => $real_now + 3300,
                    'iat'   => $real_now - 60,
                ]);

                $b64h = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
                $b64p = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
                $signature = '';
                openssl_sign($b64h . "." . $b64p, $signature, $key_info['private_key'], OPENSSL_ALGO_SHA256);
                $b64s = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
                $jwt = $b64h . "." . $b64p . "." . $b64s;

                $ch = curl_init('https://oauth2.googleapis.com/token');
                curl_setopt_array($ch, [
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => http_build_query(['grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $jwt]),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_TIMEOUT        => 15,
                ]);
                $token_response = curl_exec($ch);
                curl_close($ch);
                $token_data = json_decode($token_response, true);
                $access_token = $token_data['access_token'] ?? '';

                // 2. FCM'e Gönderim
                if ($access_token) {
                    $project_id = trim($key_info['project_id']);
                    $fcm_url = "https://fcm.googleapis.com/v1/projects/" . $project_id . "/messages:send";

                    $dyn_name = mb_convert_encoding($form['ad_soyad'] ?? '', 'UTF-8', 'auto');
                    $dyn_dist = mb_convert_encoding($form['district_name'] ?? '', 'UTF-8', 'auto');
                    $type_tr = mb_convert_encoding($form_type_lower, 'UTF-8', 'auto');
                    if (strpos($type_tr, 'sikayet') !== false || strpos($type_tr, 'şikayet') !== false) $type_tr = 'şikayet';
                    else if (strpos($type_tr, 'istek') !== false) $type_tr = 'istek';

                    $pushTitle = "Başvuru Güncellemesi";
                    $pushBody = ($status === 'İşleme Alındı') 
                        ? "Sn. {$dyn_name}, {$dyn_dist} Belediyesine iletmiş olduğunuz {$type_tr} talebiniz işleme alınmıştır."
                        : "Sn. {$dyn_name}, {$dyn_dist} Belediyesine iletmiş olduğunuz {$type_tr} talebiniz tamamlanmıştır.";

                    $fcm_payload = [
                        'message' => [
                            'token' => trim($form['fcm_token']),
                            'notification' => [
                                'title' => $pushTitle,
                                'body' => $pushBody
                            ],
                            'android' => ['notification' => ['channel_id' => 'high_importance_channel', 'sound' => 'default']],
                            'apns' => ['payload' => ['aps' => ['sound' => 'default']]],
                            'data' => [
                                'id' => (string)time(),
                                'status' => strtolower($status),
                                'title' => $pushTitle,
                                'body' => $pushBody,
                                'type' => 'cek_gonder'
                            ]
                        ]
                    ];
                    
                    $json_payload = json_encode($fcm_payload, JSON_UNESCAPED_UNICODE);
                    if ($json_payload === false) {
                        $push_error = "JSON Kodlama Hatası: " . json_last_error_msg();
                    } else {
                        $ch2 = curl_init($fcm_url);
                        curl_setopt_array($ch2, [
                            CURLOPT_POST           => true,
                            CURLOPT_HTTPHEADER     => [
                                "Authorization: Bearer " . $access_token,
                                "Content-Type: application/json"
                            ],
                            CURLOPT_POSTFIELDS     => $json_payload,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_TIMEOUT        => 15,
                        ]);
                        $fcm_response = curl_exec($ch2);
                        $fcm_http_code = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                        curl_close($ch2);

                        if ($fcm_http_code == 200) {
                            $push_sent = true;
                        } else {
                            $fcm_json = json_decode($fcm_response, true);
                            $push_error = "FCM Gönderim Hatası (HTTP $fcm_http_code): " . ($fcm_json['error']['message'] ?? $fcm_response);
                        }
                    }
                } else {
                    $push_error = "OAuth2 Token alınamadı: " . $token_response;
                }
            } else {
                $push_error = "JSON içeriği eksik.";
            }
        } else {
            $push_error = "JSON dosyası bulunamadı ($json_path).";
        }
    } else {
        $push_error = "FCM Token (cihaz kimliği) yok.";
    }
    // --- PUSH BİTİŞ ---

    echo json_encode([
        'status' => 'success', 
        'sms_sent' => $sms_sent,
        'push_sent' => $push_sent,
        'push_error' => $push_error,
        'has_token' => !empty($form['fcm_token'])
    ]);

} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine()]);
}
?>
