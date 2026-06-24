<?php
require_once __DIR__ . '/../config.php';

function fetchPharmacies($district_db_id = 3) {
    global $pdo;

    // İlçe EO ID Eşleştirmesi (diyarbakireo.org.tr kodları)
    $eo_mapping = [
        'merkez'   => '1',
        'bismil'   => '1195',
        'cermik'   => '1249',
        'cinar'    => '1253',
        'cungus'   => '1263',
        'dicle'    => '1278',
        'egil'     => '1791',
        'ergani'   => '1315',
        'hani'     => '1381',
        'hazro'    => '1389',
        'kocakoy'  => '1962',
        'kulp'     => '1490',
        'lice'     => '1504',
        'silvan'   => '1624'
    ];

    // Veritabanından ilçe slug bilgisini al
    $stmt_dist = $pdo->prepare("SELECT slug FROM districts WHERE id = ?");
    $stmt_dist->execute([$district_db_id]);
    $district_slug = $stmt_dist->fetchColumn();

    // Map üzerinden EO ID'yi bul (varsayılan merkez)
    $eo_id = $eo_mapping[strtolower($district_slug)] ?? '1';
    
    // Sunucu saati ~9.5 saat ileri gidiyor (hosting NTP sorunu).
    // Gerçek zamanı Google'dan alarak doğru tarihi belirliyoruz.
    $real_timestamp = time(); // fallback
    $gch = curl_init('https://www.google.com');
    curl_setopt($gch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($gch, CURLOPT_HEADER, true);
    curl_setopt($gch, CURLOPT_NOBODY, true);
    curl_setopt($gch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($gch, CURLOPT_TIMEOUT, 4);
    $gresp = curl_exec($gch);
    curl_close($gch);
    if ($gresp && preg_match('/Date:\s*(.+)\r?\n/i', $gresp, $gm)) {
        $gt = strtotime(trim($gm[1]));
        if ($gt > 0) $real_timestamp = $gt;
    }
    // Gerçek Istanbul saatini hesapla (UTC + 3 saat)
    $istanbul_timestamp = $real_timestamp + (3 * 3600);

    // Nöbetçi eczane mantığı: Saat 08:30'a kadar bir önceki günün nöbetçisi geçerlidir.
    $current_h = (int)gmdate('H', $istanbul_timestamp);
    $current_m = (int)gmdate('i', $istanbul_timestamp);

    if ($current_h < 8 || ($current_h == 8 && $current_m < 30)) {
        $pharmacy_date = gmdate('Y-m-d', $istanbul_timestamp - 86400); // 1 gün öncesi
    } else {
        $pharmacy_date = gmdate('Y-m-d', $istanbul_timestamp);
    }

    // Günlük ve saatlik cache kontrolü: seçili nöbet günü için 2 saatten kısa süre önce fetch yapıldıysa iptal et
    $cache_key = 'pharmacy_fetch_time_' . $district_db_id . '_' . $pharmacy_date;
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE name = ?");
    $stmt->execute([$cache_key]);
    $last_fetch_time = $stmt->fetchColumn();

    $current_timestamp = $real_timestamp; // gerçek zaman
    $cache_duration = 2 * 3600; // 2 saat

    if ($last_fetch_time && ($current_timestamp - (int)$last_fetch_time) < $cache_duration) {
        return ['status' => 'cached', 'message' => 'Cache geçerli: ' . $pharmacy_date];
    }

    // diyarbakireo.org.tr sitesinden POST ile ilgili ilçe için veriyi çek
    $url = 'https://www.diyarbakireo.org.tr/nobetkarti';
    $postData = [
        'tarihk' => $pharmacy_date,
        'ilce' => $eo_id,
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
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode != 200 || empty($htmlRaw)) {
        return ['status' => 'error', 'message' => 'Eczacı Odası sitesine ulaşılamadı.', 'code' => $httpcode, 'len' => strlen($htmlRaw)];
    }

    $cermik_pharmacies = [];

    // Regex ile HTML'i parse et
    // Çüngüş gibi bazı ilçelerde telefon olmayabiliyor. Regex'i esnetelim.
    preg_match_all('/<div class="eadi">(.*?)<\/div>.*?<div class="adres">(.*?)<\/div>/is', $htmlRaw, $matches);
    
    if (!empty($matches[1])) {
        for ($i = 0; $i < count($matches[1]); $i++) {
            $rawName = trim(strip_tags($matches[1][$i]));
            $rawContent = $matches[2][$i]; // Adres ve telefon burada birleşik olabilir
            
            // İçerikten telefon ve adresi ayırmaya çalış
            $phone = '';
            $address = '';
            
            if (preg_match('/(.*?)<br>\s*<b>TELEFON:<\/b>\s*(.*?)<br>(.*?)/is', $rawContent, $sub)) {
                $address = trim(strip_tags($sub[1]));
                $phone = trim(strip_tags($sub[2]));
            } else {
                $address = trim(strip_tags($rawContent));
            }
            
            $decodedName = html_entity_decode($rawName, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $name_clean = preg_replace('/\s*ECZANES[Iİıi]\s*|\s*ECZANE\s*/iu', '', $decodedName);
            $name_clean = trim($name_clean);
            $name = mb_convert_case($name_clean, MB_CASE_TITLE, "UTF-8") . ' Eczanesi';
            
            // Adresi temizle
            $address = html_entity_decode($address, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            
            $cermik_pharmacies[] = [
                'name' => $name,
                'address' => $address,
                'phone' => $phone ? html_entity_decode($phone, ENT_QUOTES | ENT_HTML5, 'UTF-8') : null
            ];
            
        }
    }

    if (!empty($matches[1])) {
        try {
            $pdo->beginTransaction();

            $pdo->prepare("UPDATE pharmacies SET is_on_duty = 0 WHERE district_id = ?")->execute([$district_db_id]);

            foreach ($cermik_pharmacies as $p) {
                // Eczane zaten db'de var mı?
                $checkStmt = $pdo->prepare("SELECT id FROM pharmacies WHERE name LIKE ? AND district_id = ? LIMIT 1");
                $searchName = '%' . trim(preg_replace('/\s*Eczanesi\s*/iu', '', $p['name'])) . '%';
                $checkStmt->execute([$searchName, $district_db_id]);
                $existing = $checkStmt->fetchColumn();

                if ($existing) {
                    $updateStmt = $pdo->prepare("UPDATE pharmacies SET name = ?, is_on_duty = 1, address = ?, phone = ? WHERE id = ?");
                    $updateStmt->execute([$p['name'], $p['address'], $p['phone'], $existing]);
                } else {
                    $insertStmt = $pdo->prepare("INSERT INTO pharmacies (district_id, name, address, phone, is_on_duty) VALUES (?, ?, ?, ?, 1)");
                    $insertStmt->execute([$district_db_id, $p['name'], $p['address'], $p['phone']]);
                }
            }

            $timeStmt = $pdo->prepare("INSERT INTO settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
            $timeStmt->execute([$cache_key, $current_timestamp, $current_timestamp]);

            $pdo->commit();
            return ['status' => 'success', 'message' => 'Nöbetçi eczaneler dış kaynaktan (diyarbakireo.org.tr) güncellendi.'];

        } catch (Exception $e) {
            $pdo->rollBack();
            return ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()];
        }
    } else {
         // Eğer site çalışıyor (HTML length > 1000) ama eczane bulamadıysa (0 match), o ilçe için liste BOŞTUR.
         // Bu nedenle dünkü tüm eczanelerin is_on_duty bilgisini sıfırlıyoruz.
         if (strlen($htmlRaw) > 1000) {
             $pdo->prepare("UPDATE pharmacies SET is_on_duty = 0 WHERE district_id = ?")->execute([$district_db_id]);
         }
         
            $timeStmt = $pdo->prepare("INSERT INTO settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
            $timeStmt->execute([$cache_key, $current_timestamp, $current_timestamp]);
         return ['status' => 'warning', 'message' => 'Yeni kaynakta bugün için eczane bulunamadı. Cache süresi ilerletildi.'];
    }
}
?>
