<?php
$url = 'https://www.diyarbakireo.org.tr/nobetci-eczaneler';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'test');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$htmlRaw = curl_exec($ch);
curl_close($ch);
file_put_contents('eo_html.txt', $htmlRaw);
echo "Length: " . strlen($htmlRaw) . "\n";
