<?php
header('Content-Type: application/json');
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Lütfen giriş yapın.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$target_id = $_POST['target_id'] ?? null;
$target_type = $_POST['target_type'] ?? 'place';
$district_id = $_POST['district_id'] ?? ($_SESSION['district_id'] ?? null);

// Get User's Current Location from request
$user_lat = isset($_POST['lat']) ? floatval($_POST['lat']) : null;
$user_lng = isset($_POST['lng']) ? floatval($_POST['lng']) : null;

if (!$target_id || !$district_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Eksik bilgi.']);
    exit;
}

if ($user_lat === null || $user_lng === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Konum verisi eksik.']);
    exit;
}

function getDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // in meters
    $latDiff = deg2rad($lat2 - $lat1);
    $lonDiff = deg2rad($lon2 - $lon1);
    $a = sin($latDiff / 2) * sin($latDiff / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($lonDiff / 2) * sin($lonDiff / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

try {
    // Get target location
    $stmt = $target_type === 'place' 
        ? $pdo->prepare("SELECT lat, lng FROM places WHERE id = ?")
        : $pdo->prepare("SELECT lat, lng FROM businesses WHERE id = ?");
    
    $stmt->execute([$target_id]);
    $target = $stmt->fetch();

    if (!$target || empty($target['lat']) || empty($target['lng'])) {
        echo json_encode(['status' => 'error', 'message' => 'Mekan koordinat bilgisi yok.']);
        exit;
    }

    $distance = getDistance($user_lat, $user_lng, floatval($target['lat']), floatval($target['lng']));

    if ($distance > 100) {
        echo json_encode(['status' => 'error', 'message' => 'Mesafe çok uzak.', 'distance' => round($distance)]);
        exit;
    }

    // Check last visit (24 hours check)
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM check_ins WHERE user_id = ? AND target_id = ? AND target_type = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stmt_check->execute([$user_id, $target_id, $target_type]);
    if ($stmt_check->fetchColumn() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Zaten bugün ziyaret edildi.']);
        exit;
    }

    // Insert as AUTO visit
    $stmt = $pdo->prepare("INSERT INTO check_ins (user_id, target_id, target_type, district_id, status, visit_type) VALUES (?, ?, ?, ?, 'APPROVED', 'AUTO')");
    $stmt->execute([$user_id, $target_id, $target_type, $district_id]);
    
    // Auto points (5 points for passive visit, vs 10 for manual)
    require_once __DIR__ . '/../business/GamificationService.php';
    $gamification = new \Rehber\Business\GamificationService($pdo);
    $gamification->awardPoints($user_id, 5); 
    
    echo json_encode(['status' => 'success', 'message' => 'Ziyaret otomatik kaydedildi.']);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
