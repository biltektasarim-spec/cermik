<?php
$is_district = (isset($is_district_page) && $is_district_page) || (strpos($_SERVER['REQUEST_URI'], '/cermik/') !== false) || (strpos($_SERVER['REQUEST_URI'], '/cungus/') !== false);
$prefix = $is_district ? '../' : '';
?>
<link rel="manifest" href="<?php echo $prefix; ?>manifest.json">
<meta name="theme-color" content="#0088cc">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('<?php echo $prefix; ?>sw.js');
        });
    }

    // Disable default PWA Install prompt to allow our custom Smart App Banner
    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent Chrome 67 and earlier from automatically showing the prompt
        e.preventDefault();
        // Stash the event so it can be triggered later if needed.
        window.deferredPrompt = e;
    });
</script>
