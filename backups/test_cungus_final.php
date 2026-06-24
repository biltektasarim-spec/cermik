<?php
require_once 'config.php';
require_once 'api/fetch_pharmacies.php';

echo "Çüngüş Eczane Testi Başlatılıyor (EO ID: 1263, District 5)...\n";

// Cache'i atlatıp doğrudan fetch yapmayı dene
$result = fetchPharmacies('1263', 5);
echo "Sonuç Durumu: " . $result['status'] . "\n";
if ($result['status'] === 'success') {
    echo "Toplam Eczane: " . count($result['pharmacies']) . "\n";
    foreach ($result['pharmacies'] as $p) {
        echo "- " . $p['name'] . "\n";
    }
} else if ($result['status'] === 'cached') {
    echo "Veri zaten önbellekte. Veritabanını kontrol edebilirsiniz.\n";
} else {
    echo "Hata: " . ($result['message'] ?? 'Bilinmeyen hata') . "\n";
}
?>
