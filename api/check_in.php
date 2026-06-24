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
$target_id = $_POST['target_id'] ?? ($_POST['place_id'] ?? null);
$target_type = $_POST['target_type'] ?? 'place';
$district_id = $_POST['district_id'] ?? ($_SESSION['district_id'] ?? null);

// Get User's Current Location from request
$user_lat = isset($_POST['lat']) ? floatval($_POST['lat']) : null;
$user_lng = isset($_POST['lng']) ? floatval($_POST['lng']) : null;

if (!$target_id || !$district_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz parametreler.']);
    exit;
}

if ($user_lat === null || $user_lng === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Konum bilgileriniz alınamadı. Lütfen konum izni verin.']);
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
    $target_lat = null;
    $target_lng = null;
    if ($target_type === 'place') {
        $stmt = $pdo->prepare("SELECT lat, lng FROM places WHERE id = ?");
        $stmt->execute([$target_id]);
        $target = $stmt->fetch();
    } else {
        $stmt = $pdo->prepare("SELECT lat, lng FROM businesses WHERE id = ?");
        $stmt->execute([$target_id]);
        $target = $stmt->fetch();
    }

    if (!$target || empty($target['lat']) || empty($target['lng'])) {
        echo json_encode(['status' => 'error', 'message' => 'Mekan konum bilgisi bulunamadı.']);
        exit;
    }

    $distance = getDistance($user_lat, $user_lng, floatval($target['lat']), floatval($target['lng']));

    if ($distance > 100) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Check-in yapabilmek için mekana en az 100 metre yakınında olmalısınız.',
            'distance' => round($distance) . 'm'
        ]);
        exit;
    }

    // Check if user already has a check-in here recently (24 hours)
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM check_ins WHERE user_id = ? AND target_id = ? AND target_type = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stmt_check->execute([$user_id, $target_id, $target_type]);
    if ($stmt_check->fetchColumn() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Bu mekanda 24 saat aralıklar ile check-in yapabilirsiniz.']);
        exit;
    }

    // 2. Insert Check-In record as APPROVED (Auto-approve)
    $stmt = $pdo->prepare("INSERT INTO check_ins (user_id, target_id, target_type, district_id, status) VALUES (?, ?, ?, ?, 'APPROVED')");
    $stmt->execute([$user_id, $target_id, $target_type, $district_id]);
    
    // 3. Award Points immediately (e.g., 10 points)
    require_once __DIR__ . '/../business/GamificationService.php';
    $gamification = new \Rehber\Business\GamificationService($pdo);
    $gamification->awardPoints($user_id, 10);
    
    echo json_encode(['status' => 'success', 'message' => 'Tebrikler! Check-in işleminiz onaylandı.']);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
}
?>
