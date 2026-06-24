<?php
header("HTTP/1.1 301 Moved Permanently");
header("Location: gizlilik-politikasi.php");
exit();
?>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gizlilik Politikası - ROTAREHBER</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f7f6; color: #333; font-family: 'Inter', sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
        .privacy-container { max-width: 800px; margin: 40px auto; padding: 40px; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .logo-wrap { text-align: center; margin-bottom: 30px; }
        .logo-wrap h1 { color: #0088cc; font-size: 2rem; font-weight: 800; letter-spacing: -1px; }
        footer { margin-top: auto; padding: 20px; text-align: center; color: #aaa; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="privacy-container">
        <div class="logo-wrap">
            <h1>ROTAREHBER</h1>
        </div>
        <?php include 'privacy_policy.php'; ?>
        <div style="margin-top: 40px; text-align: center;">
            <a href="index.php" style="color: #0088cc; text-decoration: none; font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Ana Sayfaya Dön</a>
        </div>
    </div>
    <footer>
        &copy; <?php echo date('Y'); ?> ROTAREHBER - Tüm hakları saklıdır.
    </footer>
</body>
</html>
