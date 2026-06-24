<?php
ob_start(); // Output buffering - header() çakışmasını önler

// ── Session Ayarları (AppServ / Windows uyumlu) ──────────────
// session_start()'tan ÖNCE ini_set çağrılmalı
if (session_status() === PHP_SESSION_NONE) {
    // Session dosyalarının kayıt yeri (AppServ'de bazen sys_temp hatalı)
    $sess_path = __DIR__ . '/sessions';
    if (!is_dir($sess_path)) @mkdir($sess_path, 0755, true);
    @ini_set('session.save_path', $sess_path);

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

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/security.php';

// Veritabanı bağlantı ayarları
define('GOOGLE_CLIENT_ID', '416501247740-4aerep5pph23s0g6vakgo2gtaivem2jm.apps.googleusercontent.com');

$host = 'localhost';
$db   = 'u2509838_rehber';
$user = 'u2509838_user622';
$pass = '7C4eRd0X1fgR:.:=';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$is_https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     $pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
     $pdo->exec("SET CHARACTER SET utf8mb4");

     // ── Otomatik DB Migration ───────────────────────────────────
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
} catch (\PDOException $e) {
     error_log('[REHBER DB] ' . $e->getMessage());
     die('Veritabanı bağlantı hatası. Lütfen daha sonra tekrar deneyin.');
}

// ── Settings Helper ──────────────────────────────────────────
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
// ─────────────────────────────────────────────────────────────
