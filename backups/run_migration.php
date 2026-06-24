<?php
/**
 * Tek seferlik DB Migration Scripti
 * Hosting sunucusunda users tablosuna eksik kolonları ekler.
 * KULLANIM: Tarayıcıda http://domain.com/run_migration.php adresini açın.
 * Çalıştırdıktan sonra bu dosyayı silin!
 */
require_once __DIR__ . '/config.php';

$results = [];

$migrations = [
    'otp_code'      => "ALTER TABLE users ADD COLUMN `otp_code` VARCHAR(10) NULL DEFAULT NULL",
    'otp_expiry'    => "ALTER TABLE users ADD COLUMN `otp_expiry` DATETIME NULL DEFAULT NULL",
    'is_verified'   => "ALTER TABLE users ADD COLUMN `is_verified` TINYINT(1) NOT NULL DEFAULT 0",
    'google_id'     => "ALTER TABLE users ADD COLUMN `google_id` VARCHAR(100) NULL DEFAULT NULL",
    'profile_image' => "ALTER TABLE users ADD COLUMN `profile_image` VARCHAR(500) NULL DEFAULT NULL",
    'last_login_at' => "ALTER TABLE users ADD COLUMN `last_login_at` DATETIME NULL DEFAULT NULL",
];

try {
    $existing_cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($migrations as $col => $sql) {
        if (in_array($col, $existing_cols)) {
            $results[] = "✅ <code>$col</code> zaten mevcut, atlandı.";
        } else {
            $pdo->exec($sql);
            $results[] = "🟢 <code>$col</code> başarıyla eklendi!";
        }
    }
} catch (Exception $e) {
    $results[] = "❌ HATA: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head><meta charset="UTF-8"><title>DB Migration</title>
<style>body{font-family:sans-serif;max-width:600px;margin:50px auto;padding:20px;}
li{margin:8px 0;font-size:1.1rem;}</style>
</head>
<body>
<h2>🛠️ Veritabanı Migration Sonuçları</h2>
<ul>
<?php foreach ($results as $r): ?>
    <li><?= $r ?></li>
<?php endforeach; ?>
</ul>
<p style="color:red;font-weight:bold;">⚠️ Bu işlem tamamlandıktan sonra <code>run_migration.php</code> dosyasını sunucudan silin!</p>
</body>
</html>
