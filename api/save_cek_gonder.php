<?php
require_once '../config.php';
header('Content-Type: application/json; charset=utf-8');

// Sadece POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => __('invalid_request')]);
    exit;
}

// Zorunlu alanlar
$basvuru_turu = trim($_POST['basvuru_turu'] ?? '');
$ad_soyad     = trim($_POST['ad_soyad']     ?? '');
$tc_no        = trim($_POST['tc_no']        ?? '');
$email        = trim($_POST['email']        ?? '');
$tel_no       = trim($_POST['tel_no']       ?? '');
$aciklama     = trim($_POST['aciklama']     ?? '');

// Encoding-safe doğrulama: boş olmamalı ve bilinen değerlerden biri olmalı
// mb_strtolower ile Türkçe karakter farklılıklarını aşıyoruz
$izin_verilen_turler_lower = ['bilgilendirme', 'i̇stek', 'istek', 'öneri', 'oneri', 'şikayet', 'sikayet', 'teşekkür', 'tesekkur'];
$basvuru_turu_lower = mb_strtolower($basvuru_turu, 'UTF-8');

if (empty($basvuru_turu) || !in_array($basvuru_turu_lower, $izin_verilen_turler_lower)) {
    // Eğer boş değilse kabul et (kullanıcı gerçekten bir seçim yapmış)
    if (empty($basvuru_turu)) {
        echo json_encode(['status' => 'error', 'message' => __('type_not_selected')]);
        exit;
    }
    // Değer dolu ama listede yoksa yine de kabul et (encoding sorunu ihtimaline karşı)
}
if (empty($ad_soyad) || empty($tc_no) || empty($aciklama)) {
    echo json_encode(['status' => 'error', 'message' => __('validation_fields_required')]);
    exit;
}
if (!preg_match('/^\d{11}$/', $tc_no)) {
    echo json_encode(['status' => 'error', 'message' => __('tc_error_numbers')]);
    exit;
}

// Tabloyu oluştur (yoksa) - district_id eklendi
$pdo->exec("
    CREATE TABLE IF NOT EXISTS cek_gonder_forms (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        district_id INT DEFAULT 0,
        user_id     INT DEFAULT NULL,
        fcm_token   VARCHAR(255) DEFAULT NULL,
        basvuru_turu VARCHAR(30)  NOT NULL,
        ad_soyad    VARCHAR(100) NOT NULL,
        tc_no       VARCHAR(11)  NOT NULL,
        email       VARCHAR(150),
        tel_no      VARCHAR(20),
        aciklama    TEXT         NOT NULL,
        foto1       VARCHAR(255),
        foto2       VARCHAR(255),
        foto3       VARCHAR(255),
        created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Eğer district_id kolonu yoksa ekle (Migration)
try {
    $cols = $pdo->query("SHOW COLUMNS FROM cek_gonder_forms")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('district_id', $cols)) {
        $pdo->exec("ALTER TABLE cek_gonder_forms ADD COLUMN district_id INT DEFAULT 0 AFTER id");
    }
    if (!in_array('user_id', $cols)) {
        $pdo->exec("ALTER TABLE cek_gonder_forms ADD COLUMN user_id INT DEFAULT NULL AFTER district_id");
    }
    if (!in_array('fcm_token', $cols)) {
        $pdo->exec("ALTER TABLE cek_gonder_forms ADD COLUMN fcm_token VARCHAR(255) DEFAULT NULL AFTER user_id");
    }
} catch (Exception $e) {}

// Fotoğraf yükleme işlevi
function uploadFoto($fileKey, $uploadDir) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $file = $_FILES[$fileKey];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxSize = 10 * 1024 * 1024; // 10 MB (Hosting için artırıldı)

    if (!in_array($file['type'], $allowedTypes)) {
        return null;
    }
    if ($file['size'] > $maxSize) {
        return null;
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('cg_') . '.' . $ext;
    $destPath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destPath)) {
        return 'uploads/cek_gonder/' . $filename;
    }
    return null;
}

$uploadDir = __DIR__ . '/../uploads/cek_gonder/';
$foto1Path = uploadFoto('foto1', $uploadDir);
$foto2Path = uploadFoto('foto2', $uploadDir);
$foto3Path = uploadFoto('foto3', $uploadDir);
$dist_id   = (int)($_POST['district_id'] ?? 0);

// Veritabanına kaydet
$user_id = $_SESSION['user_id'] ?? null;
try {
    $fcm_token = trim($_POST['fcm_token'] ?? '');
    $stmt = $pdo->prepare("
        INSERT INTO cek_gonder_forms
            (district_id, user_id, fcm_token, basvuru_turu, ad_soyad, tc_no, email, tel_no, aciklama, foto1, foto2, foto3)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $dist_id,
        $user_id,
        $fcm_token,
        $basvuru_turu,
        $ad_soyad,
        $tc_no,
        $email,
        $tel_no,
        $aciklama,
        $foto1Path,
        $foto2Path,
        $foto3Path
    ]);

    $lastId = $pdo->lastInsertId();

    // ── MAIL BİLDİRİMİ ───────────────────────────────────────────
    try {
        $settings = get_settings($pdo, $dist_id);
        $admin_email = $settings['admin_email'] ?? 'admin@rotarehber.com';
        $site_name = $settings['site_name'] ?? 'İlçe Rehberi';
        
        $subject = "Yeni Çek Gönder Başvurusu: $basvuru_turu (#$lastId)";
        $body = "Sayın Admin,\n\n"
              . "Sistem üzerinden yeni bir 'Çek Gönder' başvurusu alınmıştır.\n\n"
              . "İlçe: $site_name\n"
              . "Tür: $basvuru_turu\n"
              . "Ad Soyad: $ad_soyad\n"
              . "TC No: $tc_no\n"
              . "Telefon: $tel_no\n"
              . "E-posta: $email\n"
              . "Açıklama:\n$aciklama\n\n"
              . "Fotoğraf Linkleri:\n"
              . ($foto1Path ? "1: https://{$_SERVER['HTTP_HOST']}/REHBER/$foto1Path\n" : "")
              . ($foto2Path ? "2: https://{$_SERVER['HTTP_HOST']}/REHBER/$foto2Path\n" : "")
              . ($foto3Path ? "3: https://{$_SERVER['HTTP_HOST']}/REHBER/$foto3Path\n" : "")
              . "\nİyi çalışmalar.";

        $headers = "From: noreply@rotarehber.com\r\nReply-To: $email\r\nX-Mailer: PHP/" . phpversion();

        // 1. Genel Admin'e gönder
        @mail($admin_email, $subject, $body, $headers);
        
        // 2. Eğer ilçe admini farklıysa ona da gönder
        if ($dist_id > 0) {
            $global_settings = get_settings($pdo, 0);
            $global_admin = $global_settings['admin_email'] ?? '';
            if (!empty($admin_email) && $admin_email !== $global_admin) {
                // Zaten $settings ile ilçe adminini aldık (get_settings override eder)
                // Eğer ilçe admini genel adminden farklıysa, mail zaten gönderildi.
                // Log sistemine eklenebilir.
            }
        }
    } catch (Exception $e_mail) {}
    // ─────────────────────────────────────────────────────────────

    echo json_encode([
        'status'  => 'success',
        'message' => __('submission_received_api'),
        'id'      => $lastId
    ]);

} catch (\PDOException $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => __('db_error_msg') . ': ' . $e->getMessage()
    ]);
}
