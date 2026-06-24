<?php
require_once '../config.php';
require_once __DIR__ . '/includes/auth_guard.php';

// Sadece Süper Admin
if ($_SESSION['admin_role'] !== 'SUPER_ADMIN') die('Yetkisiz');

$path1 = __DIR__ . '/../uploads/firebase-adminsdk.json';
$path2 = __DIR__ . '/includes/firebase-adminsdk.json';

echo "<h2>FCM JSON Yolu Tanılaması</h2>";
echo "<pre>";
echo "DIR: " . __DIR__ . "\n\n";
echo "Yol 1 (uploads/): " . realpath($path1) ?: $path1 . " [yok]\n";
echo "Yol 1 var mı: " . (file_exists($path1) ? "✅ EVET" : "❌ HAYIR") . "\n\n";
echo "Yol 2 (includes/): " . realpath($path2) ?: $path2 . " [yok]\n";
echo "Yol 2 var mı: " . (file_exists($path2) ? "✅ EVET" : "❌ HAYIR") . "\n\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "uploads/ yazılabilir mi: " . (is_writable(__DIR__ . '/../uploads/') ? "✅ EVET" : "❌ HAYIR") . "\n";
echo "includes/ yazılabilir mi: " . (is_writable(__DIR__ . '/includes/') ? "✅ EVET" : "❌ HAYIR") . "\n";
echo "</pre>";
