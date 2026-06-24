<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
require_once 'config.php';
$is_en_main = (($_SESSION['lang'] ?? 'tr') === 'en');

// Fetch Districts
$stmt = $pdo->query("SELECT d.*, 
                    (SELECT image_main FROM places p WHERE p.district_id = d.id AND p.category = 'HotSpring' LIMIT 1) as special_image 
                    FROM districts d WHERE d.is_active = 1 ORDER BY d.name ASC");
$districts = $stmt->fetchAll();

// Fetch Global Events (Must be approved on both counts, and only upcoming events)
$stmt = $pdo->query("SELECT * FROM events WHERE is_global = 1 AND global_status = 'APPROVED' AND status = 'APPROVED' AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 10");
$global_events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'includes/pwa_meta.php'; ?>
    <title>ROTAREHBER - Diyarbakır Yerel Rehber</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <!-- QR Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        :root {
            --primary: #00c9ff;
            --secondary: #92fe9d;
            --dark: #0f172a;
            --glass: rgba(255, 255, 255, 0.04);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-secondary: #94a3b8;
            --cermik-grad: linear-gradient(135deg, #FF512F, #DD2476);
            --cungus-grad: linear-gradient(135deg, #1d976c, #93f9b9);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        
        body {
            background: radial-gradient(circle at top right, #1e293b, #0f172a);
            color: white;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .container { max-width: 1000px; margin: 0 auto; padding: 0 20px; }

        .header-main {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            backdrop-filter: blur(10px);
            background: rgba(15, 23, 42, 0.4);
            border-bottom: 1px solid var(--glass-border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo-wrap { display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit; }
        .logo-icon { font-size: 1.8rem; color: var(--primary); }
        .logo-text { font-size: 1.5rem; font-weight: 800; letter-spacing: 1px; }

        header.hero {
            text-align: center;
            padding: 50px 20px 30px;
        }

        .hero-title {
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--primary); /* Fallback */
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
            position: relative;
            z-index: 2;
        }

        .subtitle { font-size: 1.1rem; opacity: 0.7; font-weight: 300; }

        /* Main Section Title */
        .section-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 40px 0 20px;
        }
        .section-header h2 { font-size: 1.3rem; font-weight: 700; color: var(--primary); }
        .section-header hr { border: none; border-top: 1px solid var(--glass-border); flex: 1; }

        /* District Grid Area - Styled Boxes */
        .district-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 480px) {
            .district-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
        }

        .district-card {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
            color: inherit;
            display: block;
            backdrop-filter: blur(10px);
            position: relative;
        }
        .district-card:hover { transform: scale(1.03); border-color: var(--primary); }

        .district-img {
            width: 100%;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .district-img i { font-size: 2.5rem; opacity: 0.8; z-index: 1; }

        /* Color Tones (Gradients) for Districts */
        .grad-cermik { background: var(--cermik-grad); }
        .grad-cungus { background: var(--cungus-grad); }

        .overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to top, rgba(15, 23, 42, 0.8), transparent);
        }

        .district-info { padding: 15px; position: relative; }
        .district-name { font-size: 1.1rem; font-weight: 800; display: flex; justify-content: space-between; align-items: center; }
        .dist-tag { font-size: 0.65rem; background: #fff; color: #000; padding: 2px 8px; border-radius: 20px; font-weight: 800; display: none; }
        .dist-w { font-size: 0.8rem; color: var(--text-secondary); margin-top: 8px; font-weight: 600; display: flex; align-items: center; gap: 6px; }

        /* Events Section (Below Districts) */
        .events-strip {
            margin-top: 40px;
            margin-bottom: 60px;
        }

        .event-card {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 20px;
            transition: 0.3s;
            backdrop-filter: blur(10px);
        }
        .event-card:hover { border-color: var(--secondary); background: rgba(146, 254, 157, 0.05); }

        .ev-date-box {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            flex-shrink: 0;
            color: #000;
            font-weight: 800;
            line-height: 1.1;
        }

        .ev-info { flex: 1; }
        .ev-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 4px; }
        .ev-desc { font-size: 0.85rem; color: var(--text-secondary); opacity: 0.8; line-height: 1.4; }

        /* Footer */
        footer {
            padding: 40px 20px;
            text-align: center;
            border-top: 1px solid var(--glass-border);
            opacity: 0.4;
            font-size: 0.75rem;
        }

        footer a { color: inherit; text-decoration: none; margin-top: 10px; display: inline-block; }

        /* QR Scanner */
        .qr-scan-btn {
            margin-top: 25px;
            padding: 14px 28px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #000;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            letter-spacing: 1px;
            transition: 0.3s;
        }
        .qr-scan-btn:hover { transform: scale(1.05); }
        /* QR Scanner Revize */
        .qr-modal {
            display: none;
            position: fixed; inset: 0; 
            background: rgba(15, 23, 42, 0.95);
            z-index: 9999;
            backdrop-filter: blur(10px);
        }
        .qr-modal.active { display: flex; align-items: center; justify-content: center; }
        
        .qr-scanner-box {
            position: relative;
            width: 85vw;
            max-width: 400px;
            aspect-ratio: 1/1;
            border-radius: 30px;
            overflow: hidden;
            border: 2px solid rgba(255,255,255,0.1);
            box-shadow: 0 0 40px rgba(0,0,0,0.5);
            background: #000;
        }

        #qr-reader {
            width: 100% !important;
            height: 100% !important;
            border: none !important;
        }

        /* Çerçeve (Frame) - Reader ile aynı kapsayıcıda */
        .qr-frame-overlay {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-target-frame {
            width: 70%;
            height: 70%;
            border: 4px solid var(--primary);
            border-radius: 20px;
            position: relative;
            box-shadow: 0 0 0 1000px rgba(15, 23, 42, 0.4);
        }

        .qr-scan-line {
            position: absolute;
            top: 10%; left: 5%;
            width: 90%; height: 2px;
            background: var(--primary);
            box-shadow: 0 0 15px var(--primary);
            border-radius: 10px;
            animation: qr-scan 2s ease-in-out infinite;
        }

        @keyframes qr-scan {
            0%, 100% { top: 10%; opacity: 0.3; }
            50% { top: 90%; opacity: 1; }
        }

        /* Library Hidden Elements */
        #qr-reader__dashboard { display: none !important; }
        #qr-reader__scan_region { 
            width: 100% !important; 
            height: 100% !important; 
            display: flex !important;
        }
        #qr-reader__scan_region video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }
        #qr-reader img[alt="Info icon"], #qr-reader img[alt="Camera icon"] { display: none !important; }

        .qr-close-btn {
            position: absolute;
            bottom: 30px; left: 50%;
            transform: translateX(-50%);
            padding: 12px 40px;
            background: rgba(255,255,255,0.1); color: white;
            border: 1px solid rgba(255,255,255,0.2); border-radius: 50px;
            cursor: pointer; font-size: 0.95rem; font-weight: 700;
            backdrop-filter: blur(15px); transition: 0.3s;
            z-index: 100;
        }
        #qr-reader__dashboard { display: none !important; }
        #qr-reader__scan_region { width: 100% !important; height: 100% !important; }
        #qr-reader__scan_region img { display: none !important; }

        /* APK & Install Button */
        .header-actions {
            position: absolute;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .apk-download-link {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 8px 14px;
            border-radius: 12px;
            color: var(--secondary);
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 700;
            transition: 0.3s;
            backdrop-filter: blur(10px);
        }
        .apk-download-link:hover {
            background: var(--secondary);
            color: #000;
            transform: translateY(-2px);
        }
        .apk-download-link i { font-size: 0.9rem; }
        
        @media (max-width: 480px) {
            .logo-text { font-size: 1.2rem; }
            .apk-download-link span { display: none; }
            .apk-download-link { padding: 8px 10px; }
        }

        /* Privacy Modal */
        .privacy-modal {
            display: none; position: fixed; inset: 0; 
            background: rgba(15, 23, 42, 0.95);
            z-index: 99999; backdrop-filter: blur(10px);
            align-items: center; justify-content: center; padding: 20px;
        }
        .privacy-modal.active { display: flex; }
        .privacy-card {
            background: white; width: 100%; max-width: 600px;
            max-height: 80vh; overflow-y: auto; border-radius: 24px;
            padding: 40px; position: relative;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            touch-action: pan-y;
            -webkit-overflow-scrolling: touch;
        }
        .privacy-close {
            position: absolute; top: 15px; right: 15px;
            background: #eee; border: none; width: 32px; height: 32px;
            border-radius: 50%; cursor: pointer; color: #333;
            display: flex; align-items: center; justify-content: center;
        }
    </style>
</head>
<body data-page-context="Ana Sayfa">
<?php $is_district_page = false; include 'includes/splash_screen.php'; ?>

    <div class="header-main">
        <a href="index.php" class="logo-wrap">
            <i class="fa-solid fa-route logo-icon"></i>
            <span class="logo-text">ROTAREHBER</span>
        </a>
        <div class="header-actions">
            <!-- Language Switcher -->
            <div style="display:flex; gap:6px; align-items:center;">
                <a href="?lang=tr" style="padding:6px 12px; border-radius:10px; font-size:0.75rem; font-weight:700; text-decoration:none; transition:0.2s; <?php echo !$is_en_main ? 'background:var(--primary); color:#000;' : 'background:rgba(255,255,255,0.07); color:var(--text-secondary); border:1px solid rgba(255,255,255,0.1);'; ?>" title="Türkçe">TR</a>
                <a href="?lang=en" style="padding:6px 12px; border-radius:10px; font-size:0.75rem; font-weight:700; text-decoration:none; transition:0.2s; <?php echo $is_en_main ? 'background:var(--primary); color:#000;' : 'background:rgba(255,255,255,0.07); color:var(--text-secondary); border:1px solid rgba(255,255,255,0.1);'; ?>" title="English">EN</a>
            </div>
            <!-- Mobile App Download Link -->
            <a href="assets/apk/rotarehber_v1.6.apk" class="apk-download-link" title="Android Uygulamasını İndir">
                <i class="fa-brands fa-android"></i>
                <span>APK İNDİR</span>
            </a>
        </div>
    </div>

    <header class="hero">
        <div class="container">
            <p style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 3px; color: var(--primary); margin-bottom: 12px; font-weight: 800;">Diyarbakır Yerel Rehber Platformu</p>
            <h1 class="hero-title">ROTAREHBER</h1>
            <p class="subtitle">İlçeleri Keşfetmeye Başlayın</p>
            
            <!-- QR Scanner Button -->
            <button class="qr-scan-btn" onclick="openQRScanner()">
                <i class="fa-solid fa-qrcode"></i>
                KAREKOD OKUT
            </button>
            <p style="text-align: center; font-size: 0.75rem; opacity: 0.6; margin-top: 10px;">rotarehber FreeMind kuruluşudur</p>
        </div>
    </header>

    <main class="container">
        
        <div class="section-header">
            <h2>İlçeler</h2>
            <hr>
        </div>

        <!-- District Section (Menu boxes with Color Tones) -->
        <section class="district-grid" id="district-container">
            <?php foreach($districts as $d): ?>
            <?php 
                $grad_class = ($d['slug'] == 'cermik') ? 'grad-cermik' : 'grad-cungus';
                $icon = ($d['slug'] == 'cermik') ? 'fa-fort-awesome' : 'fa-mountain-sun';
                
                $fallback_img = ($d['slug'] == 'cermik') ? 'assets/img/categories/kaplica.jpg' : 'assets/img/categories/historical.jpg';
                $bg_img = !empty($d['special_image']) ? $d['special_image'] : $fallback_img;
            ?>
            <a href="<?php echo $d['slug']; ?>/" class="district-card" data-lat="<?php echo $d['lat']; ?>" data-lng="<?php echo $d['lng']; ?>">
                <div class="district-img <?php echo $grad_class; ?>" style="background-image: url('<?php echo $bg_img; ?>'); background-size: cover; background-position: center;">
                    <div class="overlay"></div>
                </div>
                <div class="district-info">
                    <div class="district-name">
                        <?php echo $d['name']; ?>
                        <span class="dist-tag">-- km</span>
                    </div>
                    <div class="dist-w" id="weather-<?php echo $d['slug']; ?>">
                        <i class="fa-solid fa-cloud"></i> Kontrol ediliyor...
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </section>

        <div class="section-header">
            <h2>Gelecek Etkinlikler</h2>
            <hr>
        </div>

        <!-- Global Events Section -->
        <section class="events-strip">
            <?php if (empty($global_events)): ?>
                <div class="card" style="padding: 25px; text-align: center; background: var(--glass); border-radius: 20px; border: 1px solid var(--glass-border);">
                   <p style="opacity: 0.5; font-size: 0.95rem;">Henüz yayınlanmış bir genel etkinlik bulunmuyor.</p>
                </div>
            <?php else: ?>
                <?php foreach($global_events as $ev): ?>
                <div class="event-card animate-in" <?php if(!empty($ev['image'])): ?>style="cursor: pointer;" onclick="app.openImageLightbox('<?php echo str_replace("'", "\\'", $ev['image']); ?>')"<?php endif; ?>>
                    <div class="ev-date-box">
                        <span style="font-size: 1.4rem;"><?php echo date('d', strtotime($ev['event_date'])); ?></span>
                        <span style="font-size: 0.65rem; text-transform: uppercase;"><?php echo date('M', strtotime($ev['event_date'])); ?></span>
                    </div>
                    <?php if (!empty($ev['image'])): ?>
                    <div class="ev-img-wrap" style="position: relative; flex-shrink: 0; width: 80px; height: 80px; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid var(--glass-border); overflow: hidden;">
                        <img src="<?php echo $ev['image']; ?>" style="width: 100%; height: 100%; object-fit: contain; display: block;">
                        <div style="position:absolute; bottom:4px; right:4px; background:rgba(0,0,0,0.5); border-radius:50%; width:20px; height:20px; display:flex; align-items:center; justify-content:center;">
                            <i class="fa-solid fa-expand" style="color:white; font-size:0.6rem;"></i>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="ev-info">
                        <div class="ev-title"><?php echo htmlspecialchars($ev['title']); ?></div>
                        <div class="ev-desc"><?php echo mb_substr(strip_tags($ev['description']), 0, 100) . '...'; ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> ROTAREHBER – YILMAZLAR BİLİŞİM / Freemind</p>
        <p style="display: flex; justify-content: center; gap: 18px; margin-top: 12px; flex-wrap: wrap;">
            <a href="gizlilik-politikasi.php" target="_blank"><i class="fa-solid fa-user-shield"></i> Gizlilik Politikası</a>
            <a href="kvkk-aydinlatma.php" target="_blank"><i class="fa-solid fa-file-shield"></i> KVKK Aydınlatma</a>
            <a href="cerez-politikasi.php" target="_blank"><i class="fa-solid fa-cookie-bite"></i> Çerez Politikası</a>
            <a href="kullanim-sartlari.php" target="_blank"><i class="fa-solid fa-scale-balanced"></i> Kullanım Şartları</a>
            <?php if (strpos($_SERVER['HTTP_USER_AGENT'] ?? '', 'Android') === false): ?>
                <a href="admin/login.php"><i class="fa-solid fa-shield-halved"></i> Yönetim Girişi</a>
                <a href="business/login.php"><i class="fa-solid fa-store"></i> İşletme Girişi</a>
            <?php endif; ?>
        </p>
    </footer>

    <script id="district-data" type="application/json"><?php echo json_encode($districts); ?></script>

    <script>
        const districtsData = JSON.parse(document.getElementById('district-data').textContent);
        
        async function updateWeather() {
            const ts = Date.now(); // cache-bust
            for (const d of districtsData) {
                try {
                    const res = await fetch(
                        `https://api.open-meteo.com/v1/forecast?latitude=${d.lat}&longitude=${d.lng}&current_weather=true&_t=${ts}`,
                        { cache: 'no-store' }
                    );
                    const data = await res.json();
                    const el = document.getElementById(`weather-${d.slug}`);
                    if (el && data.current_weather) {
                        const temp = Math.round(data.current_weather.temperature);
                        const wcode = data.current_weather.weathercode;
                        let icon = 'fa-sun';
                        if (wcode >= 51) icon = 'fa-cloud-rain';
                        else if (wcode >= 3) icon = 'fa-cloud';
                        else if (wcode >= 1) icon = 'fa-cloud-sun';
                        el.innerHTML = `<i class="fa-solid ${icon}"></i> ${temp}°C`;
                    }
                } catch(e) {
                    console.warn('Hava durumu alınamadı:', e);
                }
            }
        }

        // Sayfa yüklenince çalıştır + her 10 dakikada bir güncelle
        updateWeather();
        setInterval(updateWeather, 10 * 60 * 1000);

        function calculateDist(lat1, lon1, lat2, lon2) {
            const p = 0.017453292519943295;
            const c = Math.cos;
            const a = 0.5 - c((lat2 - lat1) * p)/2 + c(lat1 * p) * c(lat2 * p) * (1 - c((lon2 - lon1) * p))/2;
            return 12742 * Math.asin(Math.sqrt(a));
        }

        function sortDist(uLat, uLng) {
            const container = document.getElementById('district-container');
            if(!container) return;
            const cards = Array.from(container.children);
            cards.sort((a,b) => {
                const dA = calculateDist(uLat, uLng, a.dataset.lat, a.dataset.lng);
                const dB = calculateDist(uLat, uLng, b.dataset.lat, b.dataset.lng);
                return dA - dB;
            });
            cards.forEach(card => {
                const dist = calculateDist(uLat, uLng, card.dataset.lat, card.dataset.lng);
                const tag = card.querySelector('.dist-tag');
                if(tag) {
                    if (dist < 10) {
                        tag.style.display = 'none';
                    } else {
                        tag.textContent = dist.toFixed(1) + ' km';
                        tag.style.display = 'block';
                    }
                }
                container.appendChild(card);
            });
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(p => sortDist(p.coords.latitude, p.coords.longitude));
        }

        function closeQRScanner() {

            if (html5QrCode) {
                html5QrCode.stop().catch(e => console.error(e));
            }
            document.getElementById('qr-modal').classList.remove('active');
        }

        // â”€â”€ QR Scanner Logic â”€â”€
        let html5QrCode;

        function openQRScanner() {
            document.getElementById('qr-modal').classList.add('active');
            
            // Wait for modal animation
            setTimeout(() => {
                const readerNode = document.getElementById("qr-reader");
                if (!readerNode) { alert("Reader element not found!"); return; }
                
                html5QrCode = new Html5Qrcode("qr-reader");
                const config = { fps: 20 };

                html5QrCode.start(
                    { facingMode: "environment" }, 
                    config,
                    onScanSuccess
                ).catch(err => {
                    console.error("QR Error:", err);
                    alert("Kamera izni verilmedi veya cihazda kamera bulunamadı.");
                    closeQRScanner();
                });
            }, 300);
        }

        function onScanSuccess(decodedText, decodedResult) {
            // Stop scanner
            html5QrCode.stop().then(() => {
                document.getElementById('qr-modal').classList.remove('active');
                
                // Handle the link
                try {
                    const url = new URL(decodedText);
                    // Check if it's our domain or a known pattern
                    if (url.searchParams.has('id')) {
                        const id = url.searchParams.get('id');
                        window.location.href = `place_detail.php?id=${id}`;
                    } else if (decodedText.includes('/go/')) {
                        // Support for the proposed /go/{id} structure
                        const parts = decodedText.split('/go/');
                        const id = parts[parts.length - 1];
                        window.location.href = `place_detail.php?id=${id}`;
                    } else {
                        // External link? Confirm before redirect
                        if (confirm("Dış bir bağlantı bulundu: " + decodedText + "\nGitmek istiyor musunuz?")) {
                            window.location.href = decodedText;
                        } else {
                            openQRScanner(); // Restart if canceled
                        }
                    }
                } catch (e) {
                    alert("Geçersiz karekod veya metin: " + decodedText);
                    openQRScanner();
                }
            });
        }

        function closeQRScanner() {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => {
                    document.getElementById('qr-modal').classList.remove('active');
                });
            } else {
                document.getElementById('qr-modal').classList.remove('active');
            }
        }
    </script>

    <script>
        function openPrivacy() {
            document.getElementById('privacy-modal').classList.add('active');
        }
        function closePrivacy() {
            document.getElementById('privacy-modal').classList.remove('active');
        }
    </script>

    <!-- QR Scanner Modal Revize -->
    <div id="qr-modal" class="qr-modal">
        <div class="qr-scanner-box">
            <div id="qr-reader"></div>
            <div class="qr-frame-overlay">
                <div class="qr-target-frame">
                    <div class="qr-scan-line"></div>
                </div>
            </div>
        </div>
        <button class="qr-close-btn" onclick="closeQRScanner()">
            <i class="fa-solid fa-xmark"></i> KAPAT
        </button>
    </div>

    <!-- Privacy Modal -->
    <div id="privacy-modal" class="privacy-modal">
        <div class="privacy-card">
            <button class="privacy-close" onclick="closePrivacy()"><i class="fa-solid fa-xmark"></i></button>
            <?php include 'privacy_policy.php'; ?>
        </div>
    </div>

    <!-- Auth Modal & JS -->
    <?php include 'includes/auth_modal.php'; ?>
    <?php include 'includes/smart_banner.php'; ?>
    <script src="assets/js/turkey_data.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>

