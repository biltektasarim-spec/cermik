<?php
/**
 * ROTA REHBER - Hosting Yapılandırma Örneği
 * Bu dosyayı hosting bilgilerinizle güncelleyip 'config.php' içine yapıştırın.
 */

// Hosting Veritabanı Bilgileri
$host = 'localhost'; // Genelde localhost'tur
$db   = 'hosting_veritabani_adi';
$user = 'hosting_veritabani_kullanicisi';
$pass = 'hosting_veritabani_sifresi';

// PDO Bağlantısı
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Diğer ayarlar config.php içinden devam edecektir.
