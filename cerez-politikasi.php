<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Çerez Politikası – RotaRehber</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        html, body{
            height:100%;
            overflow-y:auto !important;
            -webkit-overflow-scrolling:touch;
            overscroll-behavior-y:auto;
            touch-action:pan-y;
        }
        body{background:#f0f4f8;font-family:'Inter',sans-serif;color:#1a1a2e;line-height:1.75}
        .wrap{max-width:860px;margin:24px auto;padding:0 16px 80px}
        .card{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.07);padding:48px 52px}
        .brand{display:flex;align-items:center;gap:12px;margin-bottom:36px;padding-bottom:24px;border-bottom:2px solid #e8f0fe}
        .brand h1{font-size:1.6rem;font-weight:800;color:#0a6ebd}
        .brand span{background:#0a6ebd;color:#fff;font-size:.7rem;font-weight:700;padding:3px 10px;border-radius:20px;letter-spacing:.5px}
        h2{font-size:1.05rem;font-weight:700;color:#0a6ebd;margin:28px 0 10px;padding-bottom:6px;border-bottom:1px solid #e8f0fe}
        p,li{font-size:.93rem;color:#444;margin-bottom:10px}
        ul{padding-left:22px;margin-bottom:10px}
        li{margin-bottom:6px}
        table{width:100%;border-collapse:collapse;margin:14px 0;font-size:.88rem}
        th{background:#e8f0fe;color:#0a6ebd;padding:10px 14px;text-align:left;font-weight:600}
        td{padding:9px 14px;border-bottom:1px solid #f0f4f8;color:#444;vertical-align:top}
        .tag{display:inline-block;padding:2px 8px;border-radius:5px;font-size:.76rem;font-weight:700}
        .tag.z{background:#d1fae5;color:#065f46}
        .tag.a{background:#dbeafe;color:#1e40af}
        .tag.i{background:#ede9fe;color:#5b21b6}
        .update{font-size:.82rem;color:#999;margin-top:40px;padding-top:16px;border-top:1px solid #eee}
        .back{display:inline-block;margin-top:28px;color:#0a6ebd;text-decoration:none;font-weight:600;font-size:.9rem}
        @media(max-width:600px){.card{padding:20px 16px}}
    </style>
</head>
<body>
<div class="wrap">
<div class="card">
    <div class="brand">
        <h1>RotaRehber</h1>
        <span>ÇEREZ POLİTİKASI</span>
    </div>

    <p><strong>Son Güncelleme:</strong> 22 Nisan 2026</p>
    <p>Bu Çerez Politikası; <strong>FreeMind</strong> tarafından işletilen <strong>RotaRehber (rotarehber.com)</strong> platformunun çerez kullanım pratiklerini açıklar. 6698 sayılı KVKK ve 5809 sayılı Elektronik Haberleşme Kanunu kapsamında hazırlanmıştır.</p>

    <h2>1. Çerez Nedir?</h2>
    <p>Çerezler (cookie), web sitelerini ziyaret ettiğinizde tarayıcınız aracılığıyla cihazınıza kaydedilen küçük metin dosyalarıdır. Bu dosyalar sayesinde platform sizi tanır, tercihlerinizi hatırlar ve daha iyi bir deneyim sunar. Çerezler yalnızca metin içerir; kişisel dosyalarınıza erişemez.</p>

    <h2>2. Kullandığımız Çerez Türleri</h2>
    <table>
        <tr><th>Çerez Türü</th><th>Adı / Amacı</th><th>Süre</th><th>Kapatılabilir mi?</th></tr>
        <tr>
            <td><span class="tag z">Zorunlu</span></td>
            <td>Oturum yönetimi (PHP session), CSRF koruması, güvenli giriş</td>
            <td>Oturum sonu</td>
            <td>Hayır – site çalışmaz</td>
        </tr>
        <tr>
            <td><span class="tag a">Analitik</span></td>
            <td>Google Firebase Analytics – sayfa görüntüleme, kullanım süresi</td>
            <td>26 ay</td>
            <td>Evet</td>
        </tr>
        <tr>
            <td><span class="tag i">İşlevsel</span></td>
            <td>Dil tercihi, bölge seçimi, oturum açık kalma tercihi</td>
            <td>30 gün</td>
            <td>Evet</td>
        </tr>
    </table>

    <h2>3. Üçüncü Taraf Çerezleri</h2>
    <table>
        <tr><th>Sağlayıcı</th><th>Amaç</th><th>Politika</th></tr>
        <tr><td>Google Maps</td><td>Harita gösterimi ve konum hizmetleri</td><td>policies.google.com</td></tr>
        <tr><td>Google Firebase</td><td>Analitik ve push bildirim altyapısı</td><td>firebase.google.com/support/privacy</td></tr>
    </table>
    <p>Üçüncü taraf çerezleri için ilgili sağlayıcıların gizlilik politikaları geçerlidir.</p>

    <h2>4. Mobil Uygulama (Android / iOS)</h2>
    <p>RotaRehber mobil uygulaması, tarayıcı çerezi kullanmaz. Bunun yerine:</p>
    <ul>
        <li><strong>Firebase Analytics:</strong> Anonim kullanım istatistikleri</li>
        <li><strong>Shared Preferences / Hive:</strong> Cihaz üzerinde yerel tercih saklama (kullanıcı adı, token)</li>
        <li><strong>FCM Token:</strong> Push bildirim kimlik doğrulaması</li>
    </ul>
    <p>Bu veriler cihazınızda tutulur ve hiçbir zaman doğrudan üçüncü taraflarla paylaşılmaz.</p>

    <h2>5. Çerezleri Nasıl Kontrol Edebilirsiniz?</h2>
    <ul>
        <li><strong>Chrome:</strong> Ayarlar → Gizlilik ve Güvenlik → Çerezler ve diğer site verileri</li>
        <li><strong>Firefox:</strong> Ayarlar → Gizlilik ve Güvenlik → Çerezler</li>
        <li><strong>Safari:</strong> Tercihler → Gizlilik → Çerezleri Yönet</li>
        <li><strong>Mobil uygulama:</strong> Sistem Ayarları → Uygulamalar → RotaRehber → İzinler</li>
    </ul>
    <p><strong>Uyarı:</strong> Zorunlu çerezleri devre dışı bırakmanız, giriş yapma ve oturum sürdürme gibi temel işlevlerin çalışmamasına yol açabilir.</p>

    <h2>6. İletişim</h2>
    <p>Çerez politikamızla ilgili sorularınız için: <strong>kvkk@rotarehber.com</strong></p>

    <p class="update">Son güncelleme: 22 Nisan 2026 | YILMAZLAR BİLİŞİM – RotaRehber (Freemind)</p>
    <a href="index.php" class="back">← Ana Sayfaya Dön</a>
</div>
</div>
</body>
</html>
