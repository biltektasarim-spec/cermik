<?php
$url = 'https://www.diyarbakireo.org.tr/nobetkarti';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'test');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$htmlRaw = curl_exec($ch);
curl_close($ch);
echo "Length: " . strlen($htmlRaw) . "\n";
if (empty($htmlRaw)) {
    echo "ERROR: HTML is empty!\n";
}
preg_match_all('/<option.*?value=[\'"](.*?)[\'"].*?>(.*?)<\/option>/is', $htmlRaw, $matches);
if (empty($matches[0])) {
    echo "ERROR: No options found in HTML!\n";
}
foreach ($matches[1] as $index => $val) {
    echo $val . " - " . trim(strip_tags($matches[2][$index])) . "\n";
}
