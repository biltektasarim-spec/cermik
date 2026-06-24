<?php
require_once '../config.php';
require_once __DIR__ . '/../includes/SmsService.php';

// ─── Rate-limit ───────────────────────────────────────────────────────────────
$ip_key = 'admin_login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

$error = '';
$locked = false;

// ─── Giriş İşlemi ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limit güvenlik koruması
    if (rate_limit_exceeded($ip_key, 10, 300)) {
        $locked = true;
        $error  = 'Çok fazla başarısız deneme. 5 dakika sonra tekrar deneyin.';
    }

    if (!$locked) {
        $usernameOrEmail = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // 1. Önce users tablosunda ara (Modern Yönetim)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR phone = ?) AND role IN ('SUPER_ADMIN', 'DISTRICT_ADMIN') AND is_active = 1");
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $u = $stmt->fetch();

        if ($u && password_verify($password, $u['password'])) {
            rate_limit_reset($ip_key);
            
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user_id'] = $u['id'];
            $_SESSION['admin_role'] = $u['role'];
            $_SESSION['admin_district_id'] = $u['district_id'];
            $_SESSION['admin_name'] = $u['first_name'] . ' ' . $u['last_name'];
            $_SESSION['admin_logged_at'] = time();

            header('Location: index.php');
            exit;
        }

        // 2. Yedek: settings tablosundaki admin_username (Eski Yönetim - Super Admin FB)
        $settings_res = $pdo->query("SELECT name, value FROM settings WHERE name IN ('admin_username', 'admin_password_hash')")->fetchAll();
        $s = [];
        foreach ($settings_res as $row) $s[$row['name']] = $row['value'];

        $stored_username = $s['admin_username'] ?? 'admin';
        $stored_hash = $s['admin_password_hash'] ?? null;

        if ($usernameOrEmail === $stored_username && $stored_hash && password_verify($password, $stored_hash)) {
            rate_limit_reset($ip_key);
            
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_role'] = 'SUPER_ADMIN';
            $_SESSION['admin_district_id'] = 0; // 0 = Genel Admin
            $_SESSION['admin_name'] = 'Genel Yönetici';
            $_SESSION['admin_logged_at'] = time();

            header('Location: index.php');
            exit;
        }

        $error = 'Geçersiz kullanıcı adı veya şifre.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Girişi - Çermik Belediyesi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .login-wrap { max-width: 380px; margin: 80px auto; padding: 20px; }
        .lock-icon { text-align: center; font-size: 3rem; margin-bottom: 15px; color: #e74c3c; }
    </style>
</head>
<body>
<div id="app">
    <div class="section animate-in login-wrap">
        <h1 style="text-align:center; margin-bottom: 8px;">Belediye Yönetimi</h1>
        <p style="text-align:center; margin-bottom: 30px; opacity:.7;">Lütfen yetkili girişi yapın.</p>

        <?php if ($locked): ?>
            <div class="lock-icon"><i class="fa-solid fa-lock"></i></div>
            <p style="color:#e74c3c; text-align:center; font-weight:700;"><?php echo htmlspecialchars($error); ?></p>
        <?php else: ?>
            <?php if ($error): ?>
                <div style="background:rgba(231,76,60,.15); border:1px solid rgba(231,76,60,.4); color:#f87171;
                            border-radius:10px; padding:12px 15px; margin-bottom:20px; font-size:.9rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <input type="text"     name="username" placeholder="Kullanıcı Adı" autocomplete="username"
                       style="width:100%; padding:15px; border-radius:12px; border:1px solid var(--glass-bg); background:var(--card-bg); color:white; margin-bottom:15px; box-sizing:border-box;" required>
                <input type="password" name="password" placeholder="Şifre"         autocomplete="current-password"
                       style="width:100%; padding:15px; border-radius:12px; border:1px solid var(--glass-bg); background:var(--card-bg); color:white; margin-bottom:20px; box-sizing:border-box;" required>
                <button type="submit" class="btn btn-primary" style="width:100%;">Giriş Yap</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>
