<?php
require_once 'config.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    header("Location: index.php");
    exit;
}

// Token kontrolü (son 1 saat içinde oluşturulmuş olmalı)
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    $error = "Geçersiz veya süresi dolmuş sıfırlama bağlantısı.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset) {
    $new_pass = $_POST['password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if (empty($new_pass) || strlen($new_pass) < 6) {
        $error = "Şifre en az 6 karakter olmalıdır.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "Şifreler eşleşmiyor.";
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt_update = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt_update->execute([$hashed, $reset['email']]);

        // Token'ı sil
        $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$reset['email']]);

        $success = "Şifreniz başarıyla güncellendi. Giriş yapabilirsiniz.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Sıfırlama - Çermik Rehberi</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .reset-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
        }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; opacity: 0.8; }
        input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 12px;
            border: 1px solid var(--glass-bg);
            background: var(--card-bg);
            color: white;
            box-sizing: border-box;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .alert-error { background: rgba(231,76,60,0.15); color: #f87171; border: 1px solid rgba(231,76,60,0.4); }
        .alert-success { background: rgba(40,167,69,0.15); color: #48bb78; border: 1px solid rgba(40,167,69,0.4); }
    </style>
</head>
<body>
    <?php include 'includes/theme_bg.php'; ?>
    <div id="app">
        <div class="reset-container animate-in">
            <h1 style="text-align: center; margin-bottom: 30px;">Yeni Şifre Belirle</h1>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <button class="btn btn-primary" style="width: 100%;" onclick="location.href='index.php'">Giriş Yap</button>
            <?php elseif ($reset): ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Yeni Şifre</label>
                        <input type="password" name="password" required placeholder="******">
                    </div>
                    <div class="form-group">
                        <label>Yeni Şifre (Tekrar)</label>
                        <input type="password" name="confirm_password" required placeholder="******">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Şifreyi Güncelle</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
