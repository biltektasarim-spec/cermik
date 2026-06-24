<?php
header('Content-Type: text/plain; charset=utf-8');

$server_time = time();
$server_date = date('Y-m-d H:i:s');

// Google'ın saatini çek
$google_time = null;
$ch = curl_init('https://www.gstatic.com/generate_204');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => true,
    CURLOPT_NOBODY         => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 5,
]);
$resp = curl_exec($ch);
curl_close($ch);
if ($resp && preg_match('/Date:\s*(.+)/i', $resp, $m)) {
    $google_time = strtotime(trim($m[1]));
}

echo "=== SUNUCU SAAT KONTROLÜ ===\n\n";
echo "Sunucu Unix Time : $server_time\n";
echo "Sunucu Saati     : $server_date\n";
echo "Sunucu Timezone  : " . date_default_timezone_get() . "\n\n";

if ($google_time) {
    $diff = $server_time - $google_time;
    echo "Google Unix Time : $google_time\n";
    echo "Google Saati     : " . gmdate('Y-m-d H:i:s', $google_time) . " (UTC)\n\n";
    echo "FARK             : $diff saniye\n";
    if (abs($diff) > 300) {
        echo "SONUÇ: *** KRİTİK HATA! Sunucu saati Google ile " . abs($diff) . " saniye farklı! ***\n";
        echo "Bu fark JWT token üretimini bozuyor ve 401 hatasına sebep oluyor.\n";
    } elseif (abs($diff) > 60) {
        echo "SONUÇ: UYARI! " . abs($diff) . " saniye fark var, sınırda.\n";
    } else {
        echo "SONUÇ: OK - Saat farkı kabul edilebilir (" . abs($diff) . " saniye)\n";
    }
} else {
    echo "Google saati alınamadı (cURL sorunu olabilir)\n";
}
