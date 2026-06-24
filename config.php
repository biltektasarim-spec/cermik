<?php
// PHP 8.2 geçişi için arayüzü bozabilecek hataları gizle (arka planda loglanır)
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start(); // Output buffering - header() çakışmasını önler

// ── Session Ayarları (AppServ / Windows uyumlu) ──────────────
// session_start()'tan ÖNCE ini_set çağrılmalı
if (session_status() === PHP_SESSION_NONE) {
    // Session dosyalarının kayıt yeri (Kilitlenmeyi önlemek için dizin dışına taşındı)
    // Hosting open_basedir kısıtlamasına takılmamak için 'private' klasörü kullanılıyor.
    $sess_path = dirname(__DIR__) . '/private/REHBER_sessions';
    if (!is_dir($sess_path)) {
        // Eğer private klasörü yoksa (örn. XAMPP) public_html içerisine (veya kök dizine) kurmayı dener.
        $sess_path_fallback = __DIR__ . '/REHBER_sessions';
        if (!is_dir($sess_path_fallback)) {
            @mkdir($sess_path_fallback, 0777, true);
        }
        $sess_path = $sess_path_fallback;
    } else {
        if (!is_dir($sess_path)) {
            @mkdir($sess_path, 0777, true);
        }
    }
    ini_set('session.save_path', $sess_path);

    // Cookie parametreleri
    @ini_set('session.gc_maxlifetime', 2592000);   // 30 gün (saniye)
    @ini_set('session.cookie_lifetime', 2592000);  // 30 gün
    @ini_set('session.use_only_cookies', 1);
    @ini_set('session.cookie_httponly', 1);
    @ini_set('session.cookie_path', '/');
    @ini_set('session.use_strict_mode', 1);

    @session_name('REHBER_SESSID');
    session_start();

    // Session'ı 30 gün uzat (her istekte cookie'yi yenile)
    if (isset($_SESSION['user_id'])) {
        setcookie(session_name(), session_id(), [
            'expires'  => time() + 2592000,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}
// ─────────────────────────────────────────────────────────────

// Tüm native PHP scriptleri için varsayılan zaman dilimini ayarla
date_default_timezone_set('Europe/Istanbul');

if (strpos($_SERVER['SCRIPT_NAME'], '/api/') === false) {
    header('Content-Type: text/html; charset=utf-8');
}
// --- CORS Support for Flutter Web ---
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);
}
// ------------------------------------
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/security.php';

// Veritabanı bağlantı ayarları
// Ortam tespiti: Canlı (rotarehber.com) veya Lokal (localhost)
$_is_live = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'rotarehber.com') !== false;

// Google Maps API Key - Google Cloud Console'da her iki domain de whitelist'e eklenmelidir:
// https://rotarehber.com/* ve http://localhost/*
define('GOOGLE_MAPS_API_KEY', 'AIzaSyC7YLV-3m5HZ7B7K7JtHC1910su9ufhLjw');
define('GOOGLE_CLIENT_ID', '416501247740-4aerep5pph23s0g6vakgo2gtaivem2jm.apps.googleusercontent.com');

$host = 'localhost';
$db   = 'rehber_db';  // Hosting panelinden oluşturduğunuz veritabanı adı
$user = 'root';    // Hosting panelinden oluşturduğunuz kullanıcı adı
$pass = '21212121';  // Belirlediğiniz şifre
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$is_https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

try {
    // MySQL time_zone değişkenini senkronize ederek current_timestamp vb alanların Türkiye saatinde çalışmasını sağlarız
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '+03:00'"
    ]);
    
    // ── Otomatik DB Migration (Sadece ?migrate=1 parametresi ile çalışır) ──
    if (isset($_GET['migrate']) && $_GET['migrate'] == '1') {
        // ── Users tablosu OTP migration ───────────────────────────
        try {
            $user_cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('otp_code', $user_cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN `otp_code` VARCHAR(10) NULL DEFAULT NULL");
            }
            if (!in_array('otp_expiry', $user_cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN `otp_expiry` DATETIME NULL DEFAULT NULL");
            }
            if (!in_array('is_verified', $user_cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN `is_verified` TINYINT(1) NOT NULL DEFAULT 0");
            }
            if (!in_array('google_id', $user_cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN `google_id` VARCHAR(100) NULL DEFAULT NULL");
            }
            if (!in_array('profile_image', $user_cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN `profile_image` VARCHAR(500) NULL DEFAULT NULL");
            }
            if (!in_array('last_login_at', $user_cols)) {
                $pdo->exec("ALTER TABLE users ADD COLUMN `last_login_at` DATETIME NULL DEFAULT NULL");
            }
        } catch (Exception $_mig) { error_log('[REHBER MIGRATION users] ' . $_mig->getMessage()); }

        try {
            $cols = $pdo->query("SHOW COLUMNS FROM businesses")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('description', $cols)) {
                $pdo->exec("ALTER TABLE businesses ADD COLUMN `description` TEXT NULL AFTER `contact_info`");
            }
            if (!in_array('description_en', $cols)) {
                $pdo->exec("ALTER TABLE businesses ADD COLUMN `description_en` TEXT NULL AFTER `description`");
            }
            if (!in_array('hotel_info', $cols)) {
                $pdo->exec("ALTER TABLE businesses ADD COLUMN `hotel_info` TEXT NULL");
            }
            if (!in_array('working_hours', $cols)) {
                $pdo->exec("ALTER TABLE businesses ADD COLUMN `working_hours` TEXT NULL COMMENT 'JSON: {days:[0-6], open:HH:MM, close:HH:MM}'");
            }
        } catch (Exception $_mig) { }

        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `announcements` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `district_id` INT DEFAULT NULL,
                    `content` TEXT NOT NULL,
                    `content_en` TEXT,
                    `image` VARCHAR(500) DEFAULT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $_mig) { }

        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `events` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `title` VARCHAR(300) NOT NULL,
                    `title_en` VARCHAR(300) DEFAULT NULL,
                    `description` TEXT,
                    `description_en` TEXT,
                    `event_date` DATETIME NOT NULL,
                    `image` VARCHAR(500) DEFAULT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $_mig) { }

        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `mail_logs` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `subject` VARCHAR(500) NOT NULL,
                    `body_preview` TEXT,
                    `recipient_count` INT DEFAULT 0,
                    `sent_by` VARCHAR(100) DEFAULT 'admin',
                    `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $_mig) { }

        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `password_resets` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `email` VARCHAR(255) NOT NULL,
                    `token` VARCHAR(255) NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX (email),
                    INDEX (token)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $_mig) { }

        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `communication_logs` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `type` VARCHAR(10) NOT NULL,
                    `recipient` VARCHAR(150) NOT NULL,
                    `subject` VARCHAR(500),
                    `message` TEXT NOT NULL,
                    `status` VARCHAR(20) DEFAULT 'Success',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $_mig) { }

        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `business_stats` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `business_id` INT NOT NULL,
                    `event_type` ENUM('view', 'direction') NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX (`business_id`),
                    INDEX (`event_type`),
                    INDEX (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $_mig) { }

        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `custom_menus` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `district_id` INT NOT NULL,
                    `name_tr` VARCHAR(200) NOT NULL,
                    `name_en` VARCHAR(200) DEFAULT NULL,
                    `slug` VARCHAR(100) NOT NULL,
                    `image` VARCHAR(500) DEFAULT NULL,
                    `icon` VARCHAR(100) DEFAULT 'fa-star',
                    `menu_type` ENUM('multi','single') DEFAULT 'multi',
                    `target_url` VARCHAR(500) DEFAULT NULL,
                    `sort_order` INT DEFAULT 0,
                    `is_active` TINYINT(1) DEFAULT 1,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY `uniq_district_slug` (`district_id`, `slug`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $_mig) { error_log('[REHBER MIGRATION custom_menus] ' . $_mig->getMessage()); }

        try {
            // --- Services (Projeler) Tablosu Güncellemesi ---
            $services_cols = $pdo->query("SHOW COLUMNS FROM services")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('progress', $services_cols)) {
                $pdo->exec("ALTER TABLE services ADD COLUMN `progress` INT DEFAULT 0 AFTER `status` ");
            }
        } catch (Exception $_mig) { error_log('[REHBER MIGRATION services] ' . $_mig->getMessage()); }

        try {
            $pdo->exec("ALTER TABLE settings MODIFY COLUMN `value` TEXT NULL");
        } catch (Exception $_mig) { error_log('[REHBER MIGRATION settings_text] ' . $_mig->getMessage()); }

        try {
            $checkins_cols = $pdo->query("SHOW COLUMNS FROM check_ins")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('visit_type', $checkins_cols)) {
                $pdo->exec("ALTER TABLE check_ins ADD COLUMN `visit_type` ENUM('MANUAL', 'AUTO') DEFAULT 'MANUAL' AFTER `status` ");
            }
        } catch (Exception $_mig) { error_log('[REHBER MIGRATION check_ins_visit_type] ' . $_mig->getMessage()); }

    }

    // ── Kritik Tablo Kontrolleri (Sessizce her zaman çalışır) ──
    try {
        // --- Çek Gönder Tablosu Status Güncellemesi (Sessiz) ---
        $cgf_cols = $pdo->query("SHOW COLUMNS FROM cek_gonder_forms")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('process_status', $cgf_cols)) {
            $pdo->exec("ALTER TABLE cek_gonder_forms ADD COLUMN `process_status` VARCHAR(50) DEFAULT 'Beklemede'");
        }
    } catch (Exception $_mig) { }
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `passive_stats` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `target_id` INT NOT NULL,
                `target_type` ENUM('place', 'business') NOT NULL,
                `district_id` INT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (`target_id`, `target_type`),
                INDEX (`district_id`),
                INDEX (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    } catch (Exception $_mig_silent) { }

} catch (\PDOException $e) {
     error_log('[REHBER DB] ' . $e->getMessage());
     die('DB Error: ' . $e->getMessage());
}

// ── Settings Helper ──────────────────────────────────────────
if (!function_exists('get_settings')) {
    function get_settings($pdo, $district_id = 0) {
        $settings = [];
        if (!$pdo) return $settings;
        
        // 1. Global settings
        try {
            $stmt = $pdo->prepare("SELECT name, value FROM settings WHERE district_id = 0");
            $stmt->execute();
            while ($row = $stmt->fetch()) { $settings[$row['name']] = $row['value']; }

            // 2. District specific overrides
            if ($district_id > 0) {
                $stmt = $pdo->prepare("SELECT name, value FROM settings WHERE district_id = ?");
                $stmt->execute([$district_id]);
                while ($row = $stmt->fetch()) { $settings[$row['name']] = $row['value']; }
            }
        } catch (Exception $e) {
            error_log('[REHBER SETTINGS] ' . $e->getMessage());
        }
        return $settings;
    }
}
// ─────────────────────────────────────────────────────────────
