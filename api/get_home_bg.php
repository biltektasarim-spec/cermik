<?php
/**
 * RotaRehber - Ana Sayfa Dinamik Arka Plan API
 * 
 * KULLANIM:
 * → Özel gün geldiğinde: /assets/img/ozel-gunler/ klasörüne herhangi bir JPG yükle
 * → Özel gün bittiğinde: klasörü boşalt (resmi sil)
 * → Başka hiçbir ayar gerekmez. Uygulama otomatik algılar.
 * 
 * GÖRSEL BOYUTU: 1080x1920px, JPG/PNG/WEBP, max 500KB
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache, must-revalidate');

$baseUrl = 'https://rotarehber.com/';
$imgDir  = __DIR__ . '/../assets/img/ozel-gunler/';
$today   = date('Y-m-d');

// Klasörde herhangi bir görsel dosyası var mı?
$bugunGorsel = null;

if (is_dir($imgDir)) {
    $desteklenenler = ['jpg', 'jpeg', 'png', 'webp'];
    $dosyalar = scandir($imgDir);

    foreach ($dosyalar as $dosya) {
        if ($dosya === '.' || $dosya === '..') continue;

        $uzanti = strtolower(pathinfo($dosya, PATHINFO_EXTENSION));
        if (in_array($uzanti, $desteklenenler)) {
            // İlk bulunan görseli döndür — ?v=timestamp ile resim değiştiği an önbellek sıfırlanır
            $mtime = filemtime($imgDir . $dosya);
            $bugunGorsel = $baseUrl . 'assets/img/ozel-gunler/' . rawurlencode($dosya) . '?v=' . $mtime;
            break;
        }
    }
}

echo json_encode([
    'bg_image' => $bugunGorsel,  // null = klasör boş → uygulama gradient gösterir
    'today'    => $today,
], JSON_UNESCAPED_UNICODE);
