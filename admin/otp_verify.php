<?php
require_once '../config.php';

if (!isset($_SESSION['admin_otp_pending']) || $_SESSION['admin_otp_pending'] !== true) {
    header('Location: login.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['otp_code'] ?? '');
    
    if (time() > ($_SESSION['admin_otp_expiry'] ?? 0)) {
        $error = 'Kodun süresi dolmuş. Lütfen tekrar giriş yapın.';
        unset($_SESSION['admin_otp_pending']);
    } elseif ($code == $_SESSION['admin_otp_code']) {
        // Başarılı
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_logged_at'] = time();
        unset($_SESSION['admin_otp_pending'], $_SESSION['admin_otp_code'], $_SESSION['admin_otp_expiry']);
        header('Location: index.php');
        exit;
    } else {
        $error = 'Geçersiz doğrulama kodu.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Doğrulama - Çermik Belediyesi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .login-wrap { max-width: 380px; margin: 80px auto; padding: 20px; }
        input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    </style>
</head>
<body>
<div id="app">
    <div class="section animate-in login-wrap">
        <h1 style="text-align:center; margin-bottom: 8px;">Güvenlik Doğrulaması</h1>
        <p style="text-align:center; margin-bottom: 30px; opacity:.7;">Telefonunuza gelen 6 haneli kodu girin.</p>

        <?php if ($error): ?>
            <div style="background:rgba(231,76,60,.15); border:1px solid rgba(231,76,60,.4); color:#f87171;
                        border-radius:10px; padding:12px 15px; margin-bottom:20px; font-size:.9rem;">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <input type="number" name="otp_code" placeholder="000000" maxlength="6"
                   style="width:100%; padding:15px; border-radius:12px; border:1px solid var(--glass-bg); background:var(--card-bg); color:white; margin-bottom:20px; box-sizing:border-box; text-align:center; font-size:1.5rem; letter-spacing:10px;" required autofocus>
            <button type="submit" class="btn btn-primary" style="width:100%;">Doğrula ve Giriş Yap</button>
            <div style="text-align:center; margin-top:15px;">
                <a href="login.php" style="color:rgba(255,255,255,0.5); font-size:0.85rem;">Geri Dön</a>
            </div>
        </form>
    </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>
