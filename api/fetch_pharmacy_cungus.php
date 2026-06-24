<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

/**
 * Çüngüş Eczacı Odası Scraper
 * Source: https://www.diyarbakireo.org.tr/nobetci-eczaneler
 */
function fetchCungusPharmacy() {
    global $pdo;
    $district_db_id = 5; // Çüngüş id
    $cache_key = 'last_pharmacy_fetch_' . $district_db_id;
    
    $current_time = time();
    $two_hours = 2 * 60 * 60;

    // Check cache
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE name = ?");
    $stmt->execute([$cache_key]);
    $last_fetch = $stmt->fetchColumn();

    if ($last_fetch && ($current_time - $last_fetch) < $two_hours && !isset($_GET['force'])) {
        return ['status' => 'cached', 'message' => 'Cache halen gecerli.'];
    }

    $url = 'https://www.diyarbakireo.org.tr/nobetci-eczaneler';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $htmlRaw = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode != 200 || empty($htmlRaw)) {
        return ['status' => 'error', 'message' => 'Eczacı Odası sitesine ulaşılamadı.'];
    }

    // Parse logic for Çüngüş
    // The format in the HTML (based on read_url_content) seems to be:
    // ### PHARMACY NAME - DISTRICT
    // ADDRESS [PHONE](tel:PHONE)
    
    $pharmacies = [];
    // Regex for specific district "ÇÜNGÜŞ"
    // Example: ### ÇÜNGÜS ECZANESİ - ÇÜNGÜŞ\ntel: ÇÜNGÜŞ
    // Wait, the chunk showed: ### ÇÜNGÜS ECZANESİ - ÇÜNGÜŞ\ntel: ÇÜNGÜŞ
    // This looks like it might lack a real address in the markdown conversion or site structure
    
    // Let's try a more robust regex on the raw HTML if possible, or use the pattern seen in the chunk.
    // In the chunk 3: "### ÇÜNGÜS ECZANESİ - ÇÜNGÜŞ\ntel: ÇÜNGÜŞ"
    
    preg_match_all('/### (.*?) - ÇÜNGÜŞ\s*(.*?)(?=\n###|$)/is', $htmlRaw, $matches);
    
    if (!empty($matches[1])) {
        foreach ($matches[1] as $idx => $name) {
            $content = $matches[2][$idx];
            $address = "";
            $phone = "";
            
            // Extract phone if exists: [0412...](tel:0412...)
            if (preg_match('/\[(.*?)\]\(tel:(.*?)\)/', $content, $pM)) {
                $phone = $pM[1];
            }
            
            // Extract address (everything before the phone links)
            $address = trim(strip_tags(preg_replace('/\[.*?\]\(tel:.*?\)/', '', $content)));
            
            $pharmacies[] = [
                'name' => trim($name) . ' Eczanesi',
                'address' => $address ?: 'Çüngüş Merkez',
                'phone' => $phone ?: 'Bilinmiyor'
            ];
        }
    }

    if (!empty($pharmacies)) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE pharmacies SET is_on_duty = 0 WHERE district_id = ?")->execute([$district_db_id]);

            foreach ($pharmacies as $p) {
                $checkStmt = $pdo->prepare("SELECT id FROM pharmacies WHERE name LIKE ? AND district_id = ? LIMIT 1");
                $searchName = '%' . trim(str_replace(' Eczanesi', '', $p['name'])) . '%';
                $checkStmt->execute([$searchName, $district_db_id]);
                $existing = $checkStmt->fetchColumn();

                if ($existing) {
                    $pdo->prepare("UPDATE pharmacies SET is_on_duty = 1, address = ?, phone = ? WHERE id = ?")
                        ->execute([$p['address'], $p['phone'], $existing]);
                } else {
                    $pdo->prepare("INSERT INTO pharmacies (district_id, name, address, phone, is_on_duty) VALUES (?, ?, ?, ?, 1)")
                        ->execute([$district_db_id, $p['name'], $p['address'], $p['phone']]);
                }
            }
            
            $pdo->prepare("INSERT INTO settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?")
                ->execute([$cache_key, $current_time, $current_time]);
            $pdo->commit();
            
            return ['status' => 'success', 'message' => 'Çüngüş nöbetçi eczaneleri güncellendi.', 'data' => $pharmacies];
        } catch (Exception $e) {
            $pdo->rollBack();
            return ['status' => 'error', 'message' => 'DB Hatası: ' . $e->getMessage()];
        }
    }

    return ['status' => 'warning', 'message' => 'Çüngüş için nöbetçi eczane bulunamadı.'];
}

if (basename($_SERVER['PHP_SELF']) == 'fetch_pharmacy_cungus.php') {
    echo json_encode(fetchCungusPharmacy());
}
