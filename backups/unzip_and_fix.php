<?php
require_once 'config.php';

// 1. Unzip the archive
$zip = new ZipArchive;
$res = $zip->open('update.zip');
if ($res === TRUE) {
    // Extract it to the current directory (web root)
    $zip->extractTo(__DIR__);
    $zip->close();
    echo "Archive extracted successfully.<br>";
} else {
    echo "Failed to extract archive. Error code: $res<br>";
}

// 2. Fix the database encoding for settings
try {
    // Ensure connection uses utf8mb4
    $pdo->exec("SET NAMES utf8mb4");

    // The problematic encoded string mapping
    $fixes = [
        'mayor_name' => 'Şehmus KARAMEHMETOĞLU',
        'mayor_title' => 'Çermik Belediye Başkanı',
        'site_name' => 'Çermik Belediyesi',
        'site_email' => 'admin@cermik.bel.tr',
        'copyright_text' => 'Rotamız Çermik',
        'explore_desc' => 'Tarihin Sıcaklığı Doğanın Saklı Yüzü',
        'explore_desc_en' => 'The Warmth of History, The Hidden Face of Nature'
    ];

    $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE name = ?");
    foreach ($fixes as $name => $value) {
        $stmt->execute([$value, $name]);
    }
    
    // Attempt to fix some kvkk texts
    $pdo->exec("UPDATE settings SET value = REPLACE(value, '├ç', 'Ç')");
    $pdo->exec("UPDATE settings SET value = REPLACE(value, '├ğ', 'ç')");
    $pdo->exec("UPDATE settings SET value = REPLACE(value, '┼ş', 'ş')");
    $pdo->exec("UPDATE settings SET value = REPLACE(value, '┼Ş', 'Ş')");
    $pdo->exec("UPDATE settings SET value = REPLACE(value, '─▒', 'ı')");
    $pdo->exec("UPDATE settings SET value = REPLACE(value, '─░', 'İ')");
    $pdo->exec("UPDATE settings SET value = REPLACE(value, '├Â', 'ö')");
    $pdo->exec("UPDATE settings SET value = REPLACE(value, '├Ö', 'Ö')");
    $pdo->exec("UPDATE settings SET value = REPLACE(value, '├╝', 'ü')");
    $pdo->exec("UPDATE settings SET value = REPLACE(value, '├£', 'Ü')");
    $pdo->exec("UPDATE settings SET value = REPLACE(value, '─ş', 'ğ')");
    $pdo->exec("UPDATE settings SET value = REPLACE(value, '─Ş', 'Ğ')");

    echo "Database settings encoding fixed.<br>";
} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage() . "<br>";
}

echo "All tasks completed. Please verify the site.";
?>
