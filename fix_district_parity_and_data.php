<?php
require_once 'config.php';

// 1. Mükerrer Çüngüş kayıtlarını temizle ve ID 2'yi standart yap
echo "--- DISTRICT CLEANUP ---\n";
// ID 3'teki verileri ID 2'ye taşı (varsa)
$pdo->exec("UPDATE businesses SET district_id = 2 WHERE district_id = 3");
$pdo->exec("UPDATE pharmacies SET district_id = 2 WHERE district_id = 3");
$pdo->exec("UPDATE hospitals SET district_id = 2 WHERE district_id = 3");
// Fazlalık Çüngüş kaydını sil
$pdo->exec("DELETE FROM districts WHERE id = 3");
echo "Silinen veya Taşınan ID 3 (Çüngüş) -> Yeni Çüngüş: ID 2\n";

// 2. Çermik ID'sini doğrula (ID 1 olmalı)
$pdo->exec("UPDATE districts SET slug = 'cermik' WHERE id = 1");

// 3. EKSİK VERİ EKLEME (Web Parity)
echo "\n--- ADDING MISSING BUSINESSES (HOTELS & RESTAURANTS) ---\n";

$businesses = [
    // ÇERMİK OTELLERİ (ID: 1)
    ['name' => 'Çermik Termal Otel', 'cat' => 'Hotel', 'dist' => 1, 'img' => 'assets/img/categories/hotels_bg.jpg'],
    ['name' => 'Geyik Otel', 'cat' => 'Hotel', 'dist' => 1, 'img' => 'assets/img/categories/hotels_bg.jpg'],
    
    // ÇERMİK RESTORANLAR (ID: 1)
    ['name' => 'Çermik Sofrası', 'cat' => 'Restaurant', 'dist' => 1, 'img' => 'assets/img/categories/restaurants_bg.jpg'],
    ['name' => 'Kardeşler Lokantası', 'cat' => 'Restaurant', 'dist' => 1, 'img' => 'assets/img/categories/restaurants_bg.jpg'],

    // ÇÜNGÜŞ OTELLERİ (ID: 2)
    ['name' => 'Çüngüş Belediye Misafirhanesi', 'cat' => 'Hotel', 'dist' => 2, 'img' => 'assets/img/categories/hotels_bg.jpg'],
    
    // ÇÜNGÜŞ RESTORANLAR (ID: 2)
    ['name' => 'Çüngüş Aile Lokantası', 'cat' => 'Restaurant', 'dist' => 2, 'img' => 'assets/img/categories/restaurants_bg.jpg'],
];

foreach ($businesses as $b) {
    // Önce var mı diye kontrol et
    $stmt = $pdo->prepare("SELECT id FROM businesses WHERE business_name = ? AND district_id = ?");
    $stmt->execute([$b['name'], $b['dist']]);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO businesses (business_name, category, district_id, image_main, is_approved, is_active, created_at) VALUES (?, ?, ?, ?, 1, 1, NOW())");
        $stmt->execute([$b['name'], $b['cat'], $b['dist'], $b['img']]);
        echo "Eklendi: {$b['name']} ({$b['cat']})\n";
    }
}

echo "\n--- DATABASE PARITY COMPLETED ---\n";
?>
