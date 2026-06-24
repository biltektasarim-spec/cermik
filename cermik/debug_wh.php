<?php
require_once '../config.php';
header('Content-Type: text/html; charset=utf-8');

$stmt = $pdo->prepare("SELECT working_hours FROM businesses WHERE id = 14");
$stmt->execute();
$row = $stmt->fetch();

$wh = json_decode($row['working_hours'] ?? '{}', true);

// Yöntem 1: date_default_timezone_set (YENİ - DOĞRU)
date_default_timezone_set('Europe/Istanbul');
$new_day = (int)date('w');
$new_min = (int)date('H') * 60 + (int)date('i');

// Yöntem 2: time() + offset (ESKİ)
$old_ts = time() + (3 * 3600);
$old_day = (int)gmdate('w', $old_ts);
$old_min = (int)gmdate('H', $old_ts) * 60 + (int)gmdate('i', $old_ts);

$op_parts = explode(':', $wh['open'] ?? '00:00');
$cl_parts = explode(':', $wh['close'] ?? '00:00');
$open_min = (int)$op_parts[0] * 60 + (int)($op_parts[1] ?? 0);
$close_min = (int)$cl_parts[0] * 60 + (int)($cl_parts[1] ?? 0);
$wh_days = isset($wh['days']) ? array_map('intval', $wh['days']) : [];

echo "<h2>🔍 Çalışma Saatleri Debug - İşletme #14</h2>";
echo "<pre style='background:#222;color:#0f0;padding:20px;border-radius:10px;font-size:14px;'>";
echo "═══════════════════════════════════════\n";
echo "📦 HAM VERİ (DB'den gelen)\n";
echo "═══════════════════════════════════════\n";
echo "working_hours JSON: " . json_encode($wh, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

echo "═══════════════════════════════════════\n";
echo "⏰ SUNUCU SAAT BİLGİSİ\n";
echo "═══════════════════════════════════════\n";
echo "PHP default timezone : " . ini_get('date.timezone') . "\n";
echo "date('Y-m-d H:i:s')  : " . date('Y-m-d H:i:s') . "\n";
echo "gmdate('Y-m-d H:i:s'): " . gmdate('Y-m-d H:i:s') . "\n";
echo "time() epoch         : " . time() . "\n\n";

echo "═══════════════════════════════════════\n";
echo "🆕 YENİ YÖNTEM (date_default_timezone_set)\n";
echo "═══════════════════════════════════════\n";
echo "Bugünkü gün numarası : $new_day (" . ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'][$new_day] . ")\n";
echo "Şu anki dakika       : $new_min (" . date('H:i') . ")\n";
echo "days array           : [" . implode(', ', $wh_days) . "]\n";
echo "Gün açık mı?         : " . (in_array($new_day, $wh_days) ? '✅ EVET' : '❌ HAYIR') . "\n";
echo "Açılış dakikası      : $open_min (" . ($wh['open'] ?? '--') . ")\n";
echo "Kapanış dakikası     : $close_min (" . ($wh['close'] ?? '--') . ")\n";
echo "Saat uygun mu?       : " . (($new_min >= $open_min && $new_min < $close_min) ? '✅ EVET' : '❌ HAYIR') . "\n";
echo "SONUÇ                : " . ((in_array($new_day, $wh_days) && $new_min >= $open_min && $new_min < $close_min) ? '🟢 AÇIK' : '🔴 KAPALI') . "\n\n";

echo "═══════════════════════════════════════\n";
echo "🔄 ESKİ YÖNTEM (time()+3*3600 + gmdate)\n";
echo "═══════════════════════════════════════\n";
echo "Bugünkü gün numarası : $old_day (" . ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'][$old_day] . ")\n";
echo "Şu anki dakika       : $old_min (" . gmdate('H:i', $old_ts) . ")\n";
echo "Gün açık mı?         : " . (in_array($old_day, $wh_days) ? '✅ EVET' : '❌ HAYIR') . "\n";
echo "Saat uygun mu?       : " . (($old_min >= $open_min && $old_min < $close_min) ? '✅ EVET' : '❌ HAYIR') . "\n";
echo "SONUÇ                : " . ((in_array($old_day, $wh_days) && $old_min >= $open_min && $old_min < $close_min) ? '🟢 AÇIK' : '🔴 KAPALI') . "\n";
echo "</pre>";
