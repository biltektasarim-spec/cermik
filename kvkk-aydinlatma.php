<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KVKK Aydınlatma Metni – RotaRehber</title>
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
        .law-box{background:#fffbea;border-left:4px solid #f59e0b;padding:14px 18px;border-radius:0 8px 8px 0;margin:16px 0;font-size:.9rem}
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
        <span>KVKK AYDINLATMA METNİ</span>
    </div>

    <div class="law-box">
        Bu aydınlatma metni, 6698 sayılı <strong>Kişisel Verilerin Korunması Kanunu'nun ("KVKK") 10. maddesi</strong> ve <em>Aydınlatma Yükümlülüğünün Yerine Getirilmesinde Uyulacak Usul ve Esaslar Hakkında Tebliğ</em> çerçevesinde hazırlanmıştır.
    </div>

    <h2>Veri Sorumlusu</h2>
    <table>
        <tr><th>Ünvan</th><td>FreeMind</td></tr>
        <tr><th>Marka</th><td>RotaRehber / Freemind</td></tr>
        <tr><th>Web Sitesi</th><td>rotarehber.com</td></tr>
        <tr><th>İletişim</th><td>kvkk@rotarehber.com</td></tr>
    </table>

    <h2>1. İşlenen Kişisel Veriler ve İşlenme Amaçları</h2>
    <table>
        <tr>
            <th>Kişisel Veri</th>
            <th>İşlenme Amacı</th>
            <th>Hukuki Dayanak (KVKK Md.5)</th>
        </tr>
        <tr>
            <td>Ad, Soyad</td>
            <td>Kullanıcı hesabı oluşturma, profil yönetimi</td>
            <td>Sözleşmenin ifası (Md.5/2-c)</td>
        </tr>
        <tr>
            <td>Cep Telefonu Numarası</td>
            <td>SMS ile kimlik doğrulama (OTP), hesap güvenliği, şifre sıfırlama</td>
            <td>Sözleşmenin ifası (Md.5/2-c); Meşru menfaat (Md.5/2-f)</td>
        </tr>
        <tr>
            <td>E-posta Adresi</td>
            <td>Hesap bildirimleri, şifre sıfırlama</td>
            <td>Sözleşmenin ifası (Md.5/2-c)</td>
        </tr>
        <tr>
            <td>Konum Bilgisi (GPS)</td>
            <td>Yakın işletme/hizmet listeleme, harita gösterimi</td>
            <td>Açık rıza (Md.5/1)</td>
        </tr>
        <tr>
            <td>FCM Token / Cihaz Bilgisi</td>
            <td>Anlık push bildirim gönderimi</td>
            <td>Açık rıza (Md.5/1)</td>
        </tr>
        <tr>
            <td>Fotoğraf / Görsel (Çek-Gönder)</td>
            <td>Belediye hizmetleri için şikayet/talep iletimi</td>
            <td>Açık rıza (Md.5/1)</td>
        </tr>
        <tr>
            <td>Kullanım ve Analitik Verileri</td>
            <td>Hizmet kalitesi ve uygulama geliştirme</td>
            <td>Meşru menfaat (Md.5/2-f)</td>
        </tr>
    </table>

    <h2>2. SMS Gönderimi Hakkında Aydınlatma</h2>
    <p>RotaRehber uygulaması, kullanıcı güvenliğini sağlamak amacıyla cep telefon numaranıza <strong>yalnızca aşağıdaki durumlarda</strong> SMS göndermektedir:</p>
    <ul>
        <li><strong>Kayıt / Giriş Doğrulaması:</strong> Hesabınıza erişim sağlarken kimliğinizi doğrulamak için tek kullanımlık kod (OTP).</li>
        <li><strong>Şifre Sıfırlama:</strong> Hesabınızı yeniden güvenli hale getirmeniz amacıyla kod iletimi.</li>
    </ul>
    <p>Bu SMS gönderimlerinin hukuki dayanağı; KVKK Madde 5/2-c (sözleşmenin ifası) ile Madde 5/2-f'dir (veri sorumlusunun meşru menfaati). Söz konusu mesajlar ticari elektronik ileti niteliği taşımamakta; <strong>6563 sayılı Elektronik Ticaretin Düzenlenmesi Hakkında Kanun</strong> kapsamında reklam veya tanıtım içermemektedir.</p>

    <h2>3. Kişisel Verilerin Aktarılması</h2>
    <p>Kişisel verileriniz aşağıdaki taraflarla KVKK'nın 8. ve 9. maddeleri kapsamında paylaşılabilir:</p>
    <ul>
        <li><strong>Yetkili Kamu Kurumları:</strong> Yasal yükümlülükler gereği (mahkeme kararı, savcılık talebi vb.)</li>
        <li><strong>Google LLC (Firebase):</strong> Push bildirim altyapısı ve analitik hizmetleri – yurt dışı aktarım</li>
        <li><strong>SMS Hizmet Sağlayıcısı:</strong> OTP gönderimi – yalnızca telefon numarası iletilmektedir</li>
    </ul>
    <p>Yurt dışına veri aktarımında, aktarım yapılan ülkenin yeterli koruma sağladığına dair Kişisel Verileri Koruma Kurulu kararları veya standart sözleşme maddeleri esas alınmaktadır.</p>

    <h2>4. Kişisel Verilerin Toplanma Yöntemi</h2>
    <p>Verileriniz aşağıdaki kanallar vasıtasıyla toplanmaktadır:</p>
    <ul>
        <li>RotaRehber mobil uygulaması (Android / iOS)</li>
        <li>rotarehber.com web sitesi ve alt sayfaları</li>
        <li>Kullanıcı tarafından doldurulan formlar (kayıt, profil, Çek-Gönder)</li>
        <li>Otomatik teknik yöntemler (Firebase Analytics, cihaz sensörleri)</li>
    </ul>

    <h2>5. Kişisel Veri Sahibinin Hakları (KVKK Madde 11)</h2>
    <p>Veri sahibi olarak aşağıdaki haklara sahipsiniz:</p>
    <ul>
        <li>Kişisel verilerinizin işlenip işlenmediğini öğrenme</li>
        <li>İşlenmişse buna ilişkin bilgi talep etme</li>
        <li>Eksik veya yanlış işlenmişse düzeltilmesini isteme</li>
        <li>Kanun ve ilgili diğer kanun hükümlerine uygun olarak silinmesini veya yok edilmesini isteme</li>
        <li>Düzeltme, silme veya yok etme işlemlerinin üçüncü kişilere bildirilmesini isteme</li>
        <li>İşlenen verilerin münhasıran otomatik sistemler vasıtasıyla analiz edilmesi nedeniyle aleyhinize bir sonucun ortaya çıkmasına itiraz etme</li>
        <li>Kanuna aykırı işleme nedeniyle zarara uğramanız hâlinde zararınızın giderilmesini talep etme</li>
    </ul>

    <h2>6. Başvuru Yöntemi</h2>
    <p>Yukarıdaki haklarınızı kullanmak için;</p>
    <ul>
        <li><strong>E-posta:</strong> kvkk@rotarehber.com adresine kimliğinizi doğrulayan bir belgeyle birlikte yazılı başvuru yapabilirsiniz.</li>
        <li>Başvurunuz, <strong>30 gün</strong> içinde ücretsiz olarak yanıtlanacaktır (KVKK Madde 13).</li>
        <li>Yanıtımızın yetersiz bulunması halinde <strong>Kişisel Verileri Koruma Kurumu'na (KVKK)</strong> şikayette bulunma hakkınız saklıdır.</li>
    </ul>

    <p class="update">Son güncelleme: 22 Nisan 2026 | YILMAZLAR BİLİŞİM – RotaRehber (Freemind)</p>
    <a href="index.php" class="back">← Ana Sayfaya Dön</a>
</div>
</div>
</body>
</html>
