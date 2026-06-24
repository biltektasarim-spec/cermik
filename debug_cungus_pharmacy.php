<?php
require_once 'config.php';
require_once 'api/fetch_pharmacies.php';

echo "Çüngüş (1263) için eczane verisi çekiliyor...\n";
// fetchPharmacies fonksiyonunu modifiye etmeden HTML'e ulaşmak için curl ile çekelim
$url = 'https://www.diyarbakireo.org.tr/nobetkarti';
$postData = [
    'tarihk' => date('Y-m-d'),
    'ilce' => '1263',
    'gnr' => 'YAZDIR'
];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$htmlRaw = curl_exec($ch);
curl_close($ch);

file_put_contents('eo_raw_cungus.html', $htmlRaw);
echo "HTML 'eo_raw_cungus.html' dosyasına kaydedildi. Uzunluk: " . strlen($htmlRaw) . "\n";

$result = fetchPharmacies('1263', 5);
print_r($result);
?>
