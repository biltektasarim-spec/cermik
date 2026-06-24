<?php
header('Content-Type: application/json');
require_once '../config.php';

// Bu API hem üye hem de anonim kullanıcılar için çalışır
$user_id = $_SESSION['user_id'] ?? null;
$target_id = isset($_POST['target_id']) ? (int)$_POST['target_id'] : null;
$target_type = $_POST['target_type'] ?? 'place';
$district_id = isset($_POST['district_id']) ? (int)$_POST['district_id'] : ($_SESSION['district_id'] ?? null);

$user_lat = isset($_POST['lat']) ? floatval($_POST['lat']) : null;
$user_lng = isset($_POST['lng']) ? floatval($_POST['lng']) : null;

if (!$target_id || !$district_id || $user_lat === null || $user_lng === null) {
    echo json_encode(['status' => 'error', 'message' => 'Eksik parametre.']);
    exit;
}

function getDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // metre
    $latDiff = deg2rad($lat2 - $lat1);
    $lonDiff = deg2rad($lon2 - $lon1);
    $a = sin($latDiff / 2) * sin($latDiff / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($lonDiff / 2) * sin($lonDiff / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

try {
    // Hedef koordinatlarını al
    $sql = ($target_type === 'place') 
        ? "SELECT lat, lng FROM places WHERE id = ?" 
        : "SELECT lat, lng FROM businesses WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$target_id]);
    $target = $stmt->fetch();

    if (!$target || empty($target['lat']) || empty($target['lng'])) {
        echo json_encode(['status' => 'error', 'message' => 'Hedef koordinatları bulunamadı.']);
        exit;
    }

    $distance = getDistance($user_lat, $user_lng, floatval($target['lat']), floatval($target['lng']));

    if ($distance > 100) {
        echo json_encode(['status' => 'error', 'message' => 'Mesafe çok uzak.', 'distance' => round($distance)]);
        exit;
    }

    if ($user_id) {
        // GİRİŞ YAPMIŞ KULLANICI: Mevcut record_visit.php mantığını işlet
        // (Burada kod tekrarı yerine yönlendirme de yapılabilir ama bağımsızlık için yazıyoruz)
        
        // 24 saat kontrolü
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM check_ins WHERE user_id = ? AND target_id = ? AND target_type = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $stmt_check->execute([$user_id, $target_id, $target_type]);
        
        if ($stmt_check->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO check_ins (user_id, target_id, target_type, district_id, status, visit_type) VALUES (?, ?, ?, ?, 'APPROVED', 'AUTO')");
            $stmt->execute([$user_id, $target_id, $target_type, $district_id]);
            
            // Puan ver
            require_once __DIR__ . '/../business/GamificationService.php';
            $gamification = new \Rehber\Business\GamificationService($pdo);
            $gamification->awardPoints($user_id, 5); 
        }
        echo json_encode(['status' => 'success', 'message' => 'Üye ziyareti kaydedildi.']);
    } else {
        // ANONİM KULLANICI: passive_stats tablosuna ekle
        // Not: IP bazlı kontrol sunucu tarafında eklenebilir, şimdilik frontend localStorage'a güveniyoruz
        $stmt = $pdo->prepare("INSERT INTO passive_stats (target_id, target_type, district_id) VALUES (?, ?, ?)");
        $stmt->execute([$target_id, $target_type, $district_id]);
        
        echo json_encode(['status' => 'success', 'message' => 'Anonim yakınlık kaydı yapıldı.']);
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
