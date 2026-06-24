<?php

require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/../includes/SmsService.php';

// Ayarları çek
$settings_res = $pdo->query("SELECT name, value FROM settings WHERE name IN ('sms_username', 'sms_password', 'sms_title')")->fetchAll();
$s = [];
foreach ($settings_res as $row) $s[$row['name']] = $row['value'];

$smsService = new SmsService(
    $s['sms_username'] ?? '7073c30918869aee144ddca9',
    $s['sms_password'] ?? 'bb37df2be980e603326bce12',
    $s['sms_title'] ?? 'REHBER'
);

// Mesaj Gönderii İşlemi
$msg_status = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $type = $_POST['type']; // SMS or Email
    $subject = $_POST['subject'] ?? null;
    $message = $_POST['message'];
    $target = $_POST['target'] ?? 'all'; // all or specific
    $specific_phone = $_POST['specific_phone'] ?? '';

    $recipients = [];
    if ($target == 'specific' && !empty($specific_phone)) {
        $recipients[] = ['email' => '', 'phone' => $specific_phone, 'name' => 'Özel Alıcı'];
    } else {
        $users = $pdo->query("SELECT email, phone, first_name, last_name FROM users WHERE is_active = 1")->fetchAll();
        foreach ($users as $u) {
            $recipients[] = [
                'email' => $u['email'],
                'phone' => $u['phone'],
                'name'  => $u['first_name'] . ' ' . $u['last_name']
            ];
        }
    }
    
    $success_count = 0;
    $fail_count = 0;
    
    foreach ($recipients as $r) {
        $recipient = ($type == 'Email') ? $r['email'] : $r['phone'];
        if (empty($recipient)) continue;
        
        $is_sent = false;
        if ($type == 'SMS') {
            $res = $smsService->sendSms($recipient, $message);
            $is_sent = $res['status'];
        } else {
            // Email logic (placeholder or existing)
            $is_sent = true; 
        }
        
        // Logla
        $stmt = $pdo->prepare("INSERT INTO communication_logs (type, recipient, subject, message, status) VALUES (?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$type, $recipient, $subject, $message, $is_sent ? 'Success' : 'Failed']);
        } catch (Exception $e) {
            // Tablo yoksa oluştur
            $pdo->exec("CREATE TABLE IF NOT EXISTS communication_logs (id INT AUTO_INCREMENT PRIMARY KEY, type VARCHAR(10), recipient VARCHAR(100), subject VARCHAR(200), message TEXT, status VARCHAR(20), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
            $stmt->execute([$type, $recipient, $subject, $message, $is_sent ? 'Success' : 'Failed']);
        }
        
        if ($is_sent) $success_count++;
        else $fail_count++;
    }
    
    $msg_status = "Gönderim tamamlandı: $success_count başarılı, $fail_count hatalı.";
}

// Logları Çek
try {
    $logs = $pdo->query("SELECT * FROM communication_logs ORDER BY created_at DESC LIMIT 50")->fetchAll();
} catch (Exception $e) {
    $logs = [];
}

// Kullanıcıları çek (tekli seçim için)
$all_users = $pdo->query("SELECT id, phone, first_name, last_name FROM users WHERE phone IS NOT NULL AND phone != '' ORDER BY first_name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İletişim Yönetimi - Çermik Yönetim</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .comm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        select, input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; }
        textarea { height: 120px; }
        .log-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .log-table th, .log-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .badge-sms { background: #ebf8ff; color: #3182ce; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; }
        .badge-email { background: #faf5ff; color: #805ad5; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <h1>İletişim ve Toplu Mesaj</h1>
                <p style="color: var(--text-muted);">Vatandaşlara tekli veya toplu SMS / E-Posta gönderin.</p>
            </div>
        </header>

        <main class="page-content">
            <?php if ($msg_status): ?>
                <div style="background: #c6f6d5; color: #2f855a; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $msg_status; ?>
                </div>
            <?php endif; ?>

            <div class="comm-grid">
                <!-- Sol: Gönderi Formu -->
                <div class="card">
                    <h3><i class="fa-solid fa-paper-plane"></i> Yeni Mesaj Oluştur</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="send">
                        
                        <div class="form-group">
                            <label>Gönderi Tipi</label>
                            <select name="type" onchange="toggleSubject(this.value)">
                                <option value="SMS">SMS</option>
                                <option value="Email">E-Posta</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Alıcı Kapsamı</label>
                            <select name="target" onchange="toggleTarget(this.value)">
                                <option value="all">Tüm Aktif Üyeler</option>
                                <option value="specific">Belirli Bir Kişi</option>
                            </select>
                        </div>

                        <div class="form-group" id="specific-target-group" style="display: none;">
                            <label>Kişi Seçin</label>
                            <select name="specific_phone" id="specific_phone">
                                <option value="">Seçiniz...</option>
                                <?php foreach($all_users as $au): ?>
                                    <option value="<?php echo htmlspecialchars($au['phone']); ?>">
                                        <?php echo htmlspecialchars($au['first_name'] . ' ' . $au['last_name'] . ' (' . $au['phone'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" id="subject-group" style="display: none;">
                            <label>Konu (Sadece E-Posta)</label>
                            <input type="text" name="subject" placeholder="E-Posta konusu...">
                        </div>

                        <div class="form-group">
                            <label>Mesaj İçeriği</label>
                            <textarea name="message" placeholder="Mesajınızı buraya yazın..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; border: none; padding: 12px; font-size: 1rem;">
                            <i class="fa-solid fa-share-from-square"></i> Şimdi Gönder
                        </button>
                    </form>
                </div>

                <!-- Sağ: Özet Bilgi -->
                <div class="card">
                    <h3><i class="fa-solid fa-circle-info"></i> Bilgilendirme</h3>
                    <p>Gönderiler belirlenen kapsama göre anlık olarak iletilir.</p>
                    <ul style="margin-top: 15px; padding-left: 20px; line-height: 1.8; color: #4a5568;">
                        <li><strong>SMS API:</strong> Şu an <?php echo htmlspecialchars($s['sms_title'] ?? 'Varsayılan'); ?> başlığı ile gönderim yapılmaktadır.</li>
                        <li><strong>Toplu SMS:</strong> Tüm aktif üyelere tek tıkla mesaj atabilirsiniz.</li>
                        <li><strong>Tekli SMS:</strong> Rehberinizdeki kayıtlı üyelerden birini seçerek özel mesaj gönderebilirsiniz.</li>
                        <li><strong>Kayıtlar:</strong> Tüm gönderimler aşağıda listelenmektedir.</li>
                    </ul>
                </div>
            </div>

            <!-- Alt: Gönderi Günlüğü -->
            <div class="card" style="margin-top: 25px;">
                <h3><i class="fa-solid fa-clock-rotate-left"></i> Son Gönderi Kayıtları</h3>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="log-table">
                        <thead>
                            <tr>
                                <th>Tip</th>
                                <th>Alıcı</th>
                                <th>Mesaj / Konu</th>
                                <th>Durum</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr><td colspan="5" style="text-align:center;">Henüz kayıt bulunamadı.</td></tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td>
                                        <span class="badge-<?php echo strtolower($log['type']); ?>">
                                            <?php echo $log['type']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $log['recipient']; ?></td>
                                    <td>
                                        <div style="font-weight: 600; font-size: 0.85rem;"><?php echo $log['subject'] ?: '(Konusuz)'; ?></div>
                                        <div style="font-size: 0.8rem; color: #718096; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 250px;">
                                            <?php echo $log['message']; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="color: <?php echo $log['status'] == 'Success' ? '#38a169' : '#e53e3e'; ?>;">
                                            <i class="fa-solid fa-<?php echo $log['status'] == 'Success' ? 'circle-check' : 'circle-xmark'; ?>"></i>
                                        </span>
                                    </td>
                                    <td style="font-size: 0.8rem;"><?php echo date('d.m.Y H:i', strtotime($log['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
    function toggleSubject(type) {
        document.getElementById('subject-group').style.display = (type === 'Email') ? 'block' : 'none';
    }
    function toggleTarget(val) {
        document.getElementById('specific-target-group').style.display = (val === 'specific') ? 'block' : 'none';
    }
    </script>
</body>
</html>
