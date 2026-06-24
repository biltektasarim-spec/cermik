<?php
header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/../config.php';

echo "=== SON FCM TOKENLAR ===\n\n";

// cek_gonder_forms tablosundan
echo "--- cek_gonder_forms tablosu (son 5 kayıt) ---\n";
$stmt = $pdo->query("SELECT id, ad_soyad, fcm_token, created_at FROM cek_gonder_forms WHERE fcm_token IS NOT NULL AND fcm_token != '' ORDER BY id DESC LIMIT 5");
$rows = $stmt->fetchAll();
foreach ($rows as $r) {
    echo "ID: {$r['id']} | {$r['ad_soyad']} | {$r['created_at']}\n";
    echo "TOKEN: {$r['fcm_token']}\n";
    echo "TOKEN_LENGTH: " . strlen($r['fcm_token']) . "\n\n";
}

// users tablosundan
echo "\n--- users tablosu (son 5 kayıt) ---\n";
$stmt2 = $pdo->query("SELECT id, first_name, last_name, fcm_token FROM users WHERE fcm_token IS NOT NULL AND fcm_token != '' ORDER BY id DESC LIMIT 5");
$rows2 = $stmt2->fetchAll();
foreach ($rows2 as $r) {
    echo "ID: {$r['id']} | {$r['first_name']} {$r['last_name']}\n";
    echo "TOKEN: {$r['fcm_token']}\n";
    echo "TOKEN_LENGTH: " . strlen($r['fcm_token']) . "\n\n";
}
echo "\nDONE.\n";
