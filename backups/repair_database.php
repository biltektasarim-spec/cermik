<?php
/**
 * RotaRehber Database Encoding Repair Tool
 * 
 * Bu betik, veritabanındaki "double-encoded" UTF-8 karakterlerini düzeltmek için tasarlanmıştır.
 * Örneğin: "Ã‡ermik" -> "Çermik"
 */

require_once 'config.php';

// Güvenlik: Sadece admin oturumu açıkken veya belirli bir anahtar ile çalışabilir
// Şimdilik geliştirme aşamasında doğrudan çalıştırılabilir, ancak canlıda silinmelidir.

header('Content-Type: text/plain; charset=utf-8');

$tables_columns = [
    'places' => ['name', 'description', 'address', 'category'],
    'districts' => ['name', 'description'],
    'districts_extra' => ['title', 'content'],
    'businesses' => ['name', 'description', 'address', 'category'],
    'events' => ['title', 'description', 'location'],
    'announcements' => ['title', 'content'],
    'badges' => ['name', 'description'],
    'municipal_guide' => ['title', 'content'],
    'hospitals' => ['name', 'address', 'specialty'],
    'pharmacies' => ['name', 'address', 'notes'],
    'ai_chat_logs' => ['user_message', 'ai_response']
];

echo "--- RotaRehber Veritabanı Onarım Başlatıldı ---\n\n";

// CLI veya Web üzerinden 'apply' kontrolü
$apply_flag = false;
if (php_sapi_name() === 'cli') {
    if (isset($argv[1]) && $argv[1] === 'apply') {
        $apply_flag = true;
    }
} else {
    if (isset($_GET['apply']) && $_GET['apply'] == '1') {
        $apply_flag = true;
    }
}

$dry_run = !$apply_flag;

if ($dry_run) {
    echo "DİKKAT: Şu an 'DRY RUN' (Simülasyon) modundasınız. Hiçbir değişiklik yapılmayacak.\n";
    echo "Gerçekten uygulamak için URL sonuna ?apply=1 ekleyin.\n\n";
} else {
    echo "UYARI: GERÇEK MOD! Veritabanı güncelleniyor...\n\n";
}

foreach ($tables_columns as $table => $columns) {
    echo "Tablo işleniyor: {$table}\n";
    
    foreach ($columns as $column) {
        // Double-encoding düzeltme SQL'i
        // CONVERT(BINARY CONVERT(column USING latin1) USING utf8mb4)
        
        $sql = "UPDATE `{$table}` SET `{$column}` = CONVERT(BINARY CONVERT(`{$column}` USING latin1) USING utf8mb4) 
                WHERE `{$column}` IS NOT NULL AND `{$column}` <> ''";
        
        if ($dry_run) {
            echo "  [SIM] Sütun düzeltilecek: {$column}\n";
        } else {
            try {
                $affected = $pdo->exec($sql);
                echo "  [OK] Sütun güncellendi: {$column} ({$affected} satır etkilendi)\n";
            } catch (PDOException $e) {
                echo "  [HATA] Sütun güncellenemedi: {$column} -> " . $e->getMessage() . "\n";
            }
        }
    }
    echo "\n";
}

echo "--- Veritabanı Onarım Tamamlandı ---\n";
if ($dry_run) {
    echo "\nDeğişiklikleri uygulamak için lütfen ?apply=1 parametresini kullanın.\n";
}
?>
