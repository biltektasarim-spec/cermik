<?php
/**
 * Hava Durumu Scraper - Dinamik Bölge Destekli
 * Google arama sonucu sayfasından sıcaklık bilgisini çeker
 * Yedek: Open-Meteo API
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=1800');

require_once '../config.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : 'cermik';
$slug = preg_replace('/[^a-z0-9-]/', '', $slug);

$cacheFile = sys_get_temp_dir() . '/weather_' . $slug . '_google.json';
$cacheTime = 1800; // 30 dk

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    echo file_get_contents($cacheFile);
    exit;
}

// İlçeyi Veritabanından Bul
$stmt = $pdo->prepare("SELECT name, lat, lng FROM districts WHERE slug = ? LIMIT 1");
$stmt->execute([$slug]);
$district = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$district) {
    // Fallback için Çermik bilgileri
    $districtName = 'Çermik';
    $districtLat = '38.1378';
    $districtLng = '39.4569';
} else {
    $districtName = $district['name'];
    $districtLat = $district['lat'] ?? '38.1378';
    $districtLng = $district['lng'] ?? '39.4569';
}

// Open-Meteo WMO kodu tabloları (yedek için)
$wmo_data = [
    0  => ['Güneşli',          'fa-sun'],
    1  => ['Açık',             'fa-sun'],
    2  => ['Parçalı Bulutlu',  'fa-cloud-sun'],
    3  => ['Bulutlu',          'fa-cloud'],
    45 => ['Sisli',            'fa-smog'],
    48 => ['Kırağılı Sis',     'fa-smog'],
    51 => ['Hafif Çisenti',    'fa-cloud-drizzle'],
    53 => ['Çisenti',          'fa-cloud-rain'],
    55 => ['Yoğun Çisenti',    'fa-cloud-rain'],
    61 => ['Hafif Yağmur',     'fa-cloud-rain'],
    63 => ['Yağmurlu',         'fa-cloud-showers-heavy'],
    65 => ['Şiddetli Yağmur',  'fa-cloud-showers-heavy'],
    71 => ['Hafif Kar',        'fa-snowflake'],
    73 => ['Kar Yağışlı',      'fa-snowflake'],
    75 => ['Yoğun Kar',        'fa-snowflake'],
    80 => ['Sağanaklı',        'fa-cloud-rain'],
    81 => ['Yağmur Sağanağı',  'fa-cloud-showers-heavy'],
    82 => ['Şiddetli Sağanak', 'fa-cloud-showers-heavy'],
    95 => ['Fırtınalı',        'fa-bolt'],
    96 => ['Dolulu Fırtına',   'fa-bolt'],
    99 => ['Şiddetli Fırtına', 'fa-bolt'],
];

function getConditionFromTemp($temp) {
    if ($temp >= 30) return ['fa-sun',         'Sıcak'];
    if ($temp >= 20) return ['fa-cloud-sun',   'Ilık'];
    if ($temp >= 10) return ['fa-cloud',       'Serin'];
    if ($temp >= 0)  return ['fa-cloud-rain',  'Soğuk'];
    return                  ['fa-snowflake',   'Karlı'];
}

$sslOpts = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]];

// ═══════════════════════════════════════════════
// YÖNTEM 1: Google Arama Sayfası Scrape
// ═══════════════════════════════════════════════
$queries = [
    $districtName . '+Diyarbakır+hava+durumu',
    $districtName . '+hava+durumu',
    $districtName . '+weather'
];

foreach ($queries as $q) {
    $googleUrl = 'https://www.google.com/search?q=' . urlencode($q) . '&hl=tr&gl=tr&num=1';

    $googleCtx = stream_context_create(array_merge($sslOpts, ['http' => [
        'method' => 'GET',
        'timeout' => 10,
        'ignore_errors' => true,
        'header' => implode("\r\n", [
            'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: tr-TR,tr;q=0.9,en;q=0.8',
            'Accept-Encoding: identity',
            'Connection: close',
            'Cache-Control: no-cache'
        ])
    ]]));

    $html = @file_get_contents($googleUrl, false, $googleCtx);
    if (!$html || strlen($html) < 1000) continue;

    $temp = null; $condition = ''; $humidity = '--'; $wind = '--'; $feelsLike = '--';

    if (preg_match('/<span[^>]*id="wob_tm"[^>]*>(\-?\d+)<\/span>/i', $html, $m) ||
        preg_match('/id="wob_tm"[^>]*>(\-?\d+)/i', $html, $m) ||
        preg_match('/wob_tm["\s]+[^>]*>(\-?\d+)/i', $html, $m)) {
        $temp = intval($m[1]);
    }

    if ($temp === null) {
        if (preg_match('/"temp_c"\s*:\s*"?(\-?\d+\.?\d*)"?/i', $html, $m2)) {
            $temp = round(floatval($m2[1]));
        }
    }

    if (preg_match('/id="wob_dc"[^>]*>([^<]+)</i', $html, $dc)) {
        $condition = trim($dc[1]);
    }
    if (preg_match('/id="wob_hm"[^>]*>([^<]+)</i', $html, $hm)) {
        $humidity = trim(str_replace('%', '', $hm[1]));
    }
    if (preg_match('/id="wob_ws"[^>]*>([^<]+)</i', $html, $ws)) {
        $wind = trim($ws[1]);
    }
    if (preg_match('/id="wob_ttm"[^>]*>(\-?\d+)/i', $html, $tt)) {
        $feelsLike = intval($tt[1]);
    }

    if ($temp !== null) {
        $iconInfo = getConditionFromTemp($temp);

        $lc = mb_strtolower($condition, 'UTF-8');
        if (str_contains($lc, 'güneş') || str_contains($lc, 'açık') || str_contains($lc, 'sunny') || str_contains($lc, 'clear')) {
            $icon = 'fa-sun';
        } elseif (str_contains($lc, 'yağmur') || str_contains($lc, 'rain') || str_contains($lc, 'shower')) {
            $icon = 'fa-cloud-showers-heavy';
        } elseif (str_contains($lc, 'kar') || str_contains($lc, 'snow')) {
            $icon = 'fa-snowflake';
        } elseif (str_contains($lc, 'fırtına') || str_contains($lc, 'thunder') || str_contains($lc, 'storm')) {
            $icon = 'fa-bolt';
        } elseif (str_contains($lc, 'sis') || str_contains($lc, 'fog') || str_contains($lc, 'mist')) {
            $icon = 'fa-smog';
        } elseif (str_contains($lc, 'parçalı') || str_contains($lc, 'partly') || str_contains($lc, 'bulut')) {
            $icon = 'fa-cloud-sun';
        } elseif (str_contains($lc, 'bulut') || str_contains($lc, 'cloud') || str_contains($lc, 'overcast')) {
            $icon = 'fa-cloud';
        } else {
            $icon = $iconInfo[0];
        }

        if (empty($condition)) $condition = $iconInfo[1];

        $result = [
            'status'     => 'success',
            'source'     => 'Google',
            'district'   => $districtName,
            'temp'       => $temp,
            'feels_like' => $feelsLike,
            'humidity'   => $humidity,
            'wind'       => $wind,
            'condition'  => $condition,
            'icon'       => $icon,
            'fetched_at' => date('H:i')
        ];
        $json = json_encode($result, JSON_UNESCAPED_UNICODE);
        file_put_contents($cacheFile, $json);
        echo $json;
        exit;
    }
}

// ═══════════════════════════════════════════════
// YÖNTEM 2: Open-Meteo (Dinamik Koordinatlar)
// ═══════════════════════════════════════════════
$omUrl = 'https://api.open-meteo.com/v1/forecast'
    . '?latitude=' . $districtLat . '&longitude=' . $districtLng
    . '&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m,apparent_temperature'
    . '&daily=temperature_2m_max,temperature_2m_min'
    . '&timezone=Europe%2FIstanbul&forecast_days=1';

$omCtx = stream_context_create(array_merge($sslOpts, [
    'http' => ['method' => 'GET', 'timeout' => 10, 'header' => 'User-Agent: Mozilla/5.0']
]));

$omResp = @file_get_contents($omUrl, false, $omCtx);
if ($omResp) {
    $omData = json_decode($omResp, true);
    if ($omData && isset($omData['current']['temperature_2m'])) {
        $c = $omData['current'];
        $wmo = intval($c['weather_code'] ?? 0);
        $ci  = $wmo_data[$wmo] ?? ['Parçalı Bulutlu', 'fa-cloud-sun'];

        $result = [
            'status'     => 'success',
            'source'     => 'Open-Meteo',
            'district'   => $districtName,
            'temp'       => round($c['temperature_2m']),
            'feels_like' => round($c['apparent_temperature'] ?? $c['temperature_2m']),
            'humidity'   => round($c['relative_humidity_2m'] ?? 0),
            'wind'       => round($c['wind_speed_10m'] ?? 0) . ' km/s',
            'condition'  => $ci[0],
            'icon'       => $ci[1],
            'min_temp'   => round($omData['daily']['temperature_2m_min'][0] ?? 0),
            'max_temp'   => round($omData['daily']['temperature_2m_max'][0] ?? 0),
            'fetched_at' => date('H:i')
        ];
        $json = json_encode($result, JSON_UNESCAPED_UNICODE);
        file_put_contents($cacheFile, $json);
        echo $json;
        exit;
    }
}

if (file_exists($cacheFile)) {
    $old = json_decode(file_get_contents($cacheFile), true);
    if ($old) { $old['stale'] = true; echo json_encode($old, JSON_UNESCAPED_UNICODE); exit; }
}

echo json_encode([
    'status' => 'error', 'district' => $districtName,
    'temp' => '--', 'condition' => '--', 'icon' => 'fa-cloud',
    'fetched_at' => date('H:i')
], JSON_UNESCAPED_UNICODE);
