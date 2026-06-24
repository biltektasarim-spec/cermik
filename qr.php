<?php
/**
 * RotaRehber Smart Yönlendirici
 */

$target_type = $_GET['target'] ?? '';
$target_id   = intval($_GET['id'] ?? 0);
$path        = $_GET['path'] ?? '';

// Mağaza URL'leri
$android_store_url = "https://play.google.com/store/apps/details?id=com.rotarehber.app";
$ios_store_url     = "https://apps.apple.com/tr/app/rotarehber/id6765806706";

// User Agent tespiti
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$is_ios     = (bool)preg_match('/iPhone|iPad|iPod|Macintosh/i', $ua) && !preg_match('/Windows/i', $ua);
$is_android = (bool)preg_match('/Android/i', $ua);

// 1. Masaüstü cihazlar (Hem iOS hem Android değilse)
// Not: iPad Safari kendini Macintosh olarak tanıtabilir, bu yüzden Macintosh'u is_ios içine ekledik.
if (!$is_android && !$is_ios) {
    header("Location: " . $android_store_url);
    exit;
}

// Android Intent URL
$intent_url = "intent://" . $_SERVER['HTTP_HOST'] . "/qr.php?" . http_build_query($_GET)
    . "#Intent;scheme=https;package=com.rotarehber.app;"
    . "S.browser_fallback_url=" . urlencode($android_store_url) . ";end";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RotaRehber - Yönlendiriliyor...</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, Arial, sans-serif; min-height: 100vh; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 40px 30px; max-width: 380px; width: 100%; text-align: center; }
        .logo { font-size: 52px; margin-bottom: 16px; }
        h2 { color: #fff; font-size: 22px; margin-bottom: 24px; }
        .spinner { width: 52px; height: 52px; border: 4px solid rgba(255,255,255,0.1); border-top-color: #4CAF50; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 20px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        #status-text { color: rgba(255,255,255,0.55); font-size: 14px; }
        .store-btn { display: none; margin-top: 20px; background: #4CAF50; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">🗺️</div>
        <h2>RotaRehber</h2>
        <div class="spinner" id="spinner"></div>
        <p id="status-text">Uygulama kontrol ediliyor...</p>
        <a href="#" id="store-btn" class="store-btn">Mağazaya Git</a>
    </div>
    <script>
        var isAndroid = <?= $is_android ? 'true' : 'false' ?>;
        var isIos     = <?= $is_ios ? 'true' : 'false' ?>;
        var intentUrl = "<?= addslashes($intent_url) ?>";
        var androidStoreUrl = "<?= addslashes($android_store_url) ?>";
        var iosStoreUrl     = "<?= addslashes($ios_store_url) ?>";
        var appOpened = false;

        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                appOpened = true;
            }
        });

        if (isAndroid) {
            // Android için Intent açmayı dene
            window.location.href = intentUrl;
        } else if (isIos) {
            // iOS için Universal Links zaten Safari dışında devreye girmeliydi.
            // Eğer Safari buraya ulaştıysa, uygulama yüklü olmayabilir.
            // Fakat arka planda çalışmasını önlemek için anında PHP yönlendirmesi yapmıyoruz.
            document.getElementById('store-btn').href = iosStoreUrl;
        }

        // 2.5 saniye bekle, eğer Safari hala ekrandaysa (arka planda değilse) ve app açılmadıysa mağazaya yönlendir.
        setTimeout(function() {
            if (!appOpened && !document.hidden) {
                document.getElementById('spinner').style.display = 'none';
                
                if (isAndroid) {
                    document.getElementById('status-text').textContent = 'Uygulama bulunamadı. Google Play\'e yönlendiriliyor...';
                    window.location.replace(androidStoreUrl);
                } else if (isIos) {
                    document.getElementById('status-text').textContent = 'Uygulama bulunamadı. App Store\'a yönlendiriliyor...';
                    window.location.replace(iosStoreUrl);
                }
            } else {
                // Uygulama açılmış (veya arka plandayız), beklemede kal
                document.getElementById('spinner').style.display = 'none';
                document.getElementById('status-text').textContent = 'Uygulamaya yönlendirildi.';
            }
        }, 2500);
    </script>
</body>
</html>
