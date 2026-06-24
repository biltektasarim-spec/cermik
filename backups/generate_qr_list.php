<?php
require_once 'config.php';

// Tüm ilçeleri çek
$districts = $pdo->query("SELECT * FROM districts ORDER BY name")->fetchAll();

$report = "# ROTAREHBER QR KOD LİSTESİ\n";
$report .= "Bu liste, her ilçe için kayıtlı tüm yerleri ve QR kod oluşturmak için gerekli linkleri içerir.\n\n";

$baseUrl = "https://rotarehber.com";

foreach ($districts as $district) {
    $report .= "## " . $district['name'] . " İlçesi\n\n";
    
    // 1. Tarihi Yerler, Doğal Güzellikler vb (Places)
    $stmt = $pdo->prepare("SELECT * FROM places WHERE district_id = ? ORDER BY category, name");
    $stmt->execute([$district['id']]);
    $places = $stmt->fetchAll();
    
    if ($places) {
        $report .= "### Tarihi ve Turistik Mekanlar\n";
        $report .= "| Mekan Adı | Kategori | Web Linki (QR İçin) | Konum (Harita) |\n";
        $report .= "| :--- | :--- | :--- | :--- |\n";
        foreach ($places as $p) {
            $webLink = $baseUrl . "/place_detail.php?id=" . $p['id'];
            $mapsLink = "https://www.google.com/maps?q=" . $p['lat'] . "," . $p['lng'];
            $report .= "| " . $p['name'] . " | " . $p['category'] . " | " . $webLink . " | [Haritada Gör](" . $mapsLink . ") |\n";
        }
        $report .= "\n";
    }
    
    // 2. İşletmeler (Businesses)
    $stmt = $pdo->prepare("SELECT * FROM businesses WHERE district_id = ? ORDER BY category, business_name");
    $stmt->execute([$district['id']]);
    $businesses = $stmt->fetchAll();
    
    if ($businesses) {
        $report .= "### İşletmeler (Otel ve Restoranlar)\n";
        $report .= "| İşletme Adı | Kategori | Web Linki (QR İçin) | Konum (Harita) |\n";
        $report .= "| :--- | :--- | :--- | :--- |\n";
        foreach ($businesses as $b) {
            $webLink = $baseUrl . "/business_detail.php?id=" . $b['id'];
            $mapsLink = "https://www.google.com/maps?q=" . $b['lat'] . "," . $b['lng'];
            $report .= "| " . $b['business_name'] . " | " . $b['category'] . " | " . $webLink . " | [Haritada Gör](" . $mapsLink . ") |\n";
        }
        $report .= "\n";
    }

    // 3. Sağlık (Hospitals & Pharmacies)
    $stmt = $pdo->prepare("SELECT * FROM hospitals WHERE district_id = ? ORDER BY name");
    $stmt->execute([$district['id']]);
    $hospitals = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM pharmacies WHERE district_id = ? ORDER BY name");
    $stmt->execute([$district['id']]);
    $pharmacies = $stmt->fetchAll();

    if ($hospitals || $pharmacies) {
        $report .= "### Sağlık Kurumları\n";
        $report .= "| Kurum Adı | Tür | Konum (Harita) |\n";
        $report .= "| :--- | :--- | :--- |\n";
        foreach ($hospitals as $h) {
            $mapsLink = "https://www.google.com/maps?q=" . $h['lat'] . "," . $h['lng'];
            $report .= "| " . $h['name'] . " | Hastane | [Haritada Gör](" . $mapsLink . ") |\n";
        }
        foreach ($pharmacies as $ph) {
            $mapsLink = "https://www.google.com/maps?q=" . $ph['lat'] . "," . $ph['lng'];
            $report .= "| " . $ph['name'] . " | Eczane | [Haritada Gör](" . $mapsLink . ") |\n";
        }
        $report .= "\n";
    }

    $report .= "---\n\n";
}

file_put_contents('QR_KOD_LISTESI.md', $report);
echo "Rapor oluşturuldu: QR_KOD_LISTESI.md\n";
