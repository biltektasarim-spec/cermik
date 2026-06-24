<?php
/**
 * Merkezi Güvenlik Yardımcısı
 * - CSRF token üretme / doğrulama
 * - Rate-limiting (brute-force koruması)
 * - Güvenli dosya yükleme doğrulama
 * - XSS temizleme
 */

// ─── CSRF TOKEN ───────────────────────────────────────────────────────────────

/**
 * Oturum için bir CSRF token üretir (yoksa) ve döndürür.
 */
if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

/**
 * CSRF token'ı doğrular; başarısız olursa 403 döner ve durur.
 */
function csrf_verify(): void {
    $submitted = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submitted)) {
        http_response_code(403);
        $debug_msg = "Geçersiz CSRF token. Sayfayı yenileyip tekrar deneyin.";
        if (isset($_SESSION['csrf_token'])) {
             $debug_msg .= " (Oturum: " . substr($_SESSION['csrf_token'], 0, 5) . "..., Gelen: " . substr($submitted, 0, 5) . "...)";
        } else {
             $debug_msg .= " (Oturumda token yok!)";
        }
        die($debug_msg);
    }
    // Tek kullanım: her doğrulama sonrası yenile
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * HTML olarak embed edilecek gizli CSRF input alanı.
 */
if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

// ─── RATE LIMITING ────────────────────────────────────────────────────────────

/**
 * Belirli bir anahtar için rate-limit uygular.
 * @param string $key        Benzersiz anahtar (ör: 'admin_login_1.2.3.4')
 * @param int    $max        Maksimum deneme sayısı
 * @param int    $window_sec Zaman penceresi (saniye)
 * @return bool true = sınır aşıldı (engelle), false = izin ver
 */
function rate_limit_exceeded(string $key, int $max = 5, int $window_sec = 300): bool {
    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }
    $now = time();
    $rl  = &$_SESSION['rate_limits'][$key] ?? null;

    if ($rl === null || ($now - $rl['start']) > $window_sec) {
        $_SESSION['rate_limits'][$key] = ['count' => 1, 'start' => $now];
        return false;
    }

    $_SESSION['rate_limits'][$key]['count']++;
    return $_SESSION['rate_limits'][$key]['count'] > $max;
}

/**
 * Belirli bir rate-limit anahtarını sıfırlar (başarılı girişten sonra).
 */
function rate_limit_reset(string $key): void {
    unset($_SESSION['rate_limits'][$key]);
}

// ─── DOSYA YÜKLEME GÜVENLİĞİ ─────────────────────────────────────────────────

/** İzin verilen MIME türleri. */
const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
const ALLOWED_IMAGE_EXTS  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
const MAX_UPLOAD_SIZE_BYTES = 5 * 1024 * 1024; // 5 MB

/**
 * Yüklenen dosyanın güvenli bir resim olduğunu doğrular.
 * @param array  $file  $_FILES['...'] dizisi
 * @param string &$error Hata mesajı (çıkış parametresi)
 * @return bool
 */
function validate_uploaded_image(array $file, string &$error): bool {
    // 1. PHP yükleme hatası
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Dosya yükleme hatası (kod: ' . $file['error'] . ').';
        return false;
    }

    // 2. Boyut kontrolü
    if ($file['size'] > MAX_UPLOAD_SIZE_BYTES) {
        $error = 'Dosya boyutu en fazla 5 MB olabilir.';
        return false;
    }

    // 3. İstemcinin söylediği uzantıyı değil, gerçek MIME tipini kontrol et (Desteklenmeyen eklentiler için fallback eklendi)
    $mimeType = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
    } elseif (function_exists('mime_content_type')) {
        $mimeType = mime_content_type($file['tmp_name']);
    } else {
        $imgInfo = @getimagesize($file['tmp_name']);
        if ($imgInfo && isset($imgInfo['mime'])) {
            $mimeType = $imgInfo['mime'];
        }
    }

    if (!$mimeType || !in_array($mimeType, ALLOWED_IMAGE_TYPES, true)) {
        $error = 'Yalnızca JPG, PNG, GIF ve WebP dosyaları yüklenebilir.';
        return false;
    }

    // 4. Uzantı kontrolü (çift uzantı saldırılarına karşı)
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_IMAGE_EXTS, true)) {
        $error = 'Geçersiz dosya uzantısı.';
        return false;
    }

    // 5. İçerik kontrolü: gerçek bir resim mi?
    if (!@getimagesize($file['tmp_name'])) {
        $error = 'Dosya geçerli bir resim değil.';
        return false;
    }

    return true;
}

/**
 * Güvenli, tahmin edilemez bir dosya adı oluşturur.
 * Orijinal uzantıyı kullanır (temizlenmiş olarak).
 */
function safe_upload_filename(array $file): string {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $ext = preg_replace('/[^a-z0-9]/', '', $ext);
    return bin2hex(random_bytes(16)) . '.' . $ext;
}

// ─── XSS / GİRDİ TEMİZLEME ───────────────────────────────────────────────────

/**
 * HTML çıkışı için güvenli şekilde kaçış yapar.
 */
if (!function_exists('e')) {
    function e(string $val): string {
        return htmlspecialchars($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

/**
 * Tamsayı kimliği (ID) olarak güvenli biçimde dönüştürür.
 * 0 veya negatif dönerse geçersiz kabul et.
 */
function safe_id($val): int {
    return max(0, (int) $val);
}
