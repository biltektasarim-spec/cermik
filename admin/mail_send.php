<?php
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

if ($_SESSION['admin_role'] !== 'SUPER_ADMIN' || ($_SESSION['admin_district_id'] ?? 0) != 0) {
    die("Bu sayfaya erişim yetkiniz yok. Sadece Süper Admin toplu mail gönderebilir.");
}

// ─── SMTP Port Fix (Testing 465 instead of 443 if requested)
// define('SMTP_PORT', 465); // Standard SSL port

// ─── PHPMailer (gelişmiş SMTP) olmadan PHP'nin mail() fonksiyonu yerine
// cURL ile SMTP gönderimi - AppServ'de PHPMailer kurulu olmayabilir
// Bu dosya PHPMailer'ı destekler, yoksa yerleşik SMTP bağlantısı kullanır

// ─── Mail Ayarları ────────────────────────────────────────────────────────────
define('SMTP_HOST',     'mail.rotamcermik.com');
define('SMTP_PORT',     465);
define('SMTP_SECURE',   'ssl');   // 465 portu standart SSL/SMTP portudur
define('SMTP_USER',     'admin@rotamcermik.com');
define('SMTP_PASS',     'CumaYilmaz.21');
define('SMTP_FROM',     'admin@rotamcermik.com');
define('SMTP_FROM_NAME','Çermik Belediyesi Rehberi');

// ─── Gönderim İşlemi ─────────────────────────────────────────────────────────
$success_msg = '';
$error_msg   = '';
$send_result = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_mail'])) {
    csrf_verify();

    $subject  = trim($_POST['subject']  ?? '');
    $body_tpl = $_POST['body']          ?? '';
    $target   = $_POST['target']        ?? 'all'; // all | active

    if ($subject === '' || trim(strip_tags($body_tpl)) === '') {
        $error_msg = 'Konu ve içerik boş bırakılamaz.';
    } else {
        // Alıcıları çek
        $where = $target === 'active' ? 'WHERE is_active = 1' : '';
        $recipients = $pdo->query("SELECT id, email, full_name FROM users {$where} ORDER BY email ASC")->fetchAll();

        if (empty($recipients)) {
            $error_msg = 'Gönderilecek üye bulunamadı.';
        } else {
            $sent    = 0;
            $failed  = 0;
            $errors  = [];

            foreach ($recipients as $user) {
                // İsim placeholder değiştir
                $name        = !empty($user['full_name']) ? $user['full_name'] : 'Sayın Üyemiz';
                $personalized = str_replace('[İsim]', $name, $body_tpl);

                // HTML e-posta şablonuna sar
                $html = buildEmailHtml($subject, $personalized, $name);

                $ok = sendSmtpMail($user['email'], $name, $subject, $html);
                if ($ok === true) {
                    $sent++;
                } else {
                    $failed++;
                    $errors[] = $user['email'] . ': ' . $ok;
                }
            }

            // Log kaydet
            $pdo->prepare("INSERT INTO mail_logs (subject, body_preview, recipient_count, sent_by) VALUES (?, ?, ?, ?)")
                ->execute([$subject, mb_substr(strip_tags($body_tpl), 0, 200), $sent, 'admin']);

            $success_msg = "✅ {$sent} üyeye başarıyla gönderildi.";
            if ($failed > 0) {
                $error_msg = "⚠️ {$failed} adrese gönderilemedi.";
            }
            $send_result = $errors;
        }
    }
}

// ─── Gönderim Geçmişi ────────────────────────────────────────────────────────
$mail_logs = $pdo->query("SELECT * FROM mail_logs ORDER BY sent_at DESC LIMIT 20")->fetchAll();

// Kullanıcı sayıları
$total_users  = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$active_users = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();

// ─── SMTP Gönderim Fonksiyonu (PHPMailer olmadan) ─────────────────────────────
function sendSmtpMail(string $toEmail, string $toName, string $subject, string $htmlBody): bool|string {
    // PHPMailer varsa kullan
    $phpmailer_path = __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    if (file_exists($phpmailer_path)) {
        return sendWithPHPMailer($toEmail, $toName, $subject, $htmlBody, $phpmailer_path);
    }

    // PHPMailer yoksa PHP stream socket ile SMTP
    return sendWithSocket($toEmail, $toName, $subject, $htmlBody);
}

function sendWithPHPMailer(string $toEmail, string $toName, string $subject, string $htmlBody, string $path): bool|string {
    require_once $path;
    require_once dirname($path) . '/Exception.php';
    require_once dirname($path) . '/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_PORT == 465 ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);
        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}

function sendWithSocket(string $toEmail, string $toName, string $subject, string $htmlBody): bool|string {
    $host = SMTP_HOST;
    $port = (int)SMTP_PORT;
    $user = base64_encode(SMTP_USER);
    $pass = base64_encode(SMTP_PASS);

    // SSL bağlantısı
    $context = stream_context_create(['ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true,
    ]]);

    $scheme = ($port == 465 || $port == 443) ? 'ssl' : 'tcp';
    $fp = @stream_socket_client("{$scheme}://{$host}:{$port}", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
    if (!$fp) return "Bağlantı hatası: {$errstr} ({$errno})";

    $read  = fgets($fp, 1024);

    $cmds = [
        "EHLO " . gethostname() . "\r\n",
        "AUTH LOGIN\r\n",
        $user . "\r\n",
        $pass . "\r\n",
        "MAIL FROM:<" . SMTP_FROM . ">\r\n",
        "RCPT TO:<{$toEmail}>\r\n",
        "DATA\r\n",
    ];

    foreach ($cmds as $cmd) {
        fwrite($fp, $cmd);
        $r = fgets($fp, 1024);
        if ($cmd === "DATA\r\n" && substr($r, 0, 3) !== '354') {
            fclose($fp);
            return "DATA hatası: {$r}";
        }
        if ($cmd !== "DATA\r\n" && substr($r, 0, 1) !== '2' && substr($r, 0, 1) !== '3') {
            fclose($fp);
            return "SMTP hatası ({$cmd}): {$r}";
        }
    }

    // Mail başlıkları
    $boundary = md5(time());
    $headers  = "From: =?UTF-8?B?" . base64_encode(SMTP_FROM_NAME) . "?= <" . SMTP_FROM . ">\r\n";
    $headers .= "To: =?UTF-8?B?" . base64_encode($toName) . "?= <{$toEmail}>\r\n";
    $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: base64\r\n";

    fwrite($fp, $headers . "\r\n" . chunk_split(base64_encode($htmlBody)) . "\r\n.\r\n");
    $r = fgets($fp, 1024);

    fwrite($fp, "QUIT\r\n");
    fclose($fp);

    if (substr($r, 0, 1) !== '2') return "Gönderim hatası: {$r}";
    return true;
}

// ─── HTML E-posta Şablonu ─────────────────────────────────────────────────────
function buildEmailHtml(string $subject, string $bodyContent, string $name): string {
    // Basit HTML satır sonu dönüşümü
    $bodyHtml = nl2br(htmlspecialchars($bodyContent, ENT_QUOTES, 'UTF-8'));

    return <<<HTML
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$subject}</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f9; font-family: 'Segoe UI', Arial, sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9; padding: 30px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
      
      <!-- Header -->
      <tr>
        <td style="background: linear-gradient(135deg, #1a3a5c 0%, #2d6a9f 100%); padding: 35px 40px; text-align:center;">
          <h1 style="color:#ffffff; margin:0; font-size:26px; font-weight:700; letter-spacing:1px;">🗺️ Çermik Rehberi</h1>
          <p style="color:rgba(255,255,255,0.8); margin:8px 0 0; font-size:14px;">rotamcermik.com</p>
        </td>
      </tr>

      <!-- Body -->
      <tr>
        <td style="padding: 40px; color:#333333; font-size:15px; line-height:1.7;">
          {$bodyHtml}
        </td>
      </tr>

      <!-- CTA Button -->
      <tr>
        <td style="padding: 0 40px 30px; text-align:center;">
          <a href="https://rotamcermik.com" style="display:inline-block; background: linear-gradient(135deg, #e67e22, #d35400);
             color:#ffffff; text-decoration:none; padding:14px 40px; border-radius:8px;
             font-weight:700; font-size:15px; letter-spacing:0.5px;">
            🌐 Siteyi Ziyaret Et
          </a>
        </td>
      </tr>

      <!-- Footer -->
      <tr>
        <td style="background:#f8f9fb; padding: 25px 40px; text-align:center; border-top: 1px solid #eee;">
          <p style="margin:0; color:#888; font-size:12px; line-height:1.8;">
            Bu e-posta <strong>Çermik Belediyesi</strong> tarafından <strong>rotamcermik.com</strong> üzerinden gönderilmiştir.<br>
            © 2024 Çermik Belediyesi — Tüm hakları saklıdır.<br>
            <a href="mailto:admin@rotamcermik.com" style="color:#2d6a9f; text-decoration:none;">admin@rotamcermik.com</a>
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toplu Mail Gönder - Çermik Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .mail-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2rem; }
        .stat-mini { background: var(--card-bg, #fff); border-radius: 10px; padding: 1.2rem; text-align: center; border: 1px solid #eee; }
        .stat-mini .num { font-size: 2rem; font-weight: 800; color: #2d6a9f; }
        .stat-mini .lbl { font-size: 0.8rem; color: #888; margin-top: 4px; }
        .mail-preview { background: #f8f9fb; border: 1px solid #e2e8f0; border-radius: 10px;
                        padding: 20px; margin-top: 15px; font-size: 0.85rem; color: #444; display: none; }
        .mail-preview.show { display: block; }
        .editor-toolbar { display: flex; gap: 6px; margin-bottom: 8px; flex-wrap: wrap; }
        .editor-toolbar button { padding: 5px 10px; border: 1px solid #ddd; border-radius: 5px;
                                  background: #fff; cursor: pointer; font-size: 0.8rem; }
        .editor-toolbar button:hover { background: #f0f4ff; color: #2d6a9f; }
        #body { min-height: 280px; font-family: 'Segoe UI', sans-serif; font-size: 14px; line-height: 1.7; }
        .log-table { font-size: 0.85rem; }
        .recipient-select { display: flex; gap: 15px; margin-bottom: 15px; }
        .recipient-option { display: flex; align-items: center; gap: 8px; cursor: pointer; 
                            padding: 10px 16px; border: 2px solid #e2e8f0; border-radius: 8px; }
        .recipient-option input:checked + span { color: #2d6a9f; font-weight: 700; }
        .recipient-option:has(input:checked) { border-color: #2d6a9f; background: #f0f4ff; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1><i class="fa-solid fa-paper-plane"></i> Toplu E-posta Gönder</h1>
                <p style="color: var(--text-muted);">Tüm üyelere veya aktif üyelere toplu bilgilendirme e-postası gönderin.</p>
            </div>
        </header>

        <main class="page-content">

            <!-- İstatistikler -->
            <div class="mail-stats">
                <div class="stat-mini">
                    <div class="num"><?php echo $total_users; ?></div>
                    <div class="lbl"><i class="fa-solid fa-users"></i> Toplam Üye</div>
                </div>
                <div class="stat-mini">
                    <div class="num"><?php echo $active_users; ?></div>
                    <div class="lbl"><i class="fa-solid fa-circle-check" style="color:#22c55e;"></i> Aktif Üye</div>
                </div>
                <div class="stat-mini">
                    <div class="num"><?php echo count($mail_logs); ?></div>
                    <div class="lbl"><i class="fa-solid fa-envelope-circle-check" style="color:#f59e0b;"></i> Gönderim Kaydı</div>
                </div>
            </div>

            <?php if ($success_msg): ?>
            <div style="background:#d1fae5; border:1px solid #6ee7b7; color:#065f46; padding:12px 18px; border-radius:8px; margin-bottom:16px; font-weight:600;">
                <i class="fa-solid fa-circle-check"></i> <?php echo e($success_msg); ?>
            </div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
            <div style="background:#fee2e2; border:1px solid #fca5a5; color:#7f1d1d; padding:12px 18px; border-radius:8px; margin-bottom:16px;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?php echo e($error_msg); ?>
                <?php if (!empty($send_result)): ?>
                <details style="margin-top: 8px;"><summary style="cursor:pointer; font-size:0.8rem;">Hata detayları</summary>
                <ul style="font-size:0.8rem; margin:5px 0 0 15px;">
                    <?php foreach (array_slice($send_result, 0, 10) as $err): ?>
                    <li><?php echo e($err); ?></li>
                    <?php endforeach; ?>
                </ul></details>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 380px; gap: 2rem; align-items: start;">

                <!-- Mail Formu -->
                <div class="card">
                    <h3 style="margin-bottom: 1.5rem;"><i class="fa-solid fa-envelope-open-text" style="color:#2d6a9f;"></i> Mail İçeriği</h3>
                    <form method="POST" id="mailForm">
                        <?php echo csrf_field(); ?>

                        <!-- Alıcı Seçimi -->
                        <label style="display:block; font-weight:600; margin-bottom:8px;">Alıcı Grubu</label>
                        <div class="recipient-select">
                            <label class="recipient-option">
                                <input type="radio" name="target" value="all" checked>
                                <span><i class="fa-solid fa-users"></i> Tüm Üyeler (<?php echo $total_users; ?>)</span>
                            </label>
                            <label class="recipient-option">
                                <input type="radio" name="target" value="active">
                                <span><i class="fa-solid fa-user-check"></i> Sadece Aktifler (<?php echo $active_users; ?>)</span>
                            </label>
                        </div>

                        <!-- Konu -->
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-weight:600; margin-bottom:6px;">E-posta Konusu</label>
                            <input type="text" name="subject" id="subject" required maxlength="200"
                                   value="<?php echo e($_POST['subject'] ?? 'Çermik Rehberi\'ne Hoş Geldiniz – Keşfetmeye Başlayın!'); ?>"
                                   style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; box-sizing:border-box; font-size:14px;">
                        </div>

                        <!-- İçerik -->
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-weight:600; margin-bottom:6px;">
                                E-posta İçeriği
                                <span style="font-size:0.75rem; font-weight:400; color:#888; margin-left:8px;">[İsim] yazını alıcının adıyla değiştirir</span>
                            </label>
                            <div class="editor-toolbar">
                                <button type="button" onclick="insertText('[İsim]')"><i class="fa-solid fa-user"></i> İsim Ekle</button>
                                <button type="button" onclick="insertText('\n\n')">Paragraf</button>
                                <button type="button" onclick="loadTemplate('welcome')"><i class="fa-solid fa-wand-magic-sparkles"></i> Hoş Geldin Şablonu</button>
                                <button type="button" onclick="loadTemplate('info')"><i class="fa-solid fa-circle-info"></i> Bilgilendirme</button>
                                <button type="button" onclick="togglePreview()"><i class="fa-solid fa-eye"></i> Önizle</button>
                            </div>
                            <textarea name="body" id="body" required
                                      style="width:100%; min-height:280px; padding:12px; border:1px solid #ddd; border-radius:8px;
                                             box-sizing:border-box; font-size:14px; line-height:1.7; resize:vertical;"
                                      placeholder="Mail içeriğini buraya yazın..."><?php echo e($_POST['body'] ?? "Sayın [İsim],\n\nÇermik Belediyesi olarak sunduğumuz rotamcermik.com platformuna üye olduğunuz için teşekkür ederiz.\n\nÇermik Rehberi ile artık tek tıkla:\n\n🏨 Oteller ve konaklamaları keşfedebilir,\n🍽️ Restoranları ve kafeleri inceleyebilir,\n♨️ Termal kaplıcaları planlayabilir,\n🏛️ Tarihi ve kültürel mekânları bulabilir,\n🏥 Sağlık hizmetleri ve eczanelere ulaşabilir,\n🗺️ Harita üzerinden yol tarifi alabilirsiniz.\n\nÇermik'i ve güzelliklerini keşfetmek için sizi rotamcermik.com adresine bekliyoruz.\n\nHer türlü görüş ve öneriniz için bizimle iletişime geçebilirsiniz.\n\nSaygılarımızla,\nÇermik Belediyesi\nadmin@rotamcermik.com"); ?></textarea>
                        </div>

                        <!-- Önizleme Alanı -->
                        <div class="mail-preview" id="preview">
                            <strong style="display:block; margin-bottom:8px; color:#2d6a9f;">📧 Önizleme:</strong>
                            <div id="preview-content"></div>
                        </div>

                        <button type="submit" name="send_mail" class="btn btn-primary" style="width:100%; margin-top:15px; padding:14px;"
                                onclick="return confirm('Bu maili göndermek istediğinize emin misiniz?')">
                            <i class="fa-solid fa-paper-plane"></i> Gönder
                        </button>
                    </form>
                </div>

                <!-- Gönderim Geçmişi -->
                <div class="card">
                    <h3 style="margin-bottom: 1rem;"><i class="fa-solid fa-clock-rotate-left" style="color:#f59e0b;"></i> Gönderim Geçmişi</h3>
                    <?php if (empty($mail_logs)): ?>
                        <p style="color:#888; font-size:0.85rem; text-align:center; padding:20px;">Henüz gönderim yapılmamış.</p>
                    <?php else: ?>
                    <div style="overflow-y:auto; max-height:500px;">
                        <?php foreach ($mail_logs as $log): ?>
                        <div style="padding:12px; border-bottom:1px solid #f0f0f0;">
                            <div style="font-weight:600; font-size:0.85rem; color:#333; margin-bottom:4px;">
                                <?php echo e($log['subject']); ?>
                            </div>
                            <div style="font-size:0.75rem; color:#888; margin-bottom:4px; font-style:italic;">
                                <?php echo mb_strimwidth(e($log['body_preview']), 0, 80, '...'); ?>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="background:#dbeafe; color:#1e40af; padding:2px 8px; border-radius:20px; font-size:0.72rem; font-weight:700;">
                                    <i class="fa-solid fa-envelope"></i> <?php echo $log['recipient_count']; ?> alıcı
                                </span>
                                <span style="font-size:0.72rem; color:#aaa;">
                                    <?php echo date('d.m.Y H:i', strtotime($log['sent_at'])); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>

    <script>
    // Metin ekleme
    function insertText(text) {
        const ta = document.getElementById('body');
        const start = ta.selectionStart;
        const val   = ta.value;
        ta.value = val.substring(0, start) + text + val.substring(ta.selectionEnd);
        ta.selectionStart = ta.selectionEnd = start + text.length;
        ta.focus();
    }

    // Önizleme
    function togglePreview() {
        const prev = document.getElementById('preview');
        const body = document.getElementById('body').value;
        const subj = document.getElementById('subject').value;
        document.getElementById('preview-content').innerHTML =
            '<strong>' + escHtml(subj) + '</strong><hr style="margin:8px 0;">' +
            escHtml(body).replace(/\n/g, '<br>');
        prev.classList.toggle('show');
    }

    function escHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // Şablonlar
    const templates = {
        welcome: `Sayın [İsim],

Çermik Belediyesi olarak sunduğumuz rotamcermik.com platformuna üye olduğunuz için teşekkür ederiz.

Çermik Rehberi ile artık tek tıkla:

🏨 Oteller ve konaklamaları keşfedebilir,
🍽️ Restoranları ve kafeleri inceleyebilir,
♨️ Termal kaplıcaları planlayabilir,
🏛️ Tarihi ve kültürel mekânları bulabilir,
🏥 Sağlık hizmetleri ve eczanelere ulaşabilir,
🗺️ Harita üzerinden yol tarifi alabilirsiniz.

Çermik'i ve güzelliklerini keşfetmek için sizi rotamcermik.com adresine bekliyoruz.

Her türlü görüş ve öneriniz için bizimle iletişime geçebilirsiniz.

Saygılarımızla,
Çermik Belediyesi
admin@rotamcermik.com`,

        info: `[İsim],

Çermik Rehberi üzerinden sizinle önemli bir bilgiyi paylaşmak istedik.

[Buraya bilgilendirme içeriğinizi yazın...]

Herhangi bir sorunuz için admin@rotamcermik.com adresine yazabilirsiniz.

Saygılarımızla,
Çermik Belediyesi`
    };

    function loadTemplate(key) {
        if (templates[key]) {
            document.getElementById('body').value = templates[key];
        }
    }
    </script>
</body>
</html>
