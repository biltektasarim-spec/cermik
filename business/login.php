<?php
session_start();
require_once '../config.php';

$ip_key = 'biz_login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
$error  = '';
$locked = false;

if (rate_limit_exceeded($ip_key, 5, 300)) {
    $locked = true;
    $error  = 'Çok fazla başarısız deneme. 5 dakika sonra tekrar deneyin.';
}

if (!$locked && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Kullanıcı adı ve şifre boş bırakılamaz.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM businesses WHERE username = ?");
        $stmt->execute([$username]);
        $business = $stmt->fetch();

        if ($business && password_verify($password, $business['password'])) {
            rate_limit_reset($ip_key);
            session_regenerate_id(true);
            $_SESSION['business_id']   = $business['id'];
            $_SESSION['business_name'] = $business['business_name'];
            $_SESSION['business_cat']  = $business['category'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Geçersiz kullanıcı adı veya şifre.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İşletme Girişi - Çermik</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-box { padding: 40px 20px; max-width: 400px; margin: 0 auto; }
        input { width: 100%; padding: 15px; margin-bottom: 15px; border-radius: 12px;
                border: 1px solid var(--glass-bg); background: var(--card-bg); color: white;
                box-sizing: border-box; }
    </style>
</head>
<body>
<div id="app">
    <div class="login-box section animate-in">
        <h1 style="margin-bottom: 10px;">İşletme Paneli</h1>
        <p style="margin-bottom: 30px; opacity:.7;">Lütfen giriş yaparak ürünlerinizi ve fiyatlarınızı yönetin.</p>

        <?php if ($locked): ?>
            <div style="background:rgba(231,76,60,.15); border:1px solid rgba(231,76,60,.4); color:#f87171;
                        border-radius:10px; padding:12px 15px; margin-bottom:20px; font-size:.9rem;">
                <i class="fa-solid fa-lock"></i> <?php echo e($error); ?>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div style="background:rgba(231,76,60,.15); border:1px solid rgba(231,76,60,.4); color:#f87171;
                            border-radius:10px; padding:12px 15px; margin-bottom:20px; font-size:.9rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <?php echo csrf_field(); ?>
                <input type="text"     name="username" placeholder="Kullanıcı Adı" autocomplete="username" required>
                <input type="password" name="password" placeholder="Şifre"         autocomplete="current-password" required>
                <button type="submit" class="btn btn-primary" style="width:100%;">Giriş Yap</button>
            </form>
        <?php endif; ?>

        <a href="../index.php" style="display:block; text-align:center; margin-top:20px; color:var(--text-secondary);">
            <i class="fa-solid fa-arrow-left"></i> Ana Sayfaya Dön
        </a>
    </div>
</div>
</body>
</html>
