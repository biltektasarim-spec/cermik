<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanım Şartları – RotaRehber</title>
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
        h3{font-size:.95rem;font-weight:600;color:#333;margin:14px 0 6px}
        p,li{font-size:.93rem;color:#444;margin-bottom:10px}
        ul{padding-left:22px;margin-bottom:10px}
        li{margin-bottom:6px}
        .warn-box{background:#fef2f2;border-left:4px solid #ef4444;padding:14px 18px;border-radius:0 8px 8px 0;margin:16px 0;font-size:.9rem;color:#7f1d1d}
        .info-box{background:#eff6ff;border-left:4px solid #3b82f6;padding:14px 18px;border-radius:0 8px 8px 0;margin:16px 0;font-size:.9rem;color:#1e3a8a}
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
        <span>KULLANIM ŞARTLARI</span>
    </div>

    <p><strong>Son Güncelleme:</strong> 22 Nisan 2026</p>

    <div class="info-box">
        Bu Kullanım Şartları ("Şartlar"), <strong>FreeMind</strong> () tarafından geliştirilen, <strong>Freemind</strong> markası altında sunulan <strong>RotaRehber</strong> mobil uygulaması ve web sitesini (rotarehber.com) kullanan tüm gerçek kişiler ("Kullanıcı") için geçerlidir. Uygulamayı veya web sitesini kullanarak bu Şartları kabul etmiş sayılırsınız.
    </div>

    <h2>1. Taraflar ve Kapsam</h2>
    <p>Bu Şartlar; RotaRehber mobil uygulaması (Android ve iOS), web sitesi (rotarehber.com) ve bunlara bağlı tüm API ve hizmetleri kapsamaktadır. Şartları kabul etmeyen kullanıcılar platformu kullanmamalıdır.</p>

    <h2>2. Hizmet Tanımı</h2>
    <p>RotaRehber; Türkiye'deki ilçe ve bölgelere ait turizm, kültür, eczane, sağlık, etkinlik, işletme ve yerel rehber bilgilerini sunan bir dijital bölge rehberi platformudur. Platform aşağıdaki hizmetleri içermektedir:</p>
    <ul>
        <li>Bölgesel turizm ve konaklama rehberi</li>
        <li>Anlık nöbetçi eczane listesi</li>
        <li>Yerel işletme rehberi ve işletme kaydı</li>
        <li>Etkinlik ve duyuru takibi</li>
        <li>Çek-Gönder (belediye şikayet/istek formu)</li>
        <li>Canlı yayın (anlık bilgi akışı)</li>
        <li>Push bildirimi ile anlık haberdar olma</li>
        <li>SMS ile kimlik doğrulama</li>
    </ul>

    <h2>3. Kullanıcı Kayıt ve Hesap Güvenliği</h2>
    <ul>
        <li>Kayıt olabilmek için geçerli bir cep telefon numarası ve ad-soyad bilgisi zorunludur.</li>
        <li>SMS ile gelen doğrulama kodu yalnızca size aittir; üçüncü şahıslarla paylaşılmamalıdır.</li>
        <li>Hesabınızın güvenliğinden siz sorumlusunuzdur. Yetkisiz erişim şüpheniz varsa derhal bildiriniz.</li>
        <li>Sahte kimlik bilgisi ile hesap açılması yasaktır ve hesap derhal kapatılır.</li>
    </ul>

    <h2>4. Kullanıcı Yükümlülükleri</h2>
    <p>Platformu kullanırken aşağıdakileri kabul edersiniz:</p>
    <ul>
        <li>Türkiye Cumhuriyeti yasalarına ve genel ahlak kurallarına aykırı içerik paylaşmamayı</li>
        <li>Başkalarının gizliliğini ihlal edecek içerik yüklememeyı</li>
        <li>Platform altyapısına zarar vermeye yönelik girişimlerde bulunmamayı (DoS, XSS, SQL enjeksiyonu vb.)</li>
        <li>Spam veya toplu mesaj gönderiminde platformun kullanılmamasını</li>
        <li>Fikri mülkiyet haklarına sahip içeriklerin izinsiz paylaşılmamasını</li>
        <li>Çek-Gönder formuna yalnızca gerçek, dürüst ve belediye hizmetleriyle ilgili bildirimler iletmeyi</li>
    </ul>

    <h2>5. Çek-Gönder Özelliği</h2>
    <p>Çek-Gönder; kullanıcıların yerel yönetimlere fotoğraf ve açıklama ile sorun veya öneri iletmesini sağlayan bir araçtır.</p>
    <div class="warn-box">
        Asılsız ihbar, hakaret veya iftira içerikli bildirimler; 5237 sayılı Türk Ceza Kanunu'nun ilgili maddeleri (iftira, hakaret, özel hayatın gizliliğini ihlal) kapsamında hukuki sorumluluk doğurabilir. Şirket, bu tür içerikleri kaldırma ve ilgili makamlara bildirme hakkını saklı tutar.
    </div>

    <h2>6. İşletme Kaydı</h2>
    <p>Platforma işletme kaydı yaptıran kullanıcılar:</p>
    <ul>
        <li>Gerçek ve güncel işletme bilgileri sağlamakla yükümlüdür.</li>
        <li>Yanıltıcı, müstehcen veya yasadışı içerik yayınlayamaz.</li>
        <li>Şirket, uygunluk denetimine tabi olmak üzere herhangi bir listeyi kaldırma hakkını saklı tutar.</li>
    </ul>

    <h2>7. Push Bildirimleri ve SMS</h2>
    <ul>
        <li>Push bildirimleri, platform tarafından gönderilen etkinlik ve duyuruları içerir. Ticari nitelikli değildir.</li>
        <li>SMS; yalnızca hesap güvenliği (OTP, şifre sıfırlama) amacıyla gönderilir.</li>
        <li>Reklam içerikli SMS gönderilmez.</li>
        <li>Bildirim tercihlerinizi uygulama ayarlarından veya cihaz ayarlarından yönetebilirsiniz.</li>
    </ul>

    <h2>8. Fikri Mülkiyet Hakları</h2>
    <p>RotaRehber adı, logosu, arayüz tasarımı, yazılım kodu ve içerik; <strong>FREEMIND / Freemind</strong>'e aittir. Tüm hakları saklıdır. İzin alınmaksızın kopyalanması, dağıtılması veya ticari amaçla kullanılması yasaktır.</p>

    <h2>9. Sorumluluk Sınırlaması</h2>
    <ul>
        <li>Platform, üçüncü taraf web sitelerine bağlantılar içerebilir; bu sitelerin içeriğinden sorumlu değiliz.</li>
        <li>Mücbir sebep, teknik arıza veya bakım süreçlerinden kaynaklanan hizmet kesintilerinden sorumluluk kabul edilmez.</li>
        <li>Kullanıcıların birbirleriyle veya işletmelerle gerçekleştirdiği işlemlerden Şirket sorumlu tutulamaz.</li>
        <li>Platformda yer alan eczane nöbet bilgileri resmi kaynaklardan alınmaktadır; anlık değişiklikler için yetkili sağlık kuruluşlarını teyit etmeniz önerilir.</li>
    </ul>

    <h2>10. Hesap Askıya Alma ve Sonlandırma</h2>
    <p>Şirket; bu Şartları ihlal eden, platformu kötüye kullanan veya üçüncü taraflara zarar veren kullanıcıların hesaplarını bildirim yapmaksızın askıya alabilir veya silebilir. Kullanıcı da dilediği zaman hesabını silme talebinde bulunabilir.</p>

    <h2>11. Değişiklikler</h2>
    <p>Şirket, bu Şartları önceden bildirmeksizin güncelleyebilir. Güncellemeler yayımlandığı tarihten itibaren geçerlidir. Platformu kullanmaya devam etmeniz değişiklikleri kabul ettiğiniz anlamına gelir.</p>

    <h2>12. Uygulanacak Hukuk ve Yetki</h2>
    <p>Bu Şartlar, <strong>Türkiye Cumhuriyeti hukukuna</strong> tabidir. Uyuşmazlıklarda <strong>Diyarbakır Mahkemeleri ve İcra Daireleri</strong> yetkilidir.</p>

    <h2>13. İletişim</h2>
    <p>Kullanım şartlarına ilişkin soru ve talepleriniz için: <strong>kvkk@rotarehber.com</strong></p>

    <p class="update">Son güncelleme: 22 Nisan 2026 | YILMAZLAR BİLİŞİM Bilgisayar ve Güvenlik Kamera Sistemleri – RotaRehber (Freemind)</p>
    <a href="index.php" class="back">← Ana Sayfaya Dön</a>
</div>
</div>
</body>
</html>
