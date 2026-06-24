<?php
ob_start();
// header('Content-Type: application/json'); // Moved to specific actions
try {
    require_once '../config.php';
    require_once __DIR__ . '/../includes/SmsService.php';
    require_once __DIR__ . '/../includes/MailService.php';

    // Helper to send clean JSON response
    function send_json($data) {
        while (ob_get_level() > 0) ob_end_clean(); // Clear all buffers
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    function format_phone_local($phone) {
        if (empty($phone)) return '';
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) == 10 && $phone[0] == '5') {
            return '0' . $phone;
        }
        if (strlen($phone) == 11 && $phone[0] == '0') {
            return $phone;
        }
        if (strlen($phone) > 11 && substr($phone, 0, 2) == '90') {
            return '0' . substr($phone, 2);
        }
        return $phone;
    }

    // Helper to fetch SMS settings (Row-based KV model)
    function get_sms_config($pdo) {
        $keys = ['sms_api_id', 'sms_api_key', 'sms_title'];
        $config = [];
        foreach ($keys as $k) {
            $stmt = $pdo->prepare("SELECT value FROM settings WHERE name = ? LIMIT 1");
            $stmt->execute([$k]);
            $val = $stmt->fetchColumn();
            $config[$k] = $val;
        }
        
        // Fallback to constants or hardcoded values if DB is empty
        return [
            'api_id'  => $config['sms_api_id'] ?: '7073c30918869aee144ddca9',
            'api_key' => $config['sms_api_key'] ?: 'bb37df2be980e603326bce12',
            'title'   => $config['sms_title'] ?: 'ROTAREHBER'
        ];
    }

    $action = $_GET['action'] ?? '';

    if ($action == 'register') {
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $city = $_POST['city'] ?? '';
        $district = $_POST['district'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone_raw = $_POST['phone'] ?? '';
        $phone = format_phone_local($phone_raw);
        $pass = $_POST['password'] ?? '';
        $hashed = password_hash($pass, PASSWORD_DEFAULT);

        if (empty($email) || empty($phone) || empty($pass)) {
            send_json(['status' => 'error', 'message' => 'E-posta, Telefon ve Şifre zorunludur.']);
        }

        // Aynı e-posta adresi ile kayıtlı mıyız kontrol et
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_check->execute([$email]);
        if ($stmt_check->fetch()) {
            send_json(['status' => 'error', 'message' => 'Bu e-posta adresi sistemde zaten kayıtlı. Lütfen giriş yapmayı deneyin.']);
        }
        
        // Aynı telefon numarası ile kayıtlı mıyız kontrol et
        $stmt_check_phone = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt_check_phone->execute([$phone]);
        if ($stmt_check_phone->fetch()) {
            send_json(['status' => 'error', 'message' => 'Bu telefon numarası sistemde zaten kayıtlı. Lütfen telefon numaranızı kontrol edin veya giriş yapmayı deneyin.']);
        }
        
        // Kayıt verilerini hazırla (is_active=0 olarak başla, OTP sonrası 1 yapılacak)
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, city, district, email, phone, password, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([$first_name, $last_name, $city, $district, $email, $phone, $hashed]);
        $user_id = $pdo->lastInsertId();

        // OTP Üret ve SMS Gönder
        $otp_code = rand(100000, 999999);
        $_SESSION['registration_otp'] = [
            'code' => $otp_code,
            'user_id' => $user_id,
            'email' => $email,
            'phone' => $phone,
            'full_name' => $first_name . ' ' . $last_name,
            'expiry' => time() + 600 // 10 dakika
        ];

        // Store OTP in DB
        $pdo->prepare("UPDATE users SET otp_code = ? WHERE id = ?")->execute([$otp_code, $user_id]);

        $sms_config = get_sms_config($pdo);
        $smsService = new SmsService($sms_config['api_id'], $sms_config['api_key'], $sms_config['title']);
        $smsMessage = "RotaRehber Uygulamasına Hoş Geldiniz. Doğrulama kodunuz: " . $otp_code;
        $smsService->sendSms($phone, $smsMessage);

        send_json(['status' => 'needs_otp', 'message' => 'Lütfen telefonunuza gelen doğrulama kodunu girin', 'user_id' => $user_id]);
    }
    elseif ($action == 'verify_otp') {
        $code = $_POST['otp_code'] ?? '';
        $session_otp = $_SESSION['registration_otp'] ?? null;
        $user_id = $_POST['user_id'] ?? ($session_otp['user_id'] ?? null);

        if (!$user_id) {
            send_json(['status' => 'error', 'message' => 'Oturum süresi dolmuş veya geçersiz işlem.']);
        }

        $stmt_chk = $pdo->prepare("SELECT otp_code, first_name, last_name, email FROM users WHERE id = ?");
        $stmt_chk->execute([$user_id]);
        $u_info = $stmt_chk->fetch();

        if ($u_info && $code == $u_info['otp_code']) {
            // Success!
            $pdo->prepare("UPDATE users SET is_active = 1, is_verified = 1, otp_code = NULL, last_login_at = NOW() WHERE id = ?")->execute([$user_id]);

            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_email'] = $u_info['email'];
            $_SESSION['user_name'] = $u_info['first_name'] . ' ' . $u_info['last_name'];

            // Optional welcome email
            try {
                $mailService = new MailService($pdo);
                $mailService->sendWelcomeEmail($_SESSION['user_email'], $_SESSION['user_name']);
            } catch (Exception $e) {}

            unset($_SESSION['registration_otp']);

            send_json(['status' => 'success', 'message' => 'Doğrulama başarılı! Giriş yapıldı.', 'user' => [
                'id' => $user_id,
                'email' => $u_info['email'],
                'first_name' => $u_info['first_name'],
                'last_name' => $u_info['last_name']
            ]]);
        } else {
            send_json(['status' => 'error', 'message' => 'Hatalı doğrulama kodu.']);
        }
    }
    elseif ($action == 'login') {
        $identity = $_POST['email'] ?? '';
        $pass = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$identity, $identity]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password'])) {
            if ($user['is_active'] == 0) {
                // OTP Üret ve Tekrar Gönder
                $otp_code = rand(100000, 999999);
                $pdo->prepare("UPDATE users SET otp_code = ? WHERE id = ?")->execute([$otp_code, $user['id']]);

                $sms_config = get_sms_config($pdo);
                $smsService = new SmsService($sms_config['api_id'], $sms_config['api_key'], $sms_config['title']);
                $smsMessage = "RotaRehber Giriş Kodunuz: " . $otp_code;
                $smsService->sendSms($user['phone'], $smsMessage);

                send_json(['status' => 'needs_otp', 'message' => 'Lütfen telefonunuza gelen doğrulama kodunu girin', 'user_id' => $user['id']]);
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            $stmt_last = $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
            $stmt_last->execute([$user['id']]);
            
            send_json(['status' => 'success', 'message' => 'Giriş başarılı.', 'user' => [
                'id' => $user['id'], 
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'phone' => $user['phone']
            ]]);
        } else {
            send_json(['status' => 'error', 'message' => 'Hatalı e-posta/telefon veya şifre.']);
        }
    }
    elseif ($action == 'google_login') {
        $credential = $_REQUEST['credential'] ?? '';
        if (empty($credential)) {
            send_json(['status' => 'error', 'message' => 'Geçersiz veri.']);
        }

        // 1. Google ID Token Doğrulama
        $verify_url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $credential;
        $ch = curl_init($verify_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            send_json(['status' => 'error', 'message' => 'Google doğrulaması başarısız oldu.']);
        }

        $google_user = json_decode($response, true);
        $google_id = $google_user['sub'] ?? null;
        $email = $google_user['email'] ?? null;
        $fname = $google_user['given_name'] ?? '';
        $lname = $google_user['family_name'] ?? '';

        if ($email) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ? OR email = ?");
            $stmt->execute([$google_id, $email]);
            $user = $stmt->fetch();

            if (!$user) {
                // Yeni kullanıcı: Kayıt modülünü açtır
                $picture = $google_user['picture'] ?? '';
                send_json(['status' => 'needs_phone', 'email' => $email, 'first_name' => $fname, 'last_name' => $lname, 'picture' => $picture]);
            }

            // Google verilerini güncelle
            if (empty($user['google_id']) || empty($user['profile_image'])) {
                $pdo->prepare("UPDATE users SET google_id = COALESCE(google_id, ?), profile_image = COALESCE(profile_image, ?) WHERE id = ?")
                    ->execute([$google_id, $google_user['picture'] ?? '', $user['id']]);
            }

            if (empty($user['phone'])) {
                send_json(['status' => 'needs_phone', 'email' => $user['email'], 'first_name' => $user['first_name'], 'last_name' => $user['last_name'], 'picture' => $user['profile_image']]);
            }

            // OTP Üret ve SMS Gönder
            $otp_code = rand(100000, 999999);
            $pdo->prepare("UPDATE users SET otp_code = ? WHERE id = ?")->execute([$otp_code, $user['id']]);

            $sms_config = get_sms_config($pdo);
            $smsService = new SmsService($sms_config['api_id'], $sms_config['api_key'], $sms_config['title']);
            $smsMessage = "RotaRehber Giriş Kodunuz: " . $otp_code;
            $smsService->sendSms($user['phone'], $smsMessage);

            send_json(['status' => 'needs_otp', 'message' => 'Lütfen telefonunuza gelen doğrulama kodunu girin', 'user_id' => $user['id']]);
        } else {
            send_json(['status' => 'error', 'message' => 'Google verisi alınamadı.']);
        }
    }
    elseif ($action == 'google_register') {
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $fname = $_POST['first_name'] ?? '';
        $lname = $_POST['last_name'] ?? '';
        $google_id = $_POST['google_id'] ?? null;

        if (empty($email) || empty($phone)) {
            send_json(['status' => 'error', 'message' => 'E-posta ve Telefon numarası zorunludur.']);
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existing = $stmt->fetch();

        if ($existing) {
            $user_id = $existing['id'];
            $pdo->prepare("UPDATE users SET phone = ?, google_id = COALESCE(google_id, ?) WHERE id = ?")->execute([$phone, $google_id, $user_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone, password, is_active, google_id, profile_image) VALUES (?, ?, ?, ?, 'GOOGLE_LOGIN_NOPASS', 0, ?, ?)");
            $stmt->execute([$fname, $lname, $email, $phone, $google_id, $_POST['picture'] ?? '']);
            $user_id = $pdo->lastInsertId();
        }

        // OTP Gönder
        $otp_code = rand(100000, 999999);
        $pdo->prepare("UPDATE users SET otp_code = ? WHERE id = ?")->execute([$otp_code, $user_id]);

        $sms_config = get_sms_config($pdo);
        $smsService = new SmsService($sms_config['api_id'], $sms_config['api_key'], $sms_config['title']);
        $smsMessage = "RotaRehber Giriş Kodunuz: " . $otp_code;
        $smsService->sendSms($phone, $smsMessage);

        send_json(['status' => 'needs_otp', 'message' => 'Lütfen telefonunuza gelen doğrulama kodunu girin', 'user_id' => $user_id]);
    }
    elseif ($action == 'forgot') {
        $identity = $_POST['identity'] ?? '';
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$identity, $identity]);
        $user = $stmt->fetch();
        
        if ($user) {
            $otp_code = rand(100000, 999999);
            $pdo->prepare("UPDATE users SET otp_code = ? WHERE id = ?")->execute([$otp_code, $user['id']]);

            $sms_config = get_sms_config($pdo);
            $smsService = new SmsService($sms_config['api_id'], $sms_config['api_key'], $sms_config['title']);
            $smsService->sendSms($user['phone'], "Şifre sıfırlama kodunuz: " . $otp_code);

            send_json(['status' => 'needs_otp', 'message' => 'Sıfırlama kodu telefonunuza gönderildi.', 'user_id' => $user['id']]);
        } else {
            send_json(['status' => 'error', 'message' => 'Kullanıcı bulunamadı.']);
        }
    }
    elseif ($action == 'quick_login_request') {
        $phone_raw = $_POST['phone'] ?? '';
        $phone = format_phone_local($phone_raw);

        if (empty($phone)) send_json(['status' => 'error', 'message' => 'Lütfen geçerli bir telefon numarası girin.']);

        // Hem '05...' hem de '5...' formatlarını ara (Veritabanı tutarsızlığına karşı)
        $phone_no_zero = ltrim($phone, '0');
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, is_active, phone FROM users WHERE phone = ? OR phone = ? LIMIT 1");
        $stmt->execute([$phone, $phone_no_zero]);
        $user = $stmt->fetch();

        if ($user) {
            $otp_code = rand(100000, 999999);
            $pdo->prepare("UPDATE users SET otp_code = ? WHERE id = ?")->execute([$otp_code, $user['id']]);

            try {
                $sms_config = get_sms_config($pdo);
                // Correct keys from get_sms_config() result
                $smsService = new SmsService($sms_config['api_id'], $sms_config['api_key'], $sms_config['title'] ?: 'ROTAREHBER');
                $smsService->sendSms($user['phone'], "RotaRehber Giriş Kodunuz: " . $otp_code);
            } catch (Exception $e) {
                // Log error
            }

            send_json(['status' => 'needs_otp', 'message' => 'Lütfen telefonunuza gelen kodu girin.', 'user_id' => (int)$user['id']]);
        } else {
            send_json(['status' => 'error', 'message' => 'Bu telefon numarasıyla kayıtlı bir hesap bulunamadı. Lütfen Kayıt Ol sayfasını kullanın.']);
        }
    }
    elseif ($action == 'logout') {
        session_destroy();
        header("Location: ../index.php");
        exit;
    }
} catch (Throwable $e) {
    send_json(['status' => 'error', 'message' => 'Sistem Hatası: ' . $e->getMessage()]);
}
?>
