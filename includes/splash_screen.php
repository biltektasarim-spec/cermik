<?php
// Dizin tespiti: cermik/ veya cungus/ altındaysa '../' prefix kullan
$_splash_prefix = '';
$_uri = $_SERVER['REQUEST_URI'] ?? '';
if (preg_match('#/(cermik|cungus)/#i', $_uri)) {
    $_splash_prefix = '../';
} elseif (isset($is_district_page) && $is_district_page) {
    $_splash_prefix = '../';
}
?>
<!-- App Splash Screen -->
<div id="app-splash-screen" style="
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: #0a0e14; display: flex; flex-direction: column;
    justify-content: center; align-items: center; z-index: 999999;
    transition: opacity 0.5s ease;">
    <img src="<?php echo $_splash_prefix; ?>splash_logo.png"
         alt="ROTAREHBER"
         style="width: 140px; height: auto; object-fit: contain; margin-bottom: 30px; animation: splash-pulse 2s ease-in-out infinite;"
         onerror="this.style.display='none'">
    <div style="
        width: 40px; height: 40px;
        border: 3px solid rgba(0, 201, 255, 0.15);
        border-radius: 50%;
        border-top-color: #00c9ff;
        animation: splash-spin 1s linear infinite;"></div>
    <p style="margin-top: 20px; color: rgba(255,255,255,0.4); font-size: 0.8rem; font-weight: 600; letter-spacing: 2px; font-family: 'Outfit', sans-serif;"><?php echo mb_strtoupper(__('loading')); ?></p>
</div>

<style>
@keyframes splash-pulse {
    0%, 100% { opacity: 0.85; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.05); }
}
@keyframes splash-spin {
    to { transform: rotate(360deg); }
}
</style>

<script>
(function() {
    // Minimum 1.2s göster, sayfa yüklenince kapat (max 4s)
    var splash = document.getElementById('app-splash-screen');
    var minTime = 1200;
    var startTime = Date.now();

    function hideSplash() {
        if (!splash) return;
        var elapsed = Date.now() - startTime;
        var delay = Math.max(0, minTime - elapsed);
        setTimeout(function() {
            splash.style.opacity = '0';
            setTimeout(function() {
                if (splash && splash.parentNode) splash.parentNode.removeChild(splash);
            }, 500);
        }, delay);
    }

    // Sayfa tamamen yüklenince kapat
    if (document.readyState === 'complete') {
        hideSplash();
    } else {
        window.addEventListener('load', hideSplash);
    }

    // Güvenlik: her halükarda 4 saniye sonra kapat
    setTimeout(function() {
        if (splash && splash.style.opacity !== '0') hideSplash();
    }, 4000);
})();
</script>
