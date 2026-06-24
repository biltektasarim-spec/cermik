<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gizlilik Politikası – RotaRehber</title>
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
        h2{font-size:1.1rem;font-weight:700;color:#0a6ebd;margin:32px 0 10px;padding-bottom:6px;border-bottom:1px solid #e8f0fe}
        h3{font-size:.95rem;font-weight:600;margin:18px 0 6px;color:#333}
        p,li{font-size:.93rem;color:#444;margin-bottom:10px}
        ul{padding-left:22px;margin-bottom:10px}
        li{margin-bottom:6px}
        table{width:100%;border-collapse:collapse;margin:14px 0;font-size:.88rem}
        th{background:#e8f0fe;color:#0a6ebd;padding:10px 14px;text-align:left;font-weight:600}
        td{padding:9px 14px;border-bottom:1px solid #f0f4f8;color:#444}
        .badge{display:inline-block;background:#e8f0fe;color:#0a6ebd;font-size:.78rem;font-weight:600;padding:2px 8px;border-radius:6px;margin-right:4px}
        .update{font-size:.82rem;color:#999;margin-top:40px;padding-top:16px;border-top:1px solid #eee}
        .back{display:inline-block;margin-top:28px;color:#0a6ebd;text-decoration:none;font-weight:600;font-size:.9rem}
        .back:hover{text-decoration:underline}
        @media(max-width:600px){.card{padding:20px 16px}}
    </style>
</head>
<body>
<div class="wrap">
<div class="card">
    <div class="brand">
        <h1>RotaRehber</h1>
        <span>GİZLİLİK POLİTİKASI</span>
    </div>

    <p><strong>Son Güncelleme:</strong> <?php echo date('d.m.Y'); ?></p>

    <p>Bu Gizlilik Politikası; <strong>FreeMind</strong> tarafından geliştirilen ve <strong>Freemind</strong> markası altında sunulan <strong>RotaRehber</strong> mobil uygulaması ve web platformunun (<em>rotarehber.com</em>) kişisel verilerinizi nasıl topladığını, kullandığını ve koruduğunu açıklamaktadır. 6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK) ile Avrupa Birliği Genel Veri Koruma Tüzüğü (GDPR) kapsamındaki yükümlülüklerimizi yerine getirmeyi taahhüt ederiz.</p>

    <h2>1. Veri Sorumlusu</h2>
    <table>
        <tr><th>Ünvan</th><td>FreeMind</td></tr>
        <tr><th>Marka / Uygulama</th><td>RotaRehber (Freemind)</td></tr>
        <tr><th>Web Sitesi</th><td>rotarehber.com</td></tr>
        <tr><th>E-posta</th><td>kvkk@rotarehber.com</td></tr>
    </table>

    <h2>2. Toplanan Kişisel Veriler</h2>
    <table>
        <tr><th>Veri Kategorisi</th><th>Toplanan Veriler</th><th>Toplama Yöntemi</th></tr>
        <tr><td><span class="badge">Kimlik</span></td><td>Ad, Soyad</td><td>Kullanıcı girişi / kayıt formu</td></tr>
        <tr><td><span class="badge">İletişim</span></td><td>Cep telefonu numarası, E-posta adresi</td><td>Kayıt formu, SMS doğrulama</td></tr>
        <tr><td><span class="badge">Konum</span></td><td>Anlık GPS koordinatları</td><td>Cihaz izni ile uygulama üzerinden</td></tr>
        <tr><td><span class="badge">Kullanım</span></td><td>Gezilen sayfalar, tıklama geçmişi, uygulama süresi</td><td>Firebase Analytics, otomatik</td></tr>
        <tr><td><span class="badge">Cihaz</span></td><td>FCM token, cihaz modeli, işletim sistemi</td><td>Otomatik (push bildirim servisi)</td></tr>
        <tr><td><span class="badge">Görsel/Dosya</span></td><td>Çek-Gönder formuna yüklenen fotoğraflar</td><td>Kullanıcı tarafından gönüllü yükleme</td></tr>
    </table>

    <h2>3. Kişisel Verilerin İşlenme Amaçları</h2>
    <ul>
        <li>RotaRehber uygulama ve web hizmetlerinin sunulması ve geliştirilmesi</li>
        <li>Kullanıcı hesabı oluşturulması ve kimlik doğrulaması (SMS OTP ile)</li>
        <li>Konuma dayalı içerik ve işletme önerilerinin sunulması</li>
        <li>Push bildirimi gönderimi (Firebase Cloud Messaging)</li>
        <li>Müşteri hizmetleri ve taleplerin yanıtlanması</li>
        <li>Yasal yükümlülüklerin yerine getirilmesi</li>
        <li>Güvenlik, dolandırıcılık ve kötüye kullanım önleme</li>
        <li>Analitik ve istatistiksel değerlendirme (anonim hale getirilerek)</li>
    </ul>

    <h2>4. SMS (Kısa Mesaj) Gönderimi</h2>
    <p>RotaRehber, aşağıdaki amaçlarla cep telefon numaranıza SMS gönderebilir:</p>
    <ul>
        <li><strong>Kimlik Doğrulama (OTP):</strong> Kayıt ve giriş işlemlerinde tek kullanımlık doğrulama kodu iletimi.</li>
        <li><strong>Şifre Sıfırlama:</strong> Hesabınıza erişim kaybetmeniz durumunda güvenli yenileme kodu.</li>
    </ul>
    <p>SMS gönderimi; <strong>6698 sayılı KVKK Madde 5/2-f (meşru menfaat)</strong> ve <strong>6563 sayılı Elektronik Ticaretin Düzenlenmesi Hakkında Kanun</strong> kapsamında, münhasıran kimlik doğrulama amacıyla gerçekleştirilmektedir. Telefon numaranız reklam veya pazarlama amaçlı kullanılmamaktadır.</p>

    <h2>5. Push Bildirimleri</h2>
    <p>Uygulamayı indirip izin vermeniz halinde, Firebase Cloud Messaging (FCM) altyapısı üzerinden size bildirim gönderilebilir. Bu bildirimler; etkinlik duyuruları, önemli güncellemeler ve bölgesel haberler içerebilir. Bildirim izinlerinizi cihaz ayarlarından her zaman değiştirebilirsiniz.</p>

    <h2>6. Konum Verisi</h2>
    <p>Uygulamayı kullanırken izin vermeniz halinde anlık konumunuz; en yakın işletmelerin, eczanelerin veya hizmetlerin listelenmesi amacıyla yalnızca uygulama aktifken kullanılır. Konum veriniz sunucularımızda depolanmaz ve üçüncü taraflarla paylaşılmaz.</p>

    <h2>7. Kişisel Verilerin Aktarılması</h2>
    <h3>Yurt İçi</h3>
    <p>Verileriniz; yasal zorunluluklar çerçevesinde yetkili kamu kurum ve kuruluşlarıyla paylaşılabilir.</p>
    <h3>Yurt Dışı</h3>
    <table>
        <tr><th>Hizmet Sağlayıcı</th><th>Aktarılan Veri</th><th>Amaç</th></tr>
        <tr><td>Google Firebase (ABD)</td><td>FCM token, analitik</td><td>Push bildirim, analiz</td></tr>
        <tr><td>Google Maps (ABD)</td><td>Konum verisi</td><td>Harita gösterimi</td></tr>
    </table>
    <p>Yurt dışına aktarım, KVKK Madde 9 kapsamında, Kişisel Verileri Koruma Kurulu kararlarına ve yeterli koruma güvencelerine uygun olarak yapılmaktadır.</p>

    <h2>8. Veri Saklama Süreleri</h2>
    <ul>
        <li>Kullanıcı hesap verileri: Hesap silme talebine kadar</li>
        <li>SMS kayıtları: 1 yıl</li>
        <li>Analitik veriler: 26 ay (anonimleştirilmiş)</li>
        <li>Çek-Gönder görselleri: İlgili başvuru kapandıktan itibaren 1 yıl</li>
        <li>Yasal yükümlülük gerektiren veriler: İlgili mevzuatta öngörülen süre</li>
    </ul>

    <h2>9. KVKK Kapsamındaki Haklarınız</h2>
    <p>KVKK'nın 11. maddesi uyarınca aşağıdaki haklara sahipsiniz:</p>
    <ul>
        <li>Kişisel verilerinizin işlenip işlenmediğini öğrenme</li>
        <li>İşlenmişse buna ilişkin bilgi talep etme</li>
        <li>İşlenme amacını ve bunların amacına uygun kullanılıp kullanılmadığını öğrenme</li>
        <li>Yurt içinde veya yurt dışında aktarıldığı üçüncü kişileri bilme</li>
        <li>Eksik veya yanlış işlenmişse düzeltilmesini isteme</li>
        <li>KVKK Madde 7 çerçevesinde silinmesini veya yok edilmesini isteme</li>
        <li>Düzeltme ve silme işlemlerinin üçüncü kişilere bildirilmesini isteme</li>
        <li>İşlenen verilerin münhasıran otomatik sistemler vasıtasıyla analiz edilmesi sonucuna itiraz etme</li>
        <li>Kanuna aykırı işleme nedeniyle zarara uğraması hâlinde zararın giderilmesini talep etme</li>
    </ul>
    <p>Bu haklarınızı kullanmak için <strong>kvkk@rotarehber.com</strong> adresine yazılı başvuruda bulunabilirsiniz.</p>

    <h2>10. Veri Güvenliği</h2>
    <p>Kişisel verileriniz; SSL/TLS şifrelemesi, güvenli sunucu altyapısı, erişim kontrolü ve düzenli güvenlik denetimleri ile korunmaktadır. Veri ihlali durumunda KVKK'nın öngördüğü süreler içinde Kurul'a ve etkilenen kişilere bildirim yapılacaktır.</p>

    <h2>11. Politika Değişiklikleri</h2>
    <p>Bu politikada yapılacak değişiklikler bu sayfada yayımlanacak ve uygulama üzerinden bildirilecektir. Güncel versiyonu takip etmenizi tavsiye ederiz.</p>

    <p class="update">Son güncelleme: <?php echo date('d F Y', strtotime('2026-04-22')); ?> | YILMAZLAR BİLİŞİM – RotaRehber (Freemind)</p>
    <a href="index.php" class="back">← Ana Sayfaya Dön</a>
</div>
</div>
</body>
</html>
