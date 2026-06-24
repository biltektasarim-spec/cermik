<?php
require_once 'config.php';
try {
    // 1. Çermik Otellerini Onayla
    $pdo->exec("UPDATE businesses SET is_approved = 1, is_active = 1 WHERE district_id = 3 AND category = 'Hotel'");
    echo "Çermik otelleri onaylandı.\n";

    // 2. Çüngüş Otellerini Düzelt (ID 47, 48 businesses tablosunda Hotel olmalı)
    // Önce var olup olmadığını kontrol edelim
    $checkObj = $pdo->query("SELECT id FROM businesses WHERE id IN (47,48)");
    if($checkObj->rowCount() > 0) {
        $pdo->exec("UPDATE businesses SET category = 'Hotel', is_approved = 1, is_active = 1 WHERE id IN (47,48)");
        echo "Çüngüş otelleri (ID 47,48) Hotel olarak güncellendi.\n";
    }

    // 3. Eksik Çermik Restoranlarını Ekle (Web'de görülenler)
    // Lezzet Durağı Restoran
    $stmt = $pdo->prepare("SELECT id FROM businesses WHERE business_name LIKE '%Lezzet Durağı%' AND district_id = 3");
    $stmt->execute();
    if($stmt->rowCount() == 0) {
        $pdo->exec("INSERT INTO businesses (district_id, business_name, category, is_approved, is_active) VALUES (3, 'Lezzet Durağı Restoran', 'Restaurant', 1, 1)");
        echo "Lezzet Durağı Restoran eklendi.\n";
    }

    // Osmanlı Sofrası
    $stmt = $pdo->prepare("SELECT id FROM businesses WHERE business_name LIKE '%Osmanlı Sofrası%' AND district_id = 3");
    $stmt->execute();
    if($stmt->rowCount() == 0) {
        $pdo->exec("INSERT INTO businesses (district_id, business_name, category, is_approved, is_active) VALUES (3, 'Osmanlı Sofrası', 'Restaurant', 1, 1)");
        echo "Osmanlı Sofrası eklendi.\n";
    }

    // 4. Eksik Çüngüş Restoranlarını Ekle
    // MEKAN
    $stmt = $pdo->prepare("SELECT id FROM businesses WHERE business_name = 'MEKAN' AND district_id = 5");
    $stmt->execute();
    if($stmt->rowCount() == 0) {
        $pdo->exec("INSERT INTO businesses (district_id, business_name, category, is_approved, is_active) VALUES (5, 'MEKAN', 'Restaurant', 1, 1)");
        echo "Çüngüş MEKAN lokantası eklendi.\n";
    }

    // PAŞANIN YERİ
    $stmt = $pdo->prepare("SELECT id FROM businesses WHERE business_name = 'PAŞANIN YERİ' AND district_id = 5");
    $stmt->execute();
    if($stmt->rowCount() == 0) {
        $pdo->exec("INSERT INTO businesses (district_id, business_name, category, is_approved, is_active) VALUES (5, 'PAŞANIN YERİ', 'Restaurant', 1, 1)");
        echo "Çüngüş PAŞANIN YERİ lokantası eklendi.\n";
    }

    // Düzgün bir şekilde onaylananların sayısını görelim
    $count = $pdo->query("SELECT COUNT(*) FROM businesses WHERE is_approved = 1")->fetchColumn();
    echo "Toplam Onaylı İşletme Sayısı: $count\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() ;
}
?>
