<?php
/**
 * sync_duty_pharmacy.php
 * Nöbetçi eczane verisini diyarbakireo.org.tr'den çekip DB'ye yazar.
 * PharmacyController'dan arka planda (fire & forget) çağrılır.
 * 
 * Parametreler:
 *   ?district=cermik  → ilçe slug (opsiyonel, varsayılan: cermik)
 *   ?district_id=3    → ilçe DB ID (opsiyonel, slug yerine kullanılabilir)
 */
header('Content-Type: application/json');
require_once '../config.php';   // $pdo ve date_default_timezone_set() burada
require_once 'fetch_pharmacies.php';

try {
    $district_slug = strtolower(trim($_GET['district'] ?? ''));
    $district_id   = isset($_GET['district_id']) ? (int)$_GET['district_id'] : 0;

    // Önce ID ile bak, yoksa slug ile DB'den çöz
    if ($district_id <= 0 && $district_slug !== '') {
        $stmt = $pdo->prepare("SELECT id FROM districts WHERE slug = ? ORDER BY id ASC LIMIT 1");
        $stmt->execute([$district_slug]);
        $district_id = (int)($stmt->fetchColumn() ?: 0);
    }

    // Hâlâ bulunamadıysa slug'a göre manuel fallback (güvenlik)
    if ($district_id <= 0) {
        $fallback = [
            'cermik' => 3,
            'cungus' => 4,
            'bismil' => 2,
            'merkez' => 1,
        ];
        $district_id = $fallback[$district_slug] ?? 3; // Varsayılan: Çermik
    }

    // Çüngüş için özel scraper (farklı kaynak kullanıyor)
    if ($district_slug === 'cungus' || $district_id === 4) {
        require_once 'fetch_pharmacy_cungus.php';
        $result = fetchCungusPharmacy();
    } else {
        // Tüm diğer ilçeler için genel scraper (diyarbakireo.org.tr)
        $result = fetchPharmacies($district_id);
    }

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
