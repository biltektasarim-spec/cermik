<?php
require_once __DIR__ . '/../config.php';

class MailService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        
        // Define SMTP settings if not defined
        if (!defined('SMTP_HOST')) define('SMTP_HOST',     'mail.rotamcermik.com');
        if (!defined('SMTP_PORT')) define('SMTP_PORT',     443);
        if (!defined('SMTP_SECURE')) define('SMTP_SECURE',   'ssl');
        if (!defined('SMTP_USER')) define('SMTP_USER',     'admin@rotamcermik.com');
        if (!defined('SMTP_PASS')) define('SMTP_PASS',     'CumaYilmaz.21');
        if (!defined('SMTP_FROM')) define('SMTP_FROM',     'admin@rotamcermik.com');
        if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME','ROTAREHBER');
    }

    public function sendWelcomeEmail($toEmail, $toName) {
        $subject = 'Şehrin Kalbi Artık Cebinizde! 🥳 Diyarbakır’ı Keşfetmeye Hazır mısın?';
        $body = "Merhaba Değerli Hemşehrimiz ve Diyarbakır Tutkunu,

Sana harika bir müjdemiz var! Binlerce yıllık tarihiyle, eşsiz lezzetleriyle ve bitmek bilmeyen enerjisiyle sevdamız Diyarbakır’ı karış karış keşfetmen için hazırladığımız yerel rehber uygulamamız artık yayında! 🚀

Bu sadece bir uygulama değil; kadim Sur sokaklarından modern yaşam alanlarına kadar bu şehrin her köşesini seninle buluşturan, Diyarbakır'ı yeniden tanımanı sağlayacak dijital bir yol arkadaşı.

🤝 Gücümüzü Yerel Yönetimlerimizden Alıyoruz!
Sana en doğru, en güncel ve en güvenilir bilgileri sunabilmek için yerel yönetimlerimizle el ele verdik. Belediyelerimizle yürüttüğümüz bu ortak çalışma sayesinde; şehrimizdeki tüm etkinliklerden, yeni açılan mekanlardan ve resmi duyurulardan anında haberin olacak. Şehrin yönetim gücüyle rehberimizin dinamizmini senin için birleştirdik!

Seni Neler Bekliyor?
Gizli Lezzet Durakları: Sadece yerlilerin bildiği o meşhur ciğerciler, fırınlar ve en tatlı duraklar.

Kültür ve Sanat: Konserler, sergiler, festivaller ve etkinlik takvimi anlık olarak cebinde.

Keşif Rotaları: Şehrin tarihini ve saklı güzelliklerini adım adım keşfedeceğin özel rotalar.

Size Özel Avantajlar: Yerel esnafımızla yaptığımız iş birlikleri ve uygulamaya özel sürpriz indirimler.

Diyarbakır’ın her sokağında bir hikaye, her köşesinde bir lezzet var. Bu hikayeyi birlikte yazmak ve şehrimizi dijital dünyada hak ettiği şekilde temsil etmek için sabırsızlanıyoruz.

Hadi, uygulamaya gir ve bu kadim şehri bizimle birlikte yeniden keşfetmeye başla!

Mutlulukla kal,
FREEMİND Ekibi";

        $html = $this->buildEmailHtml($subject, $body, $toName);
        return $this->sendSmtpMail($toEmail, $toName, $subject, $html);
    }

    private function sendSmtpMail($toEmail, $toName, $subject, $htmlBody) {
        $phpmailer_path = __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
        if (file_exists($phpmailer_path)) {
            return $this->sendWithPHPMailer($toEmail, $toName, $subject, $htmlBody, $phpmailer_path);
        }
        return $this->sendWithSocket($toEmail, $toName, $subject, $htmlBody);
    }

    private function sendWithPHPMailer($toEmail, $toName, $subject, $htmlBody, $path) {
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
            $mail->SMTPSecure = (SMTP_PORT == 465 || SMTP_PORT == 443) ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->send();
            $this->logMail($subject, $toEmail, 'PHPMailer');
            return true;
        } catch (Exception $e) {
            return $mail->ErrorInfo;
        }
    }

    private function sendWithSocket($toEmail, $toName, $subject, $htmlBody) {
        $host = SMTP_HOST;
        $port = (int)SMTP_PORT;
        $user = base64_encode(SMTP_USER);
        $pass = base64_encode(SMTP_PASS);

        $context = stream_context_create(['ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ]]);

        $scheme = ($port == 465 || $port == 443) ? 'ssl' : 'tcp';
        $fp = @stream_socket_client("{$scheme}://{$host}:{$port}", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
        if (!$fp) return "Bağlantı hatası: {$errstr}";

        $read = fgets($fp, 1024);
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
        }

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

        $this->logMail($subject, $toEmail, 'Socket');
        return true;
    }

    private function buildEmailHtml($subject, $bodyContent, $name) {
        $bodyHtml = nl2br(htmlspecialchars($bodyContent, ENT_QUOTES, 'UTF-8'));
        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0; padding:0; background:#f4f6f9; font-family: Arial, sans-serif;">
<table width="100%" style="background:#f4f6f9; padding: 20px 0;">
  <tr><td align="center">
    <table width="600" style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
      <tr><td style="background: #1a3a5c; padding: 30px; text-align:center;">
        <h1 style="color:#ffffff; margin:0;">ROTAREHBER</h1>
      </td></tr>
      <tr><td style="padding: 40px; color:#333; line-height:1.6;">{$bodyHtml}</td></tr>
      <tr><td style="background:#f8f9fb; padding: 20px; text-align:center; font-size:12px; color:#888;">
        © 2024 ROTAREHBER — Tüm hakları saklıdır.
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
    }

    private function logMail($subject, $to, $method) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO communication_logs (type, recipient, subject, message, status) VALUES ('Email', ?, ?, ?, 'Success')");
            $stmt->execute([$to, $subject, "Method: $method"]);
        } catch (Exception $e) {}
    }
}
